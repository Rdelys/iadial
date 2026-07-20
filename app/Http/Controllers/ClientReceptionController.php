<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ClientReceptionController extends Controller
{
    /**
     * Page "Mon Récep IA" — affiche le shortcode Vapi configuré
     * par l'admin pour ce client, ou un message d'attente.
     */
    public function show()
    {
        $user = Auth::user();

        if ($user->hasVapiAssistant()) {
            $user->bookingSlug(); // génère le slug à la volée si absent
        }

        return view('client.recep-ia', compact('user'));
    }
}