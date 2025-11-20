<?php

namespace App\Enum;

enum OrderStatus: string
{
    case RECEIVED = 'Recebido';
    case TRANSPORT = 'Transporte';
    case DELIVERED = 'Entregue';
}
