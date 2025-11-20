<?php

namespace App\Http\Controllers\v1;

use App\Models\OrderEvent;
use App\Http\Controllers\Controller;


class OrderEventController extends Controller
{
    public function show($orderId)
    {
        $events = OrderEvent::where('order_id', $orderId)->get();

        return response()->json($events);
    }

    public function status($orderId)
    {
        $event = OrderEvent::where('order_id', $orderId)
            ->orderBy('id', 'desc')
            ->first();

        return response()->json($event);
    }
}
