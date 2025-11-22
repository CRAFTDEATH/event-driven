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
