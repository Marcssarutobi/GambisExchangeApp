<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;

/**
 * Centralise l'écriture des mouvements de caisse générale (point 3 & 4/7 des évolutions
 * demandées) afin que MovementController (dépôts/retraits clients) et
 * CurrencyPurchasesController (achats/ventes de devises) appliquent exactement la même
 * logique de calcul de solde et d'historique.
 *
 * À appeler à l'intérieur d'une transaction DB existante (le compte/registre est verrouillé
 * par l'appelant via lockForUpdate() si nécessaire).
 */
class CashRegisterService
{
    /**
     * Vérifie qu'il y a assez de cash dans la caisse générale, dans cette devise, avant toute
     * sortie physique de cash (retrait client, vente de devises...), quelle que soit la page
     * d'origine de l'opération.
     *
     * Comme pour les comptes clients, seule la caisse en Dollar (USD) ne doit jamais devenir
     * négative : les autres devises (Naira, Franc CFA...) peuvent être mises à découvert.
     */
    public static function assertSufficientForWithdrawal(int $currencyId, float $amount): void
    {
        $currency = Currency::find($currencyId);
        if (!$currency || $currency->code !== 'USD') {
            return; // seule la caisse en USD est protégée
        }

        $register = CashRegister::forCurrency($currencyId);
        $register->lockForUpdate()->find($register->id);

        if ((float) $register->balance < $amount) {
            throw new \Exception(
                "Solde de caisse insuffisant en {$currency->code} pour cette opération. "
                . "Disponible : " . number_format((float) $register->balance, 2, ',', ' ') . " {$currency->code}, "
                . "montant demandé : " . number_format($amount, 2, ',', ' ') . " {$currency->code}."
            );
        }
    }

    /**
     * Enregistre un mouvement de caisse et met à jour le solde de la caisse concernée.
     *
     * @param int $currencyId Devise de la caisse impactée
     * @param string $type Nature du mouvement (voir enum cash_movements.type)
     * @param string $direction 'in' (entrée) ou 'out' (sortie)
     * @param float $amount Montant du mouvement (toujours positif)
     * @param Model|null $reference Modèle à l'origine du mouvement (Movement, CurrencyPurchases...)
     * @param string|null $note
     */
    public static function record(
        int $currencyId,
        string $type,
        string $direction,
        float $amount,
        ?Model $reference = null,
        ?string $note = null,
        ?\DateTimeInterface $occurredAt = null
    ): CashMovement {
        $register = CashRegister::forCurrency($currencyId);
        $register->lockForUpdate()->find($register->id);

        $balanceBefore = (float) $register->balance;

        if ($direction === 'in') {
            $register->increment('balance', $amount);
        } else {
            $register->decrement('balance', $amount);
        }

        $balanceAfter = (float) $register->fresh()->balance;

        $data = [
            'cash_register_id' => $register->id,
            'type'             => $type,
            'amount'           => $amount,
            'balance_before'   => $balanceBefore,
            'balance_after'    => $balanceAfter,
            'reference_type'   => $reference ? get_class($reference) : null,
            'reference_id'     => $reference?->id,
            'note'             => $note,
        ];

        // Permet de rejouer un mouvement passé (synchronisation des données existantes)
        // en conservant sa vraie date, plutôt que la date du jour.
        if ($occurredAt) {
            $data['created_at'] = $occurredAt;
            $data['updated_at'] = $occurredAt;
        }

        return CashMovement::create($data);
    }
}
