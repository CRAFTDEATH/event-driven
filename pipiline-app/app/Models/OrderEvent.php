<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $fillable = [
        'codigo',
        'payload',
    ];
    protected $casts = [
        'payload' => 'array',
    ];
}
