<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function index()
    {
        $data = Movement::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'rate' => 'nullable|numeric|min:0.0001',
            'final_amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
        ]);

        $movement = Movement::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $movement
        ], 201);
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
