<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'balance',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Récupère (ou crée) la caisse d'une devise donnée.
     */
    public static function forCurrency(int $currencyId): self
    {
        return static::firstOrCreate(['currency_id' => $currencyId], ['balance' => 0]);
    }
}
