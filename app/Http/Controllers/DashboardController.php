<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Point 3 : aperçu de la caisse générale sur le tableau de bord (solde par devise + entrées du jour).
     */
    public function cashRegisterSummary()
    {
        $registers = CashRegister::with('currency')->get();

        $todayMovements = DB::table('cash_movements')
            ->join('cash_registers', 'cash_movements.cash_register_id', '=', 'cash_registers.id')
            ->join('currencies', 'cash_registers.currency_id', '=', 'currencies.id')
            ->whereDate('cash_movements.created_at', now()->toDateString())
            ->select(
                'currencies.code as currency',
                DB::raw("SUM(CASE WHEN cash_movements.type IN ('client_deposit','purchase_in','sale_in') THEN cash_movements.amount ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN cash_movements.type IN ('client_withdraw','purchase_out','sale_out') THEN cash_movements.amount ELSE 0 END) as total_out")
            )
            ->groupBy('currencies.code')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'balances' => $registers,
                'today' => $todayMovements,
            ],
        ]);
    }


    /**
     * Bug corrigé : additionnait tous les comptes quelle que soit leur devise
     * (ex: 500 USD + 150 000 XOF affiché comme "150 500 XOF"). Renvoie désormais
     * un solde par devise, comme pour la caisse générale.
     */
    public function totalBalance()
    {
        // Solde total par devise
        $balancesByCurrency = Account::join('currencies', 'accounts.currency_id', '=', 'currencies.id')
            ->select('currencies.code as currency', DB::raw('SUM(accounts.balance) as total'))
            ->groupBy('currencies.code')
            ->get();

        // Total reçu par jour (toutes devises confondues, à titre indicatif uniquement)
        $dailyDeposits = Account::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(balance) as total')
        )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'balancesByCurrency' => $balancesByCurrency,
            'dailyDeposits' => $dailyDeposits,
        ]);
    }

    public function depositsSummary()
    {
        // Bug corrigé : mélangeait les devises (final_amount de comptes en USD, XOF...
        // additionnés ensemble). Montant du jour désormais par devise.
        $todayDeposits = Movement::join('currencies', 'movements.currency_id', '=', 'currencies.id')
            ->where('movements.type', 'deposit')
            ->whereDate('movements.created_at', now()->toDateString())
            ->select('currencies.code as currency', DB::raw('SUM(movements.final_amount) as total'))
            ->groupBy('currencies.code')
            ->get();

        // 2. Montant total des dépôts groupés par jour (toutes devises confondues, à titre indicatif)
        $dailyDeposits = Movement::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(final_amount) as total')
        )
            ->where('type', 'deposit')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'todayDeposits' => $todayDeposits,
            'dailyDeposits' => $dailyDeposits
        ]);
    }

    public function withdrawalsSummary()
    {
        // Même correctif que depositsSummary() : montant du jour par devise.
        $todayWithdrawals = Movement::join('currencies', 'movements.currency_id', '=', 'currencies.id')
            ->where('movements.type', 'withdraw')
            ->whereDate('movements.created_at', now()->toDateString())
            ->select('currencies.code as currency', DB::raw('SUM(movements.final_amount) as total'))
            ->groupBy('currencies.code')
            ->get();

        // 2. Montant total des retraits groupés par jour (toutes devises confondues, à titre indicatif)
        $dailyWithdrawals = Movement::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(final_amount) as total')
        )
            ->where('type', 'withdraw')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'todayWithdrawals' => $todayWithdrawals,
            'dailyWithdrawals' => $dailyWithdrawals
        ]);
    }

    public function financialSummary()
    {
        // Dépôts par jour
        $deposits = Movement::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(final_amount) as total')
        )
            ->where('type', 'deposit')
            ->groupBy('date')
            ->pluck('total', 'date'); // [date => total]

        // Retraits par jour
        $withdrawals = Movement::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(final_amount) as total')
        )
            ->where('type', 'withdraw')
            ->groupBy('date')
            ->pluck('total', 'date'); // [date => total]

        // Balance par jour
        $balances = Account::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(balance) as total')
        )
            ->groupBy('date')
            ->pluck('total', 'date'); // [date => total]

        // Fusionner par date
        $dates = collect()
            ->merge($deposits->keys())
            ->merge($withdrawals->keys())
            ->merge($balances->keys())
            ->unique()
            ->sort();

        $data = $dates->map(function ($date) use ($deposits, $withdrawals, $balances) {
            return [
                'y' => $date,
                'deposits' => $deposits[$date] ?? 0,
                'withdrawals' => $withdrawals[$date] ?? 0,
                'balance' => $balances[$date] ?? 0,
            ];
        })->values();

        return response()->json($data);
    }

    public function exchangeRatesDonut()
    {
        $rates = DB::table('exchangerates as e')
            ->join('currencies as c1', 'e.from_currency_id', '=', 'c1.id')
            ->join('currencies as c2', 'e.to_currency_id', '=', 'c2.id')
            ->select(
                DB::raw("CONCAT(c1.code, ' → ', c2.code) as label"),
                'e.rate as value'
            )
            ->get();

        return response()->json($rates);
    }

    public function totalClients()
    {
        // Total global des clients
        $totalClients = Client::count();

        // Nombre de clients par jour
        $dailyClients = Client::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'totalClients' => $totalClients,
            'dailyClients' => $dailyClients,
        ]);
    }

    public function lastClients()
    {
        $clients = Client::orderBy('created_at', 'desc')->take(6)->get();
        return response()->json($clients);
    }

    public function lastMovements()
    {
        $movements = Movement::with(['account.currency', 'currency'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return response()->json($movements);
    }
}
