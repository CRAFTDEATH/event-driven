<?php

namespace App\Jobs;

use App\Services\Webhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessOrderEvent implements ShouldQueue
{
    use Queueable;

    protected $orderEvent;

    public function __construct($orderEvent)
    {
        $this->orderEvent = $orderEvent;
    }

    public function handle(): void
    {
        (new Webhook())->sendOrderEvent($this->orderEvent);
    }
}