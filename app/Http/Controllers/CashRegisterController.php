<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Services\CashRegisterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Point 3 : Caisse générale — solde de trésorerie réel par devise (dépôts − retraits,
 * plus les mouvements liés aux achats/ventes de devises), avec historique filtrable.
 */
class CashRegisterController extends Controller
{
    /**
     * Soldes actuels, un par devise. Alimente le tableau de bord.
     */
    public function index()
    {
        $registers = CashRegister::with('currency')->get();

        return response()->json([
            'status' => 'success',
            'data' => $registers,
        ]);
    }

    /**
     * Historique des mouvements de caisse, avec filtre de dates (point 6) et par devise.
     * Query params : ?currency_id=1&from=2026-01-01&to=2026-01-31
     */
    public function history(Request $request)
    {
        $request->validate([
            'currency_id' => 'nullable|exists:currencies,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $query = CashMovement::with('cashRegister.currency')->orderBy('created_at', 'desc');

        if ($request->filled('currency_id')) {
            $query->whereHas('cashRegister', function ($q) use ($request) {
                $q->where('currency_id', $request->currency_id);
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    /**
     * Ajustement manuel de caisse (écart de comptage physique).
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'direction' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:255',
        ]);

        $movement = DB::transaction(function () use ($request) {
            return CashRegisterService::record(
                (int) $request->currency_id,
                'adjustment',
                $request->direction,
                (float) $request->amount,
                null,
                $request->note ?? 'Ajustement manuel'
            );
        });

        return response()->json([
            'status' => 'success',
            'data' => $movement,
        ]);
    }
}
