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
            'order_id' => 'required'
        ]);

        $id = $validated['order_id'];

        // Redirect to the show route for this order
        return redirect()->route('orders.show', ['id' => $id]);
    }

    public function show(Request $request, $id)
    {
        // Fetch events server-side and pass them to the Inertia page so the front-end doesn't need to call the API again
        $events = OrderEvent::where('order_id', $id)->orderBy('created_at', 'asc')->get();
        return Inertia::render('Show', ['order' => $events]);
    }
}
