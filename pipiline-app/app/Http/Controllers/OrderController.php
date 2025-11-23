<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\OrderEvent;

class OrderController
{
    public function index()
    {
        // Render a simple page with a form to input an order id
        return Inertia::render('Orders/Index');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string'
        ]);

        $codigo = $validated['codigo'];

        $codigo = trim($codigo);

        // Prefer searching by the dedicated `codigo` column on OrderEvent
        $events = OrderEvent::where('codigo', $codigo)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($events->isNotEmpty()) {
            return Inertia::render('Orders/Show', [
                'order' => ['order' => ['codigo' => $codigo]],
                'events' => $events->toArray(),
            ]);
        }

        // As a fallback, if codigo looks numeric, try redirecting using it as codigo
        if (is_numeric($codigo)) {
            return redirect()->route('orders.show', ['codigo' => $codigo]);
        }

        // Not found — redirect back with an error message
        return back()->withErrors(['codigo' => 'Pedido não encontrado para o código informado.']);
    }

    public function show(Request $request, $codigo)
    {
        $codigo = trim($codigo);

        // Prefer the dedicated `codigo` column on OrderEvent
        $events = OrderEvent::where('codigo', $codigo)
            ->orderBy('created_at', 'asc')
            ->get();

        // Fallback: some older events might have the code inside the payload
        if ($events->isEmpty()) {
            $events = OrderEvent::where('payload->order->codigo', $codigo)
                ->orWhere('payload->codigo', $codigo)
                ->orWhere('payload->order->code', $codigo)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        // Always render the Orders/Show page and pass the events collection (may be empty)
        return Inertia::render('Orders/Show', [
            'order' => ['order' => ['codigo' => $codigo]],
            'events' => $events->toArray(),
        ]);
    }
}