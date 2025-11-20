<?php

namespace App\Listeners;

use App\Jobs\ProcessOrderEvent;
use App\Events\OrderStatusUpdated;

class ProcessOrderStatus
{

    public function handle(OrderStatusUpdated $event)
    {
        ProcessOrderEvent::dispatch($event->orderEvent);
    }
}
