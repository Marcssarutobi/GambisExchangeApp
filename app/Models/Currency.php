<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function exchangeRatesFrom()
    {
        return $this->hasMany(Exchangerate::class, 'from_currency_id');
    }

    public function exchangeRatesTo()
    {
        return $this->hasMany(Exchangerate::class, 'to_currency_id');
    }
}
