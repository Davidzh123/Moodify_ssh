<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        $user    = Auth::user();

        // On récupère la dernière recommandation (ou null)
        $lastRec = $user->recommendations()
                        ->latest('id')
                        ->first();

        return view('dashboard', [
            'user'    => $user,
            'lastRec' => $lastRec,
        ]);
    }
}
