<?php

namespace App\Http\Controllers;

use App\Models\DevisRequest;
use Illuminate\Http\Request;

class DevisController extends Controller
{
    protected array $options = [
        'galerie' => 'Galerie photo',
        'avis' => 'Espace avis clients',
        'ecommerce' => 'Fonctionnalités e-commerce',
        'multilingue' => 'Site multilingue',
        'blog' => 'Blog / actualités',
        'reservation' => 'Module de réservation avancé',
    ];

    public function create()
    {
        return view('devis', ['options' => $this->options]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'options.*' => 'string',
            'message' => 'nullable|string|max:2000',
        ]);

        DevisRequest::create($data);

        return redirect()->route('tarifs')
            ->with('success', "Votre demande de devis a bien été envoyée. Notre équipe vous recontactera rapidement.");
    }
}