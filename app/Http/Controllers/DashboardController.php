<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function totalBalance()
    {
        // Total global
        $totalBalance = Account::sum('balance');

        // Total reçu par jour
        $dailyDeposits = Account::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(balance) as total')
        )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'totalBalance' => $totalBalance,
            'dailyDeposits' => $dailyDeposits,
        ]);
    }

    public function depositsSummary()
    {
        // 1. Montant total des dépôts aujourd’hui
        $todayDeposits = Movement::where('type', 'deposit')
            ->whereDate('created_at', now()->toDateString())
            ->sum('final_amount');

        // 2. Montant total des dépôts groupés par jour
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
        // 1. Montant total des retraits aujourd’hui
        $todayWithdrawals = Movement::where('type', 'withdraw')
            ->whereDate('created_at', now()->toDateString())
            ->sum('final_amount');

        // 2. Montant total des retraits groupés par jour
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
                DB::raw("c1.code || ' → ' || c2.code as label"),
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
