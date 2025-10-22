<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Movement;
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
            'account_id'   => 'required|exists:accounts,id',
            'type'         => 'required|in:deposit,withdraw',
            'amount'       => 'required|numeric|min:0.01',
            'rate'         => 'nullable|numeric|min:0.0001',
            'currency_id'  => 'required|exists:currencies,id',
            'performed_by' => 'nullable|string|max:255',
        ]);

        try {
            $movement = DB::transaction(function () use ($request) {

                // 1️⃣ Récupérer le compte et sa devise
                $account = Account::with('currency')->lockForUpdate()->findOrFail($request->account_id);
                // ⚠️ lockForUpdate() évite les conflits de concurrence (2 opérations simultanées sur le même compte)

                // 2️⃣ Calculer les montants
                $amount = (float) $request->amount;
                $rate   = $request->rate ? (float) $request->rate : null;
                $finalAmount = $amount;

                if ($account->currency_id != $request->currency_id) {
                    if (!$rate) {
                        throw new \Exception("Taux de conversion requis pour cette opération");
                    }
                    $finalAmount = $amount * $rate;
                }

                // Sauvegarder le solde avant
                $balanceBefore = (float) $account->balance;

                // 3️⃣ Appliquer le mouvement
                if ($request->type === 'deposit') {
                    $account->increment('balance', $finalAmount);
                } elseif ($request->type === 'withdraw') {
                    $account->decrement('balance', $finalAmount);
                }

                // Solde après opération
                $balanceAfter = $account->fresh()->balance;

                // 4️⃣ Enregistrer le mouvement
                return Movement::create([
                    'account_id'     => $request->account_id,
                    'type'           => $request->type,
                    'amount'         => $amount,           // Montant saisi
                    'rate'           => $rate,             // Taux utilisé (si conversion)
                    'final_amount'   => $finalAmount,      // Montant converti
                    'currency_id'    => $request->currency_id,  // Devise saisie
                    'performed_by'   => $request->performed_by, // Personne ayant effectué le mouvement
                    'balance_before' => $balanceBefore,    // 👈 Solde avant
                    'balance_after'  => $balanceAfter,     // 👈 Solde après
                ]);
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

    public function exportHistory($month)
    {
        // Récupérer le premier mouvement du mois pour obtenir les infos du compte/client
        $firstMovement = Movement::with(['account.client'])
            ->whereMonth('created_at', Carbon::parse($month)->month)
            ->whereYear('created_at', Carbon::parse($month)->year)
            ->first();

        if (!$firstMovement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun historique trouvé pour ce mois.',
            ], 404);
        }

        // Nom du client
        $accountName = trim(($firstMovement->account->client->nom ?? '') . ' ' . ($firstMovement->account->client->prenom ?? '')) ?: 'Inconnu';
        $fileName = "Historique_{$month}_{$accountName}.xlsx";

        // Export
        return Excel::download(new HistoryExport($month, $accountName), $fileName);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'         => 'nullable|in:deposit,withdraw',
            'amount'       => 'nullable|numeric|min:0.01',
            'rate'         => 'nullable|numeric|min:0.0001',
            'currency_id'  => 'nullable|exists:currencies,id',
            'performed_by' => 'nullable|string|max:255',
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
                $newCurrencyId = $request->currency_id ?? $movement->currency_id;

                $finalAmount = $newAmount;

                if ($account->currency_id != $newCurrencyId) {
                    if (!$newRate) {
                        throw new \Exception("Taux de conversion requis pour cette opération");
                    }
                    $finalAmount = $newAmount * $newRate;
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
                    'final_amount'   => $finalAmount,
                    'currency_id'    => $newCurrencyId,
                    'performed_by'   => $request->performed_by ?? $movement->performed_by,
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                ]);

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
