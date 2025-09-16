<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

     protected $fillable = [
        'code',
        'client_id',
        'currency_id',
        'balance',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

}
