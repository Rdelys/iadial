<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Services\PapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Tarifs affichés et facturés au client, en euros.
     */
    protected array $plans = [
        'starter' => ['label' => 'IADial Starter', 'amount_eur' => 399],
        'pro'     => ['label' => 'IADial Pro',     'amount_eur' => 599],
    ];

    public function checkout(string $plan)
    {
        abort_unless(isset($this->plans[$plan]), 404);

        return view('paiement.checkout', [
            'planKey' => $plan,
            'plan' => $this->plans[$plan],
        ]);
    }

    public function store(Request $request, string $plan, PapiService $papi)
    {
        abort_unless(isset($this->plans[$plan]), 404);
        $planData = $this->plans[$plan];

        $data = $request->validate([
            'client_name'  => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'sector'       => 'required|string|max:255',
            'address'      => 'required|string|max:255',
            'city'         => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:30',
            'accept_cgu'   => 'accepted',
        ], [
            'accept_cgu.accepted' => 'Vous devez accepter les CGU/CGV pour continuer.',
        ]);

        $rate = (float) config('services.papi.eur_to_mga_rate');
        $amountEur = (float) $planData['amount_eur'];
        $amountMga = round($amountEur * $rate, 2);

        $payment = Payment::create([
            'plan' => $plan,
            'plan_label' => $planData['label'],
            'client_name' => $data['client_name'],
            'company_name' => $data['company_name'],
            'sector' => $data['sector'],
            'address' => $data['address'],
            'city' => $data['city'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'amount_eur' => $amountEur,
            'amount_mga' => $amountMga,
            'exchange_rate' => $rate,
            'currency' => 'EUR',
            'reference' => 'IADIAL-' . strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        try {
            $result = $papi->createPaymentLink([
                'amount' => $amountMga,
                'clientName' => $payment->client_name,
                'reference' => $payment->reference,
                'description' => 'Abonnement ' . $planData['label']
                    . ' (' . number_format($amountEur, 2, ',', ' ') . ' €) - ' . $payment->company_name,
                'successUrl' => route('paiement.succes', ['reference' => $payment->reference]),
                'failureUrl' => route('paiement.echec', ['reference' => $payment->reference]),
                'notificationUrl' => route('paiement.notification'),
                'validDuration' => 60,
                'payerEmail' => $payment->email,
                'payerPhone' => $payment->phone,
                'isTestMode' => (bool) config('services.papi.test_mode'),
            ]);

            Log::info('Papi: lien de paiement créé', [
                'reference' => $payment->reference,
                'notificationUrl' => route('paiement.notification'),
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed']);
            Log::error('Papi: exception à la création du lien', ['message' => $e->getMessage()]);

            return back()->withInput()->withErrors(['payment' => "Le paiement n'a pas pu être initié. Veuillez réessayer."]);
        }

        $payment->update([
            'notification_token' => $result['notificationToken'] ?? null,
            'payment_link' => $result['paymentLink'] ?? null,
        ]);

        return redirect()->away($result['paymentLink']);
    }

    public function succes(Request $request)
    {
        $payment = Payment::where('reference', $request->query('reference'))->first();

        if ($payment && $payment->status === 'success' && $payment->user_id) {
            Auth::loginUsingId($payment->user_id);

            return redirect()->route('home')
                ->with('success', 'Paiement confirmé ! Votre compte ' . $payment->plan_label . ' est activé.');
        }

        return view('paiement.succes', ['payment' => $payment]);
    }

    /**
     * Endpoint JSON interrogé par la page succès pendant qu'elle attend
     * la confirmation asynchrone du webhook Papi.
     */
    public function statut(string $reference)
    {
        $payment = Payment::where('reference', $reference)->first();

        return response()->json([
            'status' => $payment->status ?? 'inconnu',
            'ready' => (bool) ($payment && $payment->status === 'success' && $payment->user_id),
        ]);
    }

    public function echec(Request $request)
    {
        $payment = Payment::where('reference', $request->query('reference'))->first();

        return view('paiement.echec', ['payment' => $payment]);
    }

    public function notification(Request $request)
    {
        Log::info('Papi: notification reçue', $request->all());

        $payload = $request->validate([
            'paymentStatus' => 'required|string',
            'paymentReference' => 'required|string',
            'notificationToken' => 'required|string',
            'paymentMethod' => 'nullable|string',
        ]);

        $payment = Payment::where('reference', $payload['paymentReference'])->first();

        if (!$payment) {
            Log::warning('Papi: notification reçue pour une référence inconnue', $payload);
            return response()->json(['message' => 'Référence inconnue.'], 422);
        }

        if ($payment->notification_token !== $payload['notificationToken']) {
            Log::warning('Papi: jeton de notification invalide', [
                'attendu' => $payment->notification_token,
                'recu' => $payload['notificationToken'],
            ]);
            return response()->json(['message' => 'Jeton invalide.'], 422);
        }

        $status = strtolower($payload['paymentStatus']) === 'success'
            ? 'success'
            : (strtolower($payload['paymentStatus']) === 'pending' ? 'pending' : 'failed');

        $payment->update([
            'status' => $status,
            'payment_method' => $payload['paymentMethod'] ?? null,
            'raw_notification' => $request->all(),
        ]);

        if ($payment->status === 'success' && !$payment->user_id) {
            $user = User::where('email', $payment->email)->first();
            $temporaryPassword = Str::random(12);

            if (!$user) {
                $user = User::create([
                    'name' => $payment->client_name,
                    'email' => $payment->email,
                    'phone' => $payment->phone,
                    'company_name' => $payment->company_name,
                    'sector' => $payment->sector,
                    'address' => $payment->address,
                    'city' => $payment->city,
                    'password' => Hash::make($temporaryPassword),
                    'plan' => $payment->plan,
                    'plan_label' => $payment->plan_label,
                    'subscription_status' => 'active',
                    'subscribed_at' => now(),
                ]);

                Log::info('Compte créé automatiquement après paiement', ['user_id' => $user->id, 'email' => $user->email]);

                // Mail::to($user->email)->send(new \App\Mail\CompteActive($user, $temporaryPassword));
            } else {
                $user->update([
                    'plan' => $payment->plan,
                    'plan_label' => $payment->plan_label,
                    'subscription_status' => 'active',
                    'subscribed_at' => now(),
                ]);
            }

            $payment->update(['user_id' => $user->id]);
        }

        return response()->json(['message' => 'Notification reçue.']);
    }
}