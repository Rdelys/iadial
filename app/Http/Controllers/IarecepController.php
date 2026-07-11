<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IarecepController extends Controller
{
    public function index()
    {
        return view('iarecep');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'sector'       => 'nullable|string|max:255',
        ]);

        // TODO : enregistrer $validated en base (ex: Lead::create($validated);)
        // ou envoyer un email de notification.

        return back()->with('success', 'Votre demande a bien été envoyée ! Nous vous contactons sous 24h.');
    }
}