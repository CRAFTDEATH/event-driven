<?php

namespace App\Http\Controllers\v1;

use App\Models\Order;
use App\Models\Client;
use App\Models\Address;
use App\Models\Product;
use App\Enum\OrderStatus;
use App\Models\OrderEvent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\ProcessOrderEvent;
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {

            // 1) Create Address
            $address = Address::create([
                'neighborhood' => $validated['client']['address']['neighborhood'],
                'street'       => $validated['client']['address']['street'],
                'number'       => $validated['client']['address']['number'],
                'complement'   => $validated['client']['address']['complement'] ?? null,
                'city'         => $validated['client']['address']['city'],
                'state'        => $validated['client']['address']['state'],
                'zipcode'      => $validated['client']['address']['zipcode'],
            ]);

            // 2) Create Client
            $client = Client::create([
                'name'       => $validated['client']['name'],
                'cpf'        => $validated['client']['cpf'],
                'email'      => $validated['client']['email'],
                'phone'      => $validated['client']['phone'],
                'address_id' => $address->id,
            ]);

            // 3) Create Order
            $order = Order::create([
                'client_id' => $client->id,
                'codigo'    => strtoupper(Str::random(10)), 
            ]);

            // 4) Create the products linked to order
            foreach ($validated['product'] as $item) {
                Product::create([
                    'order_id' => $order->id,
                    'name'     => $item['name'],
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                ]);
            }

            // 5) Create initial OrderEvent
            $orderEvent = OrderEvent::create([
                'order_id' => $order->id,
                'status'   => OrderStatus::RECEIVED,
                'date'     => now(),
            ]);

            // 6) Dispatch Job
            ProcessOrderEvent::dispatch($orderEvent);

            DB::commit();

            return response()->json([
                'order_id' => $order->id,
                'codigo'   => $order->codigo,
                'message'  => 'Order created successfully.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao salvar ordem.', [
                'function'  => 'store',
                'request'   => $validated,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocorreu um erro na criação do pedido, tente novamente mais tarde.'
            ], 500);
        }
    }
    
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $name = $request->get('name');
        $codigo = $request->get('codigo');
        $status = $request->get('status');

        $query = Order::query()
            ->select(['orders.*', DB::raw('(select status from order_events where order_events.order_id = orders.id order by date desc limit 1) as current_status')])
            ->with('client')
            ->orderBy('id', 'desc');

        if ($name) {
            $query->whereHas('client', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        if ($codigo) {
            $query->where('codigo', 'like', "%{$codigo}%");
        }

        if ($status) {
            // filter by latest status using the same subquery used in select
            $query->whereRaw("(select status from order_events where order_events.order_id = orders.id order by date desc limit 1) = ?", [$status]);
        }

        $orders = $query->paginate($perPage);

        // Append query params for pagination links
        $orders->appends($request->all());

        return response()->json($orders);
    }
    public function show(Request $request, $orderId)
    {
        $order = Order::with(['client.address', 'products', 'events'])->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        // Attach latest status for convenience
        $latest = $order->events()->orderBy('date', 'desc')->first();
        $order->current_status = $latest?->status;

        return response()->json($order);
    }

    public function updateDetails(Request $request, $orderId)
    {
        $order = Order::with(['client.address', 'products'])->find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        $validated = $request->validate([
            'client.name' => 'required|string|max:255',
            'client.cpf' => 'nullable|string|max:32',
            'client.email' => 'nullable|email',
            'client.phone' => 'nullable|string|max:64',
            'client.address.street' => 'nullable|string|max:255',
            'client.address.number' => 'nullable|string|max:50',
            'client.address.complement' => 'nullable|string|max:255',
            'client.address.neighborhood' => 'nullable|string|max:255',
            'client.address.city' => 'nullable|string|max:255',
            'client.address.state' => 'nullable|string|max:8',
            'client.address.zipcode' => 'nullable|string|max:20',
            'product' => 'nullable|array',
            'product.*.name' => 'required_with:product|string|max:255',
            'product.*.quantity' => 'required_with:product|integer|min:1',
            'product.*.price' => 'required_with:product|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // update client
            $c = $order->client;
            if ($c) {
                $c->update([
                    'name' => $validated['client']['name'],
                    'cpf' => $validated['client']['cpf'] ?? $c->cpf,
                    'email' => $validated['client']['email'] ?? $c->email,
                    'phone' => $validated['client']['phone'] ?? $c->phone,
                ]);

                // update address
                $addrData = $validated['client']['address'] ?? [];
                if ($c->address) {
                    $c->address->update(array_merge($c->address->toArray(), $addrData));
                } else {
                    $addr = Address::create($addrData + ['zipcode' => $addrData['zipcode'] ?? null]);
                    $c->address_id = $addr->id;
                    $c->save();
                }
            }

            // replace products if provided
            if (isset($validated['product']) && is_array($validated['product'])) {
                // remove existing
                Product::where('order_id', $order->id)->delete();
                foreach ($validated['product'] as $item) {
                    Product::create([
                        'order_id' => $order->id,
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Pedido atualizado com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar pedido', ['exception' => $e->getMessage(), 'order' => $orderId]);
            return response()->json(['message' => 'Erro ao atualizar pedido'], 500);
        }
    }
    public function update(Request $request, $orderId)
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        DB::beginTransaction();

        try {
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['message' => 'Pedido não encontrado'], 404);
            }
            $statusEnum = OrderStatus::from($validated['status']);
            $orderEvent = OrderEvent::create([
                'order_id' => $order->id,
                'status' => $statusEnum,
                'date' => date('Y-m-d H:i:s')
            ]);
            event(new OrderStatusUpdated($orderEvent));
            DB::commit();
            return response()->json([], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro ao salvar.', [
                'function' => 'store',
                'request'  => $validated,
                'exception' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Ocorreu um erro na criação do produto tente novamente mais tarde'], 500);
        }
    }
}
