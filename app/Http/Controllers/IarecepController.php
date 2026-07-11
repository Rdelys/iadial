<?php
// app/Http/Controllers/IarecepController.php

namespace App\Http\Controllers;

use App\Mail\IarecepDemandeMail;
use App\Models\IarecepMessage;
use App\Models\IarecepTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IarecepController extends Controller
{
    /**
     * Affiche la page (formulaire + chat texte/vocal).
     */
    public function index()
    {
        if (! session()->has('iarecep_token')) {
            session(['iarecep_token' => (string) Str::uuid()]);
        }

        $token = session('iarecep_token');

        // Si le visiteur a déjà un test en cours (rechargement de page), on le reprend.
        $existingTest = IarecepTest::where('token', $token)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        return view('iarecep', [
            'token' => $token,
            'existingTest' => $existingTest,
            'existingMessages' => $existingTest?->messages()->orderBy('id')->get() ?? collect(),
        ]);
    }

    /**
     * Crée le test (remplace l'ancien "store" basique) et renvoie le message
     * d'accueil de la réceptionniste IA. Appelé en AJAX.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'sector'       => 'nullable|string|max:255',
            'about'        => 'required|string|max:2000',
            'mode'         => 'nullable|in:text,vocal',
        ]);

        $token = session('iarecep_token') ?: (string) Str::uuid();
        session(['iarecep_token' => $token]);

        // Un token = un test "in_progress" à la fois.
        $test = IarecepTest::updateOrCreate(
            ['token' => $token, 'status' => 'in_progress'],
            [
                'company_name' => $validated['company_name'],
                'full_name'    => $validated['full_name'],
                'email'        => $validated['email'],
                'sector'       => $validated['sector'] ?? null,
                'about'        => $validated['about'],
                'mode'         => $validated['mode'] ?? 'text',
                'status'       => 'in_progress',
            ]
        );

        // On repart d'une conversation propre si un test existait déjà pour ce token.
        $test->messages()->delete();

        $welcome = "Bonjour, je suis la réceptionniste virtuelle de {$test->company_name}. Comment puis-je vous aider aujourd'hui ?";

        $test->messages()->create([
            'role' => 'assistant',
            'content' => $welcome,
        ]);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'welcome' => $welcome,
        ]);
    }

    /**
     * Reçoit un message du visiteur, appelle l'IA, renvoie la réponse.
     */
    public function chat(Request $request)
    {
        $data = $request->validate([
            'token'   => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        $test = IarecepTest::where('token', $data['token'])->latest()->first();

        if (! $test) {
            return response()->json(['error' => "Session de test introuvable."], 404);
        }

        if (! $test->isOpen()) {
            return response()->json([
                'error' => "Cette session de test est terminée. Merci de relancer un essai gratuit.",
            ], 410);
        }

        $test->messages()->create([
            'role' => 'user',
            'content' => $data['message'],
        ]);

        $history = $test->messages()->orderBy('id')->get(['role', 'content']);

        $reply = $this->callAi($test, $history);

        $test->messages()->create([
            'role' => 'assistant',
            'content' => $reply,
        ]);

        return response()->json(['reply' => $reply]);
    }

    /**
     * Marque le test comme "closed" quand le visiteur quitte/ferme l'onglet.
     * Les données NE sont PAS supprimées : seul le statut change, pour
     * empêcher toute reprise/mélange de conversation par un autre visiteur.
     */
    public function close(Request $request)
    {
        $token = $request->input('token');

        if ($token) {
            IarecepTest::where('token', $token)
                ->where('status', 'in_progress')
                ->update(['status' => 'closed']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Formulaire "Je suis satisfait, contactez-moi" en bas du chat.
     */
    public function requestDemo(Request $request)
    {
        $data = $request->validate([
            'token'   => 'required|string',
            'phone'   => 'nullable|string|max:30',
            'message' => 'nullable|string|max:1000',
        ]);

        $test = IarecepTest::where('token', $data['token'])->latest()->first();

        if (! $test) {
            return response()->json(['error' => "Session introuvable."], 404);
        }

        try {
            Mail::to(config('mail.from.address'))
                ->send(new IarecepDemandeMail($test, $data['phone'] ?? null, $data['message'] ?? null));
        } catch (\Throwable $e) {
            Log::error('Erreur envoi mail IARECEP: '.$e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'message' => "Merci ! Notre équipe vous contacte sous 24h.",
        ]);
    }

    /**
     * Appel au modèle IA. Adapte l'URL/le format selon ton fournisseur
     * (variables IARECEP_AI_* dans .env). Un fallback simulé est prévu
     * si la clé n'est pas configurée, pour pouvoir tester l'UI sans API.
     */
    private function callAi(IarecepTest $test, $history): string
    {
        $apiKey = config('services.iarecep_ai.key');
        $model  = config('services.iarecep_ai.model', 'claude-sonnet-4-6');

        $systemPrompt = "Tu es la réceptionniste virtuelle de l'entreprise \"{$test->company_name}\""
            .($test->sector ? ", active dans le secteur : {$test->sector}." : '.')
            ." Voici la description fournie par le responsable de l'entreprise : \"{$test->about}\"."
            ." Ton rôle : accueillir chaleureusement les client(e)s, répondre à leurs questions sur l'entreprise,"
            ." prendre leurs coordonnées et le motif de leur appel/message, proposer un rendez-vous si pertinent."
            ." Reste professionnelle, concise (3-4 phrases max) et chaleureuse. Réponds en français sauf si le"
            ." client écrit dans une autre langue. Ne dis jamais que tu es une IA de démonstration : incarne"
            ." pleinement la réceptionniste de {$test->company_name}.";

        if (! $apiKey) {
            // Fallback pour dev/démo sans clé API configurée.
            return "(Mode démo sans clé API) Merci pour votre message, un membre de {$test->company_name} "
                ."reviendra vers vous rapidement. Souhaitez-vous laisser vos coordonnées ?";
        }

        $messages = $history->map(fn ($m) => [
            'role' => $m->role,
            'content' => $m->content,
        ])->values()->all();

        try {
            $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->timeout(20)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 400,
                    'system' => $systemPrompt,
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::error('Erreur API IA IARECEP: '.$response->body());
                return "Désolé, je rencontre un souci technique momentané. Pouvez-vous reformuler votre demande ?";
            }

            $blocks = $response->json('content', []);
            $text = collect($blocks)->firstWhere('type', 'text')['text'] ?? null;

            return $text ?: "Désolé, je n'ai pas bien compris. Pouvez-vous préciser votre demande ?";
        } catch (\Throwable $e) {
            Log::error('Exception API IA IARECEP: '.$e->getMessage());
            return "Désolé, je rencontre un souci technique momentané. Pouvez-vous reformuler votre demande ?";
        }
    }
}