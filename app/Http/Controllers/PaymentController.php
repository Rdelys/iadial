<?php

namespace App\Http\Controllers;

use App\Mail\CompteActive;
use App\Models\Payment;
use App\Models\User;
use App\Services\PapiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Tarifs affichés et facturés au client, en euros.
     */
    protected array $plans;

    public function __construct()
    {
        $this->plans = config('plans');
    }

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

        // --- Création / mise à jour du compte, connexion immédiate, statut inactif ---
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $temporaryPassword = Str::random(12);

            $user = User::create([
                'name' => $data['client_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'company_name' => $data['company_name'],
                'sector' => $data['sector'],
                'address' => $data['address'],
                'city' => $data['city'],
                'password' => Hash::make($temporaryPassword),
                'plan' => $plan,
                'plan_label' => $planData['label'],
                'subscription_status' => 'inactive',
            ]);

            Log::info('Compte créé en attente de paiement', ['user_id' => $user->id, 'email' => $user->email]);

            try {
                Mail::to($user->email)->send(new CompteActive($user, $temporaryPassword));
            } catch (\Throwable $e) {
                Log::error('Échec envoi mail CompteActive', ['message' => $e->getMessage()]);
            }
        } else {
            $user->update([
                'name' => $data['client_name'],
                'phone' => $data['phone'],
                'company_name' => $data['company_name'],
                'sector' => $data['sector'],
                'address' => $data['address'],
                'city' => $data['city'],
                'plan' => $plan,
                'plan_label' => $planData['label'],
                'subscription_status' => 'inactive',
            ]);

            Log::info('Compte existant mis à jour en attente de paiement', ['user_id' => $user->id, 'email' => $user->email]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        // --- Création du paiement, lié au compte dès maintenant ---
        $payment = Payment::create([
            'user_id' => $user->id,
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

        if ($payment && $payment->status === 'success') {
            // Le compte a été créé et connecté dès l'étape store(). On sécurise
            // la connexion au cas où la session aurait été perdue entre-temps.
            if (!Auth::check() && $payment->user_id) {
                Auth::loginUsingId($payment->user_id);
            }

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
            'ready' => (bool) ($payment && $payment->status === 'success'),
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
            'merchantPaymentReference' => 'required|string',
            'paymentReference' => 'nullable|string',
            'notificationToken' => 'required|string',
            'paymentMethod' => 'nullable|string',
        ]);

        // "merchantPaymentReference" = notre référence (Payment::reference).
        // "paymentReference" = l'identifiant interne généré par Papi (à ne pas confondre).
        $payment = Payment::where('reference', $payload['merchantPaymentReference'])->first();

        if (!$payment) {
            Log::warning('Papi: notification reçue pour une référence inconnue', [
                'merchantPaymentReference' => $payload['merchantPaymentReference'],
            ]);
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

        // Le compte existe déjà (créé lors du store()) : on ne fait qu'activer l'abonnement.
        if ($payment->status === 'success' && $payment->user_id) {
            $user = $payment->user ?? User::find($payment->user_id);

            if ($user) {
                $user->update([
                    'plan' => $payment->plan,
                    'plan_label' => $payment->plan_label,
                    'subscription_status' => 'active',
                    'subscribed_at' => now(),
                ]);

                Log::info('Abonnement activé après paiement confirmé', ['user_id' => $user->id, 'email' => $user->email]);
            }
        }

        return response()->json(['message' => 'Notification reçue.']);
    }
}