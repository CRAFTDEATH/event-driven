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

        // Try to find an OrderEvent that contains the codigo in common payload shapes
        $event = OrderEvent::where('payload->order->codigo', $codigo)
            ->orWhere('payload->codigo', $codigo)
            ->orWhere('payload->order->code', $codigo)
            ->first();

        // If found, redirect to the order's show page using the codigo in the URL
        if ($event) {
            return redirect()->route('orders.show', ['codigo' => $codigo]);
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
        // Find an event that matches the codigo in common payload locations
        $match = OrderEvent::where('payload->order->codigo', $codigo)
            ->orWhere('payload->codigo', $codigo)
            ->orWhere('payload->order->code', $codigo)
            ->first();

        if ($match) {
            // Load all events for the corresponding order_id
            $events = OrderEvent::where('order_id', $match->order_id)->orderBy('created_at', 'asc')->get();
            return Inertia::render('Orders/Show', ['order' => ['order' => ['codigo' => $codigo], 'order_event' => $events->first()], 'events' => $events]);
        }

        // If nothing matched, return the Orders/Show page with empty events (frontend will show a notice)
        return Inertia::render('Orders/Show', ['order' => null, 'events' => []]);
    }
}
