<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Webhook
{
    protected string $baseUrl = 'http://host.docker.internal:8030';

    public function sendOrderEvent($orderEvent)
    {
        try {
            // Login
            $login = Http::post("{$this->baseUrl}/api/login", [
                'email'    => 'adriano.rufino@hotmail.com',
                'password' => 'password'
            ]);

            if (!$login->successful()) {
                Log::error('Webhook login falhou', [
                    'status' => $login->status(),
                    'body'   => $login->body()
                ]);
                return;
            }

            $token = $login->json()['token'];

            // Payload enviado ao outro sistema
            $payload = [
                'payload'  =>  $this->buildPayload($orderEvent),
                'order_id' => $orderEvent->order_id
            ];

            // Enviar webhook
            $resp = Http::withToken($token)
                ->post("{$this->baseUrl}/api/v1/orders", $payload);

            if (!$resp->successful()) {
                Log::error('Erro ao enviar webhook', [
                    'status' => $resp->status(),
                    'body'   => $resp->body()
                ]);
            } else {
                Log::info('Webhook enviado com sucesso');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar webhook', ['message' => $e->getMessage()]);
        }
    }
    private function buildPayload($orderEvent)
    {
        return [
            'order_event' => [
                'id'        => $orderEvent->id,
                'order_id'  => $orderEvent->order_id,
                'status'    => $orderEvent->status,
                'payload'   => $orderEvent->payload,
                'date'      => $orderEvent->date,
                'created_at' => $orderEvent->created_at,
                'updated_at' => $orderEvent->updated_at,
            ],

            'order' => [
                'id'        => $orderEvent->order->id,
                'codigo'    => $orderEvent->order->codigo,

                'client' => [
                    'id'        => $orderEvent->order->client->id,
                    'name'      => $orderEvent->order->client->name,
                    'cpf'       => $orderEvent->order->client->cpf,
                    'email'     => $orderEvent->order->client->email,
                    'phone'     => $orderEvent->order->client->phone,

                    'address' => [
                        'neighborhood' => $orderEvent->order->client->address->neighborhood,
                        'street'       => $orderEvent->order->client->address->street,
                        'number'       => $orderEvent->order->client->address->number,
                        'city'         => $orderEvent->order->client->address->city,
                        'state'        => $orderEvent->order->client->address->state,
                        'zipcode'      => $orderEvent->order->client->address->zipcode,
                    ],
                ],

                'products' => $orderEvent->order->products
                    ->map(function ($product) {
                        return [
                            'id'       => $product->id,
                            'name'     => $product->name,
                            'quantity' => $product->quantity,
                            'price'    => $product->price,
                        ];
                    })
                    ->toArray(),
            ]
        ];
    }
}
