<?php

namespace App\Http\Controllers\v1;

use App\Models\Order;
use App\Enum\OrderStatus;
use App\Models\OrderEvent;
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
            $order = Order::create($validated);
            $orderEvent = OrderEvent::create([
                'order_id' => $order->id,
                'status' => OrderStatus::RECEIVED,
                'date' => date('Y-m-d H:i:s')
            ]);
            ProcessOrderEvent::dispatch($orderEvent);
            DB::commit();
            return response()->json([], 201);
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
                'status' => $statusEnum ,
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
