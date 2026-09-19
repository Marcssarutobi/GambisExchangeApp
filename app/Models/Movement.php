<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'rate',
        'rate_direction',
        'final_amount',
        'currency_id',
        'performed_by',
        'transfer_ref',
        'counterpart_account_id',
        'balance_before',
        'balance_after',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // L'autre compte d'un transfert (NULL pour un dépôt/retrait classique)
    public function counterpartAccount()
    {
        return $this->belongsTo(Account::class, 'counterpart_account_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
