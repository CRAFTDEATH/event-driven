<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'neighborhood',
        'street',
        'number',
        'complement',
        'city',
        'state',
        'zipcode',
    ];

    public function client()
    {
        return $this->hasOne(Client::class);
    }
}
