<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(){
        $data = Client::orderBy('id','desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request){
       $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'email' => 'nullable|email|unique:clients,email',
            'npiece' => 'required|string|max:100|unique:clients,npiece',
        ]);

        $client = Client::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $client
        ], 201);
    }

    public function show($id){
        $client = Client::find($id);
        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    public function update(Request $request, $id){
        $client = Client::find($id);
        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        $client->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $client
        ]);
    }

    public function destroy($id){
        $client = Client::find($id);
        if (!$client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully'
        ]);
    }
}
