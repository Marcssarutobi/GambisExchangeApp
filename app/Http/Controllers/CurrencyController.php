<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $data = Currency::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
       $validate = $request->validate([
            'code' => 'required|string|unique:currencies,code|max:3',
            'name' => 'required|string|max:255',
        ]);

        $currency = Currency::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $currency
        ], 201);
    }

    public function show($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $currency
        ]);
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found'
            ], 404);
        }

        $currency->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $currency
        ]);
    }

    public function destroy($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found'
            ], 404);
        }

        $currency->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Currency deleted successfully'
        ]);
    }
}
