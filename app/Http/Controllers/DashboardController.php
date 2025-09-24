<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Client;
use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    
    public function totalBalance(){
        $totalBalance = Account::sum('balance');
        return response()->json(['totalBalance' => $totalBalance]);
    }

    public function depositsSummary(){
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

    public function withdrawalsSummary(){
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

    public function totalClients(){
        $totalClients = Client::count();
        return response()->json(['totalClients' => $totalClients]);
    }

}
