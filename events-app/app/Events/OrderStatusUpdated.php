<?php

namespace App\Events;

use App\Models\OrderEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public $orderEvent;

    public function __construct(OrderEvent $orderEvent)
    {
        $this->orderEvent = $orderEvent;
    }
}
