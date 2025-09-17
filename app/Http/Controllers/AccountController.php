<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index(){
        $data = Account::orderBy('id','desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request){
        $validate = $request->validate([
            'code' => 'required|string|max:255|unique:accounts',
            'client_id' => 'required|integer|exists:clients,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'balance' => 'required|numeric|min:0',
        ]);

        $account = Account::create($validate);

        return response()->json([
            'status' => 'success',
            'data' => $account
        ]);
    }

    public function show($id){
        $data = Account::find($id);
        if(!$data){
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id){
        $account = Account::find($id);
        if(!$account){
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        $account->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $account
        ]);
    }

    public function destroy($id){
        $data = Account::find($id);
        if(!$data){
            return response()->json([
                'status' => 'error',
                'message' => 'Account not found'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Account deleted successfully'
        ]);
    }
}
