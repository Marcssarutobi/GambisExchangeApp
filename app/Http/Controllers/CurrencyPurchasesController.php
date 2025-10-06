<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyPurchases;
use App\Models\Movement;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'supplier' => 'required|string|max:255',
            'amount_purchased' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'payment_currency_id' => 'nullable|exists:currencies,id',
            'total_paid' => 'required|numeric|min:0',
        ]);

        $purchase = CurrencyPurchases::create($validated);

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

        $purchase->delete();

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
