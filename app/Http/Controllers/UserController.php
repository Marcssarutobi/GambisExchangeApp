<?php

namespace App\Http\Controllers;

use App\Mail\SendResetMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        $user = User::orderBy('id', 'desc')->get();
        return response()->json([
            'users' => $user
        ]);
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'user' => $user
        ]);
    }

    public function store(Request $request)
    {
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

    public function update(Request $request, $id)
    {
        $data = User::find($id);
        $user = $data->update($request->all());
        return response()->json([
            'user' => $user
        ]);
    }

    public function destroy($id)
    {
        $data = User::find($id);
        $user = $data->delete();
        return response()->json([
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required",
            "password" => "required",
        ]);



        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json([
                "message" => "Identifiants incorrects"
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;



        return response()->json([
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function getUser(Request $request)
    {
        $user = User::find(Auth::id());
        return response()->json([
            "user" => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "message" => "Deconnexion"
            ], 200);
        }
    }

    public function changePassword(Request $request,$id){
        $request->validate([
            "current_password"=>"required",
            "new_password"=>"required|confirmed"
        ]);

         // Récupérer l'utilisateur par son ID
        $user = User::find($id);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'error'=>'Le mot de passe actuel est incorrect.'
            ],403);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Retourner une réponse de succès
        return response()->json([
            'message' => 'Le mot de passe a été changé avec succès.'
        ]);

    }

    public function sendResetCode(Request $request){
        $request->validate([
            'email' => "required|email"
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Adresse email introuvable.'
            ], 404);
        }

        $code = rand(100000, 999999);

        $user->reset_code = $code;
        $user->save();

        Mail::to($user->email)->send(new SendResetMail($user->reset_code));

        return response()->json([
            "user"=>$user
        ]);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_code' => 'required'
        ]);

        $user = User::where('email', $request->email)->where('reset_code', $request->reset_code)->first();

        if (!$user) {
            return response()->json(['message' => 'Code invalide.'], 400);
        } else {
            $user->reset_code = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                "message" => "Code vérifié avec succès et connexion effectuée",
                "user" => $user,
                "token" => $token
            ]);
        }
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|confirmed',
        ]);

        // Récupérer l'utilisateur par son ID
        $user = User::find($id);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Retourner une réponse de succès
        return response()->json([
            'message' => 'Le mot de passe a été changé avec succès.'
        ]);
    }
}
