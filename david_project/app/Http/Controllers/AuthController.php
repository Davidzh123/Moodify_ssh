<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create(){
        return view('auth.register');
    }

    public function store(Request $request){
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|max:320|unique:users',
            'password' => 'required|string|confirmed'
        ]);

        User::create([
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        return redirect()->route("login.create");
    }

    public function loginCreate(){
        return view('auth.login');
    }

    public function loginPost(Request $request)
    {
        // 1. Validation
        $request->validate([
            'email' => 'required|email|max:320',
            'password' => 'required'
        ]);

        // 2. Récupération de l'utilisateur
        $user = User::where('email', $request->email)->first();

        // 3. Vérification de l'utilisateur
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'email' => 'Identifiants incorrects.',
            ])->withInput();
        }

        // 4. Authentification de l'utilisateur
        Auth::login($user);

        // 5. Redirection après connexion
        return redirect()->route('home'); // à adapter selon ta logique
    }

    public function delete(){

    }

    public function logout()
    {
    Auth::logout();
    return redirect('/');
    }
}
