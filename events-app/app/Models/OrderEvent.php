<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $fillable = [
        'order_id',
        'status',
        'date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
