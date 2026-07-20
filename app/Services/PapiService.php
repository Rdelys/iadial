<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PapiService
{
    protected string $endpoint = 'https://app.papi.mg/dashboard/api/payment-links';

    /**
     * Crée un lien de paiement Papi et retourne le tableau "data" de la réponse.
     *
     * @throws \RuntimeException
     */
    public function createPaymentLink(array $data): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => config('services.papi.token'),
        ])->post($this->endpoint, $data);

        if ($response->failed()) {
            Log::error('Papi: échec de création du lien de paiement', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('Impossible de créer le lien de paiement Papi.');
        }

        return $response->json('data') ?? [];
    }
}