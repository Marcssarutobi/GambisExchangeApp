<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Movement;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\HistoryExport;
use Maatwebsite\Excel\Facades\Excel;

class MovementController extends Controller
{
    public function index()
    {
        $data = Movement::with('account.currency', 'currency')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id'     => 'required|exists:accounts,id',
            'type'           => 'required|in:deposit,withdraw',
            'amount'         => 'required|numeric|min:0.01',
            'rate'           => 'nullable|numeric|min:0.0001',
            // Point 5 : le taux reste saisi manuellement, mais le sens du calcul doit être
            // explicite dès qu'une conversion est nécessaire (fin de la multiplication systématique).
            'rate_direction' => 'nullable|in:multiply,divide',
            'currency_id'    => 'required|exists:currencies,id',
            'performed_by'   => 'nullable|string|max:255',
        ]);

        try {
            $movement = DB::transaction(function () use ($request) {

                // 1️⃣ Récupérer le compte et sa devise
                $account = Account::with('currency')->lockForUpdate()->findOrFail($request->account_id);
                // ⚠️ lockForUpdate() évite les conflits de concurrence (2 opérations simultanées sur le même compte)

                // 2️⃣ Calculer les montants
                $amount        = (float) $request->amount;
                $rate          = $request->rate ? (float) $request->rate : null;
                $rateDirection = $request->rate_direction;
                $finalAmount   = $amount;

                if ($account->currency_id != $request->currency_id) {
                    if (!$rate) {
                        throw new \Exception("Taux de conversion requis pour cette opération");
                    }
                    if (!in_array($rateDirection, ['multiply', 'divide'], true)) {
                        throw new \Exception("Sens du taux requis : multiplier ou diviser");
                    }
                    // Avant : $finalAmount = $amount * $rate; (toujours une multiplication -> bug
                    // signalé sur le Naira). Désormais le sens est explicite, choisi par l'agent.
                    $finalAmount = $rateDirection === 'multiply'
                        ? $amount * $rate
                        : $amount / $rate;
                } else {
                    $rateDirection = null; // pas de conversion, le sens n'a pas de raison d'être
                }

                // Sauvegarder le solde avant
                $balanceBefore = (float) $account->balance;

                // 3️⃣ Appliquer le mouvement
                if ($request->type === 'deposit') {
                    $account->increment('balance', $finalAmount);
                } elseif ($request->type === 'withdraw') {
                    if ($balanceBefore < $finalAmount) {
                        throw new \Exception("Solde insuffisant pour ce retrait");
                    }
                    $account->decrement('balance', $finalAmount);
                }

                // Solde après opération
                $balanceAfter = $account->fresh()->balance;

                // 4️⃣ Enregistrer le mouvement
                $movement = Movement::create([
                    'account_id'     => $request->account_id,
                    'type'           => $request->type,
                    'amount'         => $amount,           // Montant saisi
                    'rate'           => $rate,             // Taux utilisé (si conversion)
                    'rate_direction' => $rateDirection,    // multiply ou divide (point 5)
                    'final_amount'   => $finalAmount,      // Montant converti
                    'currency_id'    => $request->currency_id,  // Devise saisie
                    'performed_by'   => $request->performed_by, // Personne ayant effectué le mouvement
                    'balance_before' => $balanceBefore,    // 👈 Solde avant
                    'balance_after'  => $balanceAfter,     // 👈 Solde après
                ]);

                // 5️⃣ Caisse générale (point 3) : le client apporte/retire du cash physique
                // dans la devise saisie (currency_id), pour le montant saisi (amount).
                CashRegisterService::record(
                    $request->currency_id,
                    $request->type === 'deposit' ? 'client_deposit' : 'client_withdraw',
                    $request->type === 'deposit' ? 'in' : 'out',
                    $amount,
                    $movement,
                    'Mouvement #' . $movement->id . ' - compte ' . $account->code
                );

                return $movement;
            });

            return response()->json([
                'status' => 'success',
                'data'   => $movement
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $movement = Movement::find($id);
        if (!$movement) {
            return response()->json(['status' => 'error', 'message' => 'Movement not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $movement
        ]);
    }

    public function history($accountId)
    {
        $movements = Movement::where('account_id', $accountId)
            ->with(['account.client', 'account.currency', 'currency'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Groupement par mois + année
        $grouped = $movements->groupBy(function ($movement) {
            return Carbon::parse($movement->created_at)->format('Y-m'); // ex: "2025-10"
        });

        // Reformater pour avoir Month + History
        $result = $grouped->map(function ($items, $key) {
            $date = Carbon::createFromFormat('Y-m', $key);
            return [
                'month' => $date->translatedFormat('F Y'), // ex: "Octobre 2025"
                'history' => $items->values()
            ];
        })->values(); // on reset les clés

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Point 6 : export avec filtre de dates ("du ... au ..."), en plus du filtre par mois
     * conservé pour compatibilité. Filtre optionnel par compte.
     *
     * Query params : ?month=2026-01 (rétrocompatible)
     *             ou ?from=2026-01-01&to=2026-01-31
     *             + ?account_id=12 (optionnel, sur les deux formes ci-dessus)
     */
    public function exportHistory(Request $request)
    {
        $request->validate([
            'month'      => 'nullable|string',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $query = Movement::with(['account.client']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
            }
            $from = $request->from ?? '...';
            $to = $request->to ?? '...';
            $periodLabel = "du {$from} au {$to}";
            $fileLabel = "{$from}_au_{$to}";
        } elseif ($request->filled('month')) {
            $query->whereMonth('created_at', Carbon::parse($request->month)->month)
                  ->whereYear('created_at', Carbon::parse($request->month)->year);
            $periodLabel = Carbon::parse($request->month)->translatedFormat('F Y');
            $fileLabel = $request->month;
        } else {
            $periodLabel = 'complet';
            $fileLabel = 'complet';
        }

        $firstMovement = (clone $query)->orderBy('created_at')->first();

        if (!$firstMovement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun historique trouvé pour cette période.',
            ], 404);
        }

        // Nom du client (pour le titre/nom de fichier)
        $accountName = trim(($firstMovement->account->client->nom ?? '') . ' ' . ($firstMovement->account->client->prenom ?? '')) ?: 'Inconnu';
        $fileName = "Historique_{$fileLabel}_{$accountName}.xlsx";

        // Export
        return Excel::download(new HistoryExport($query, $periodLabel, $accountName), $fileName);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'           => 'nullable|in:deposit,withdraw',
            'amount'         => 'nullable|numeric|min:0.01',
            'rate'           => 'nullable|numeric|min:0.0001',
            'rate_direction' => 'nullable|in:multiply,divide',
            'currency_id'    => 'nullable|exists:currencies,id',
            'performed_by'   => 'nullable|string|max:255',
        ]);

        try {
            $movement = Movement::findOrFail($id);
            $account  = Account::findOrFail($movement->account_id);

            if ($movement->created_at->diffInMinutes(now()) > 30) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ce mouvement est trop ancien pour être modifié ou supprimé.'
                ], 403);
            }

            $updatedMovement = DB::transaction(function () use ($request, $movement, $account) {

                // 🔒 Empêcher les opérations simultanées
                $account->lockForUpdate();

                $originalCurrencyId = $movement->currency_id;
                $originalAmount     = (float) $movement->amount;
                $originalType       = $movement->type;

                // 1️⃣ Restaurer le solde du compte comme s’il n’y avait pas eu ce mouvement
                if ($movement->type === 'deposit') {
                    $account->decrement('balance', $movement->final_amount);
                } elseif ($movement->type === 'withdraw') {
                    $account->increment('balance', $movement->final_amount);
                }

                // 2️⃣ Recalculer le nouveau montant
                $newType       = $request->type ?? $movement->type;
                $newAmount     = $request->amount ? (float) $request->amount : $movement->amount;
                $newRate       = $request->rate ? (float) $request->rate : $movement->rate;
                $newDirection  = $request->rate_direction ?? $movement->rate_direction;
                $newCurrencyId = $request->currency_id ?? $movement->currency_id;

                $finalAmount = $newAmount;

                if ($account->currency_id != $newCurrencyId) {
                    if (!$newRate) {
                        throw new \Exception("Taux de conversion requis pour cette opération");
                    }
                    if (!in_array($newDirection, ['multiply', 'divide'], true)) {
                        throw new \Exception("Sens du taux requis : multiplier ou diviser");
                    }
                    // Point 5 : sens du calcul explicite, plus de multiplication systématique.
                    $finalAmount = $newDirection === 'multiply'
                        ? $newAmount * $newRate
                        : $newAmount / $newRate;
                } else {
                    $newDirection = null;
                }

                // 3️⃣ Vérifier le solde avant application
                $balanceBefore = $account->balance;

                if ($newType === 'withdraw' && $balanceBefore < $finalAmount) {
                    throw new \Exception("Solde insuffisant pour cette modification");
                }

                // 4️⃣ Appliquer la nouvelle opération
                if ($newType === 'deposit') {
                    $account->increment('balance', $finalAmount);
                } elseif ($newType === 'withdraw') {
                    $account->decrement('balance', $finalAmount);
                }

                $balanceAfter = $account->fresh()->balance;

                // 5️⃣ Mettre à jour le mouvement
                $movement->update([
                    'type'           => $newType,
                    'amount'         => $newAmount,
                    'rate'           => $newRate,
                    'rate_direction' => $newDirection,
                    'final_amount'   => $finalAmount,
                    'currency_id'    => $newCurrencyId,
                    'performed_by'   => $request->performed_by ?? $movement->performed_by,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                ]);

                // 6️⃣ Caisse générale : annuler l'ancien mouvement de caisse et enregistrer le nouveau,
                // au cas où le montant, la devise saisie ou le type auraient changé.
                CashRegisterService::record(
                    $originalCurrencyId,
                    $originalType === 'deposit' ? 'client_withdraw' : 'client_deposit', // inverse pour annuler
                    $originalType === 'deposit' ? 'out' : 'in',
                    $originalAmount,
                    $movement,
                    'Annulation avant modification du mouvement #' . $movement->id
                );
                CashRegisterService::record(
                    $newCurrencyId,
                    $newType === 'deposit' ? 'client_deposit' : 'client_withdraw',
                    $newType === 'deposit' ? 'in' : 'out',
                    $newAmount,
                    $movement,
                    'Modification du mouvement #' . $movement->id
                );

                return $movement;
            });

            return response()->json([
                'status' => 'success',
                'data'   => $updatedMovement
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $movement = Movement::findOrFail($id);
            $account  = Account::findOrFail($movement->account_id);

            if ($movement->created_at->diffInMinutes(now()) > 30) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ce mouvement est trop ancien pour être modifié ou supprimé.'
                ], 403);
            }

            DB::transaction(function () use ($movement, $account) {
                // 🔒 Bloquer le compte pendant la transaction
                $account->lockForUpdate();

                // 1️⃣ Annuler les effets du mouvement sur le solde
                if ($movement->type === 'deposit') {
                    // Si c'était un dépôt, on retire ce qui avait été ajouté
                    if ($account->balance < $movement->final_amount) {
                        throw new \Exception("Impossible de supprimer ce mouvement : solde insuffisant pour annuler le dépôt");
                    }
                    $account->decrement('balance', $movement->final_amount);
                } elseif ($movement->type === 'withdraw') {
                    // Si c'était un retrait, on réajoute ce qui avait été retiré
                    $account->increment('balance', $movement->final_amount);
                }

                // 1️⃣bis Annuler l'effet sur la caisse générale (point 3)
                CashRegisterService::record(
                    $movement->currency_id,
                    $movement->type === 'deposit' ? 'client_withdraw' : 'client_deposit',
                    $movement->type === 'deposit' ? 'out' : 'in',
                    (float) $movement->amount,
                    $movement,
                    'Annulation (suppression) du mouvement #' . $movement->id
                );

                // 2️⃣ Supprimer le mouvement
                $movement->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Mouvement supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
