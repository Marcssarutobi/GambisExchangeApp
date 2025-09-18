<?php

namespace App\Http\Controllers;

use App\Models\Exchangerate;
use Illuminate\Http\Request;

class ExchangerateController extends Controller
{
    public function index()
    {
        $data = Exchangerate::with(['fromCurrency', 'toCurrency'])->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'from_currency_id' => 'required|exists:currencies,id',
            'to_currency_id' => 'required|exists:currencies,id',
            'rate' => 'required|numeric|min:0',
        ]);

        $exchangerate = Exchangerate::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $exchangerate
        ]);
    }

    public function show($id)
    {
        $exchangerate = Exchangerate::find($id);

        if (!$exchangerate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exchange rate not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $exchangerate
        ]);
    }

    public function update(Request $request, $id)
    {
        $exchangerate = Exchangerate::find($id);

        if (!$exchangerate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exchange rate not found'
            ], 404);
        }

        $exchangerate->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $exchangerate
        ]);
    }

    public function destroy($id)
    {
        $exchangerate = Exchangerate::find($id);

        if (!$exchangerate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Exchange rate not found'
            ], 404);
        }

        $exchangerate->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exchange rate deleted successfully'
        ]);
    }
}
