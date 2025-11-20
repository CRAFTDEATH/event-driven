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
    public function show($id)
    {
        $orderEvent = OrderEvent::where('order_id', $id)->get();
        return response()->json($orderEvent, 200);
    }
}
