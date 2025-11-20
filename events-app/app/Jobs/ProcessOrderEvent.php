<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessOrderEvent implements ShouldQueue
{
    use Queueable;
    protected $orderEvent;
    /**
     * Create a new job instance.
     */
    public function __construct($orderEvent)
    {
        $this->orderEvent = $orderEvent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1️⃣ Fazer login na API para obter token
            $loginResponse = Http::post('http://host.docker.internal:8030/api/login', [
                'email' => 'adriano.rufino@hotmail.com',
                'password' => 'password'
            ]);

            if (!$loginResponse->successful()) {
                Log::error('Falha ao logar', [
                    'status' => $loginResponse->status(),
                    'body' => $loginResponse->body()
                ]);
                return;
            }

            $token = $loginResponse->json()['token'];

            // 2️⃣ Enviar payload para o endpoint de pedidos
            $orderPayload = [
                'payload' => $this->orderEvent->toArray(),
                'order_id' => $this->orderEvent->order_id
            ];

            $orderResponse = Http::withToken($token)->post('http://host.docker.internal:8030/api/v1/orders', $orderPayload);

            if ($orderResponse->successful()) {
                Log::info('Pedido enviado com sucesso', $orderResponse->json());
            } else {
                Log::error('Erro ao enviar pedido', [
                    'status' => $orderResponse->status(),
                    'body' => $orderResponse->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro na requisição', ['message' => $e->getMessage()]);
        }
    }
}
