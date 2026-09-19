<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Transfert de compte à compte.
 *
 * Un transfert est une opération INTERNE : aucun cash physique n'entre ni ne sort,
 * la caisse générale (CashRegisterService) n'est donc volontairement pas touchée.
 * Il crée deux mouvements liés par la même transfer_ref :
 *   - un retrait sur le compte source (montant saisi, dans la devise du compte source)
 *   - un dépôt sur le compte destination (converti si les devises diffèrent)
 */
class TransferController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id'   => 'required|exists:accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:0.01',
            'rate'            => 'nullable|numeric|min:0.0001',
            'rate_direction'  => 'nullable|in:multiply,divide',
            'performed_by'    => 'nullable|string|max:255',
        ]);

        try {
            $result = DB::transaction(function () use ($request) {

                // Verrouiller les deux comptes dans un ordre fixe (évite les deadlocks
                // si deux transferts inverses sont lancés en même temps).
                $ids = collect([$request->from_account_id, $request->to_account_id])
                    ->map(fn ($id) => (int) $id)->sort()->values()->all();

                $locked = Account::with('currency')
                    ->whereIn('id', $ids)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $from = $locked[(int) $request->from_account_id];
                $to   = $locked[(int) $request->to_account_id];

                $amount        = round((float) $request->amount, 2); // devise du compte source
                $rate          = null;
                $rateDirection = null;
                $receivedAmount = $amount;

                // Conversion nécessaire si les deux comptes n'ont pas la même devise
                if ($from->currency_id != $to->currency_id) {
                    $rate = $request->rate ? (float) $request->rate : null;
                    $rateDirection = $request->rate_direction;

                    if (!$rate) {
                        throw new \Exception("Taux de conversion requis : les deux comptes n'ont pas la même devise");
                    }
                    if (!in_array($rateDirection, ['multiply', 'divide'], true)) {
                        throw new \Exception("Sens du taux requis : multiplier ou diviser");
                    }

                    $receivedAmount = round(
                        $rateDirection === 'multiply' ? $amount * $rate : $amount / $rate,
                        2
                    );
                }

                $fromBefore = (float) $from->balance;
                if ($fromBefore < $amount) {
                    throw new \Exception("Solde insuffisant sur le compte source");
                }
                $toBefore = (float) $to->balance;

                $from->decrement('balance', $amount);
                $to->increment('balance', $receivedAmount);

                $ref = 'TRF-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(4));

                // Retrait sur le compte source
                $out = Movement::create([
                    'account_id'             => $from->id,
                    'type'                   => 'withdraw',
                    'amount'                 => $amount,
                    // Taux et sens conservés aussi sur le retrait (information/affichage) :
                    // final_amount reste le montant débité, dans la devise du compte source.
                    'rate'                   => $rate,
                    'rate_direction'         => $rateDirection,
                    'final_amount'           => $amount,
                    'currency_id'            => $from->currency_id,
                    'performed_by'           => $request->performed_by,
                    'transfer_ref'           => $ref,
                    'counterpart_account_id' => $to->id,
                    'balance_before'         => $fromBefore,
                    'balance_after'          => $from->fresh()->balance,
                ]);

                // Dépôt sur le compte destination (montant saisi dans la devise source,
                // final_amount = montant crédité dans la devise du compte destination)
                $in = Movement::create([
                    'account_id'             => $to->id,
                    'type'                   => 'deposit',
                    'amount'                 => $amount,
                    'rate'                   => $rate,
                    'rate_direction'         => $rateDirection,
                    'final_amount'           => $receivedAmount,
                    'currency_id'            => $from->currency_id,
                    'performed_by'           => $request->performed_by,
                    'transfer_ref'           => $ref,
                    'counterpart_account_id' => $from->id,
                    'balance_before'         => $toBefore,
                    'balance_after'          => $to->fresh()->balance,
                ]);

                return ['transfer_ref' => $ref, 'out' => $out, 'in' => $in];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $result,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
