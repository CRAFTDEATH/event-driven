<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $fillable = [
        'order_id',
        'payload',
    ];
    protected $casts = [
        'payload' => 'array',
    ];
}
