<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){
        $user = User::orderBy('id','desc')->get();
        return response()->json([
            'users' => $user
        ]);
    }

    public function show($id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'user' => $user
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        return response()->json([
            'user' => $user
        ]);
    }

    public function update(Request $request, $id){
        $data = User::find($id);
        $user = $data->update($request->all());
        return response()->json([
            'user' => $user
        ]);
    }

    public function destroy($id){
        $data = User::find($id);
        $user = $data->delete();
        return response()->json([
            'user' => $user
        ]);
    }

    public function login(Request $request){
        $request->validate([
            "email"=>"required",
            "password"=>"required",
        ]);



        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
           return response()->json([
            "message"=>"Identifiants incorrects"
           ],401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;



        return response()->json([
            'message'=>'Connexion réussie',
            'user'=>$user,
            'token' => $token
        ]);
    }

    public function getUser(Request $request){
        $user = User::find(Auth::id());
        return response()->json([
            "user" => $user,
        ]);
    }

    public function logout(Request $request){
        $user = Auth::user();
        if ($user) {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "message"=>"Deconnexion"
            ],200);
        }
    }
}
