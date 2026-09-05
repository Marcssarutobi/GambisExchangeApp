<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyPurchases;
use App\Models\Movement;
use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyPurchasesController extends Controller
{
    public function index()
    {
        $data = CurrencyPurchases::with(['currency', 'paymentCurrency'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        // Points 4 & 7 : achat ET vente de devises, avec impact automatique sur la caisse
        // générale, et le même correctif de sens de calcul que pour les mouvements (point 5).
        $validated = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'type' => 'required|in:achat,vente',
            // "supplier" = fournisseur pour un achat, ou nom de l'acheteur pour une vente.
            'supplier' => 'nullable|string|max:255',
            'amount_purchased' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'rate_direction' => 'required|in:multiply,divide',
            'payment_currency_id' => 'nullable|exists:currencies,id',
            'total_paid' => 'required|numeric|min:0',
        ]);

        $validated['supplier'] = $validated['supplier'] ?? '';

        // Point 5 (retour client) : un ACHAT ne peut pas payer plus que ce qui est
        // réellement disponible en caisse dans la devise de paiement. La vente n'est
        // volontairement pas concernée (on encaisse de l'argent, on n'en sort pas).
        if ($validated['type'] === 'achat' && !empty($validated['payment_currency_id'])) {
            $cashRegister = CashRegister::where('currency_id', $validated['payment_currency_id'])->first();
            $available = $cashRegister ? (float) $cashRegister->balance : 0;

            if ((float) $validated['total_paid'] > $available) {
                $currencyCode = Currency::find($validated['payment_currency_id'])->code ?? '';
                return response()->json([
                    'status' => 'error',
                    'message' => "Solde de caisse insuffisant en {$currencyCode} pour cet achat. "
                        . "Disponible : " . number_format($available, 2, ',', ' ') . " {$currencyCode}, "
                        . "montant demandé : " . number_format((float) $validated['total_paid'], 2, ',', ' ') . " {$currencyCode}.",
                ], 422);
            }
        }

        $purchase = DB::transaction(function () use ($validated) {
            $purchase = CurrencyPurchases::create($validated);

            if (!empty($validated['payment_currency_id'])) {
                if ($validated['type'] === 'achat') {
                    // Achat : sortie de caisse dans la devise de paiement, entrée dans la devise achetée
                    CashRegisterService::record(
                        $validated['payment_currency_id'],
                        'purchase_out',
                        'out',
                        (float) $validated['total_paid'],
                        $purchase,
                        'Achat #' . $purchase->id . ' auprès de ' . ($validated['supplier'] ?: 'fournisseur non renseigné')
                    );
                    CashRegisterService::record(
                        $validated['currency_id'],
                        'purchase_in',
                        'in',
                        (float) $validated['amount_purchased'],
                        $purchase,
                        'Achat #' . $purchase->id
                    );
                } else {
                    // Vente : entrée de caisse dans la devise de paiement reçue, sortie de la devise vendue
                    CashRegisterService::record(
                        $validated['payment_currency_id'],
                        'sale_in',
                        'in',
                        (float) $validated['total_paid'],
                        $purchase,
                        'Vente #' . $purchase->id . ($validated['supplier'] ? ' à ' . $validated['supplier'] : '')
                    );
                    CashRegisterService::record(
                        $validated['currency_id'],
                        'sale_out',
                        'out',
                        (float) $validated['amount_purchased'],
                        $purchase,
                        'Vente #' . $purchase->id
                    );
                }
            }

            return $purchase;
        });

        return response()->json([
            'status' => 'success',
            'data' => $purchase
        ], 200);
    }

    public function show($id)
    {
        $purchase = CurrencyPurchases::with(['currency', 'paymentCurrency'])->find($id);

        if (!$purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $purchase
        ]);
    }

    public function edit($id, Request $request)
    {
        $data = CurrencyPurchases::find($id);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }

        $data->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $purchase = CurrencyPurchases::find($id);

        if (!$purchase) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }

        DB::transaction(function () use ($purchase) {
            // Annuler l'impact sur la caisse générale avant suppression (point 3/4/7)
            if ($purchase->payment_currency_id) {
                if ($purchase->type === 'achat') {
                    CashRegisterService::record($purchase->payment_currency_id, 'purchase_in', 'in', (float) $purchase->total_paid, $purchase, 'Annulation (suppression) de l\'achat #' . $purchase->id);
                    CashRegisterService::record($purchase->currency_id, 'purchase_out', 'out', (float) $purchase->amount_purchased, $purchase, 'Annulation (suppression) de l\'achat #' . $purchase->id);
                } else {
                    CashRegisterService::record($purchase->payment_currency_id, 'sale_out', 'out', (float) $purchase->total_paid, $purchase, 'Annulation (suppression) de la vente #' . $purchase->id);
                    CashRegisterService::record($purchase->currency_id, 'sale_in', 'in', (float) $purchase->amount_purchased, $purchase, 'Annulation (suppression) de la vente #' . $purchase->id);
                }
            }

            $purchase->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Purchase deleted successfully'
        ]);
    }

    public function expectedGains()
    {
        $currencies = Currency::all();
        $results = [];

        foreach ($currencies as $currency) {
            // --- Récupérer tous les achats de cette devise ---
            $purchases = CurrencyPurchases::where('currency_id', $currency->id)->get();

            $totalPurchasedAmount = $purchases->sum('amount_purchased');
            $totalPurchasedCost   = $purchases->sum(function($p){
                return $p->amount_purchased * $p->rate;
            });

            if ($totalPurchasedAmount == 0) {
                $results[] = [
                    'currency' => $currency->code,
                    'real_gain' => 0,
                    'message' => 'Pas encore d’achats',
                    'daily_gains' => [],
                ];
                continue;
            }

            // --- Coût moyen pondéré ---
            $weightedCost = $totalPurchasedCost / $totalPurchasedAmount;

            // --- Récupérer toutes les ventes (withdraw) ---
            $sales = Movement::where('currency_id', $currency->id)
                ->where('type', 'withdraw')
                ->orderBy('created_at')
                ->get();

            if ($sales->isEmpty()) {
                $results[] = [
                    'currency' => $currency->code,
                    'real_gain' => 0,
                    'message' => 'Pas encore de ventes',
                    'daily_gains' => [],
                ];
                continue;
            }

            // --- Calcul du gain réel et par jour ---
            $realGain = 0;
            $dailyGains = [];

            foreach ($sales as $sale) {
                $gain = ($sale->rate - $weightedCost) * $sale->amount;
                $realGain += $gain;

                $day = $sale->created_at->format('Y-m-d'); // format YYYY-MM-DD

                if (!isset($dailyGains[$day])) {
                    $dailyGains[$day] = 0;
                }
                $dailyGains[$day] += $gain;
            }

            $results[] = [
                'currency' => $currency->code,
                'weighted_cost' => round($weightedCost, 4),
                'total_sold' => $sales->sum('amount'),
                'real_gain' => round($realGain, 2),
                'daily_gains' => collect($dailyGains)->map(fn($g) => round($g, 2)), // arrondi
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $results,
        ]);
    }
}
