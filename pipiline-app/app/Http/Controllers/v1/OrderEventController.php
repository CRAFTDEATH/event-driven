<?php

namespace App\Http\Controllers\v1;

use App\Models\OrderEvent;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class OrderEventController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payload' => 'array',
            'order_id' => 'numeric'
        ]);
        OrderEvent::create($validated);

        return response()->json([], 201);
    }
    public function show(Request $request, $id)
    {
        // If the request reached here it means the sanctum middleware authenticated the user.
        // Return the events for the order.
        $orderEvent = OrderEvent::where('order_id', $id)->get();
        return response()->json($orderEvent, 200);
    }
}
