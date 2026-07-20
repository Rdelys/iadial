<?php

namespace App\Http\Controllers;

use App\Models\User;

class PublicBookingController extends Controller
{
    /**
     * Page publique de réservation en lecture seule, accessible
     * via le lien partagé par le client à ses propres clients.
     */
    public function show(string $slug)
    {
        $user = User::where('booking_slug', $slug)->firstOrFail();

        abort_unless($user->hasVapiAssistant(), 404);

        return view('public.booking', compact('user'));
    }
}