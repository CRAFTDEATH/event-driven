<?php

namespace App\Listeners;

use App\Jobs\ProcessOrderEvent;
use App\Events\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessOrderStatus implements ShouldQueue
{

    public function handle(OrderStatusUpdated $event)
    {
        ProcessOrderEvent::dispatch($event->orderEvent);
    }
}
