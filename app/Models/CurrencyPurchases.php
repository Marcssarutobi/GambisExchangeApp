<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CurrencyPurchases extends Model
{
    use HasFactory;


    protected $fillable = [
        'currency_id',
        'supplier',
        'amount_purchased',
        'rate',
        'payment_currency_id',
        'total_paid',
    ];

    /**
     * Relation : devise achetée.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Relation : devise utilisée pour le paiement.
     */
    public function paymentCurrency()
    {
        return $this->belongsTo(Currency::class, 'payment_currency_id');
    }

    /**
     * Accessor : retourne le total payé formaté.
     */
    public function getFormattedTotalPaidAttribute()
    {
        return number_format($this->total_paid, 2, ',', ' ');
    }

    /**
     * Accessor : retourne le montant acheté formaté.
     */
    public function getFormattedAmountPurchasedAttribute()
    {
        return number_format($this->amount_purchased, 2, ',', ' ');
    }
}
