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
            'codigo' => 'string'
        ]);
        OrderEvent::create($validated);

        return response()->json([], 201);
    }
    public function show(Request $request, $id)
    {
        $codigo = $id;
        $orderEvent = OrderEvent::where('codigo', $codigo)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($orderEvent, 200);
    }
}
