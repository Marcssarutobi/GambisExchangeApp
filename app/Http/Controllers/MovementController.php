<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    public function index()
    {
        $data = Movement::with('account', 'currency')->orderBy('id', 'desc')->get();
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
        ]);

        try {
            $movement = DB::transaction(function () use ($request) {
                // 1. Récupérer le compte et sa devise
                $account = Account::with('currency')->findOrFail($request->account_id);

                // 2. Calculer le montant final dans la devise du compte
                $amount = (float) $request->amount;
                $rate   = $request->rate ? (float) $request->rate : null;
                $finalAmount = $amount;

                // Si la devise du compte est différente, appliquer le taux
                if ($account->currency_id != $request->currency_id) {
                    if (!$rate) {
                        throw new \Exception("Taux de conversion requis pour cette opération");
                    }
                    $finalAmount = $amount * $rate;
                }

                // 3. Déposer ou retirer
                if ($request->type === 'deposit') {
                    $account->increment('balance', $finalAmount);
                } elseif ($request->type === 'withdraw') {
                    if ((float) $account->balance < $finalAmount) {
                        throw new \Exception("Solde insuffisant");
                    }
                    $account->decrement('balance', $finalAmount);
                }

                // 4. Enregistrer le mouvement
                return Movement::create([
                    'account_id'   => $request->account_id,
                    'type'         => $request->type,
                    'amount'       => $amount,          // Montant saisi
                    'rate'         => $rate,            // Taux utilisé (si conversion)
                    'final_amount' => $finalAmount,     // Montant converti
                    'currency_id'  => $request->currency_id,  // Devise saisie
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

    public function update(Request $request, $id)
    {
        $movement = Movement::find($id);
        if (!$movement) {
            return response()->json(['status' => 'error', 'message' => 'Movement not found'], 404);
        }

        $movement->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $movement
        ]);
    }

    public function destroy($id)
    {
        $movement = Movement::find($id);
        if (!$movement) {
            return response()->json(['status' => 'error', 'message' => 'Movement not found'], 404);
        }

        $movement->delete();

        return response()->json(['status' => 'success', 'message' => 'Movement deleted']);
    }
}
