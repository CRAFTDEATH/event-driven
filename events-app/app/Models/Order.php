<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id',
        'codigo',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function events()
    {
        return $this->hasMany(OrderEvent::class);
    }
}
