<?php
// app/Http/Controllers/IarecepController.php

namespace App\Http\Controllers;

use App\Mail\IarecepDemandeMail;
use App\Models\IarecepAppointment;
use App\Models\IarecepTest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class IarecepController extends Controller
{
    private const SLOTS = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00'];

    public function index()
    {
        if (! session()->has('iarecep_token')) {
            session(['iarecep_token' => (string) Str::uuid()]);
        }

        $token = session('iarecep_token');

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
     * Page "Calendrier" accessible depuis le menu principal.
     * Vue d'ensemble en LECTURE SEULE de tous les rendez-vous présents en
     * base (tous tests confondus) : aucune réservation possible ici, juste
     * un affichage complet, façon Google Agenda.
     */
    public function calendrier()
    {
        $appointments = IarecepAppointment::with('test:id,company_name')
            ->where('status', 'confirmed')
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->map(fn ($a) => [
                'date'      => $a->date->format('Y-m-d'),
                'date_fr'   => $a->date->format('d/m/Y'),
                'time'      => substr($a->time, 0, 5),
                'full_name' => $a->full_name,
                'company'   => $a->test->company_name ?? null,
            ])
            ->values();

        return view('calendrier', [
            'appointments' => $appointments,
        ]);
    }

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
     * Reçoit un message du visiteur. L'IA peut, pendant la conversation,
     * décider de réserver un rendez-vous (via tool use) si le client a
     * donné date, heure et nom. La réponse renvoie alors aussi le
     * rendez-vous créé pour que le calendrier se mette à jour tout seul.
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

        $result = $this->callAi($test, $history);

        $test->messages()->create([
            'role' => 'assistant',
            'content' => $result['text'],
        ]);

        return response()->json([
            'reply' => $result['text'],
            'appointment' => $result['appointment'], // null si aucune réservation n'a été faite
        ]);
    }

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
     * Liste des rendez-vous du test (calendrier manuel + réservations faites par le chatbot).
     */
    public function appointmentsIndex(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);

        $test = IarecepTest::where('token', $data['token'])->latest()->first();

        if (! $test) {
            return response()->json(['error' => "Session introuvable."], 404);
        }

        $appointments = IarecepAppointment::where('iarecep_test_id', $test->id)
            ->where('status', 'confirmed')
            ->orderBy('date')->orderBy('time')
            ->get(['date', 'time', 'full_name'])
            ->map(fn ($a) => [
                'date' => $a->date->format('Y-m-d'),
                'time' => substr($a->time, 0, 5),
                'full_name' => $a->full_name,
            ]);

        return response()->json(['appointments' => $appointments]);
    }

    /**
     * Réservation manuelle via le calendrier (reste disponible en option).
     */
    public function appointmentsStore(Request $request)
    {
        $data = $request->validate([
            'token'     => 'required|string',
            'date'      => 'required|date|after_or_equal:today',
            'time'      => 'required|date_format:H:i',
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'notes'     => 'nullable|string|max:500',
        ]);

        $test = IarecepTest::where('token', $data['token'])->latest()->first();

        if (! $test) {
            return response()->json(['error' => "Session introuvable."], 404);
        }

        $appointment = $this->createAppointmentIfFree($test, $data);

        if (! $appointment) {
            return response()->json(['error' => "Ce créneau vient d'être réservé, merci d'en choisir un autre."], 409);
        }

        return response()->json([
            'ok' => true,
            'appointment' => [
                'date' => $appointment->date->format('Y-m-d'),
                'time' => substr($appointment->time, 0, 5),
                'full_name' => $appointment->full_name,
            ],
            'message' => "Rendez-vous confirmé pour le {$appointment->date->format('d/m/Y')} à {$data['time']}.",
        ]);
    }

    /**
     * Crée un rendez-vous s'il n'y a pas de conflit. Retourne null si le créneau est déjà pris.
     */
    private function createAppointmentIfFree(IarecepTest $test, array $data): ?IarecepAppointment
    {
        $alreadyTaken = IarecepAppointment::where('iarecep_test_id', $test->id)
            ->where('date', $data['date'])
            ->where('time', $data['time'])
            ->where('status', 'confirmed')
            ->exists();

        if ($alreadyTaken) {
            return null;
        }

        return IarecepAppointment::create([
            'iarecep_test_id' => $test->id,
            'token'           => $test->token,
            'date'            => $data['date'],
            'time'            => $data['time'],
            'full_name'       => $data['full_name'],
            'phone'           => $data['phone'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'status'          => 'confirmed',
        ]);
    }

    /**
     * Appel IA avec tool use : le modèle peut appeler "book_appointment"
     * dès qu'il a récolté date + heure + nom du client dans la conversation.
     * Retourne ['text' => string, 'appointment' => array|null].
     */
    private function callAi(IarecepTest $test, $history): array
    {
        $apiKey = config('services.iarecep_ai.key');
        $model  = config('services.iarecep_ai.model', 'claude-sonnet-4-6');
        $today  = Carbon::today()->toDateString();
        $slotsList = implode(', ', self::SLOTS);

        $systemPrompt = "Tu es la réceptionniste virtuelle de l'entreprise \"{$test->company_name}\""
            .($test->sector ? ", active dans le secteur : {$test->sector}." : '.')
            ." Voici la description fournie par le responsable de l'entreprise : \"{$test->about}\"."
            ." Nous sommes aujourd'hui le {$today}."
            ." Ton rôle : accueillir chaleureusement les client(e)s, répondre à leurs questions, et si le client"
            ." souhaite prendre rendez-vous, lui proposer un créneau parmi ces horaires disponibles : {$slotsList}."
            ." Dès que tu as obtenu la date souhaitée, l'heure (parmi la liste ci-dessus) et le nom complet du"
            ." client, utilise l'outil book_appointment pour enregistrer le rendez-vous. Ne l'utilise que lorsque"
            ." ces trois informations sont confirmées par le client. Une fois l'outil exécuté, confirme oralement"
            ." le rendez-vous au client de façon naturelle. Si le créneau est indisponible, propose-lui un autre"
            ." horaire de la liste. Reste professionnelle, concise (3-4 phrases max) et chaleureuse. Réponds en"
            ." français sauf si le client écrit dans une autre langue. Ne dis jamais que tu es une IA de"
            ." démonstration : incarne pleinement la réceptionniste de {$test->company_name}.";

        if (! $apiKey) {
            return [
                'text' => "(Mode démo sans clé API) Merci pour votre message, un membre de {$test->company_name} "
                    ."reviendra vers vous rapidement. Souhaitez-vous laisser vos coordonnées ?",
                'appointment' => null,
            ];
        }

        $tools = [[
            'name' => 'book_appointment',
            'description' => "Réserve un rendez-vous pour le client une fois la date, l'heure et le nom confirmés.",
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'date'      => ['type' => 'string', 'description' => 'Date au format YYYY-MM-DD'],
                    'time'      => ['type' => 'string', 'description' => 'Heure au format HH:MM parmi les créneaux proposés'],
                    'full_name' => ['type' => 'string', 'description' => 'Nom complet du client'],
                    'phone'     => ['type' => 'string', 'description' => 'Téléphone du client, si donné'],
                    'notes'     => ['type' => 'string', 'description' => 'Motif du rendez-vous, si donné'],
                ],
                'required' => ['date', 'time', 'full_name'],
            ],
        ]];

        $messages = $history->map(fn ($m) => [
            'role' => $m->role,
            'content' => $m->content,
        ])->values()->all();

        try {
            $response = $this->callAnthropic($apiKey, $model, $systemPrompt, $messages, $tools);

            if (! $response) {
                return ['text' => "Désolé, je rencontre un souci technique momentané. Pouvez-vous reformuler ?", 'appointment' => null];
            }

            $blocks = $response->json('content', []);
            $toolUse = collect($blocks)->firstWhere('type', 'tool_use');

            // Pas d'appel d'outil : réponse texte simple.
            if (! $toolUse || $toolUse['name'] !== 'book_appointment') {
                $text = collect($blocks)->firstWhere('type', 'text')['text'] ?? null;
                return ['text' => $text ?: "Pouvez-vous préciser votre demande ?", 'appointment' => null];
            }

            // L'IA veut réserver : on valide et on exécute côté serveur.
            $input = $toolUse['input'];
            $appointmentResult = $this->handleBookingTool($test, $input);

            // On renvoie le résultat de l'outil à l'IA pour qu'elle formule la confirmation.
            $messages[] = ['role' => 'assistant', 'content' => $blocks];
            $messages[] = [
                'role' => 'user',
                'content' => [[
                    'type' => 'tool_result',
                    'tool_use_id' => $toolUse['id'],
                    'content' => json_encode($appointmentResult['toolResponse']),
                ]],
            ];

            $followUp = $this->callAnthropic($apiKey, $model, $systemPrompt, $messages, $tools);
            $followBlocks = $followUp?->json('content', []) ?? [];
            $finalText = collect($followBlocks)->firstWhere('type', 'text')['text'] ?? null;

            return [
                'text' => $finalText ?: $appointmentResult['fallbackText'],
                'appointment' => $appointmentResult['appointment'],
            ];
        } catch (\Throwable $e) {
            Log::error('Exception API IA IARECEP: '.$e->getMessage());
            return ['text' => "Désolé, je rencontre un souci technique momentané. Pouvez-vous reformuler ?", 'appointment' => null];
        }
    }

    private function callAnthropic(string $apiKey, string $model, string $system, array $messages, array $tools)
    {
        $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->timeout(20)
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 500,
                'system' => $system,
                'messages' => $messages,
                'tools' => $tools,
            ]);

        if ($response->failed()) {
            Log::error('Erreur API IA IARECEP: '.$response->body());
            return null;
        }

        return $response;
    }

    /**
     * Valide les données envoyées par l'IA et crée le rendez-vous si le créneau est libre.
     */
    private function handleBookingTool(IarecepTest $test, array $input): array
    {
        $date = $input['date'] ?? null;
        $time = $input['time'] ?? null;
        $fullName = $input['full_name'] ?? null;

        $isValidDate = $date && Carbon::createFromFormat('Y-m-d', $date, false) !== false
            && Carbon::parse($date)->greaterThanOrEqualTo(Carbon::today());
        $isValidTime = $time && in_array($time, self::SLOTS, true);

        if (! $isValidDate || ! $isValidTime || ! $fullName) {
            return [
                'appointment' => null,
                'toolResponse' => ['ok' => false, 'reason' => 'invalid_input'],
                'fallbackText' => "Je n'ai pas pu enregistrer le rendez-vous, certaines informations sont manquantes ou invalides.",
            ];
        }

        $appointment = $this->createAppointmentIfFree($test, [
            'date' => $date,
            'time' => $time,
            'full_name' => $fullName,
            'phone' => $input['phone'] ?? null,
            'notes' => $input['notes'] ?? null,
        ]);

        if (! $appointment) {
            return [
                'appointment' => null,
                'toolResponse' => ['ok' => false, 'reason' => 'slot_taken'],
                'fallbackText' => "Ce créneau vient d'être réservé, merci d'en choisir un autre.",
            ];
        }

        return [
            'appointment' => [
                'date' => $appointment->date->format('Y-m-d'),
                'time' => substr($appointment->time, 0, 5),
                'full_name' => $appointment->full_name,
            ],
            'toolResponse' => ['ok' => true],
            'fallbackText' => "Votre rendez-vous du {$appointment->date->format('d/m/Y')} à {$time} est confirmé.",
        ];
    }

    /**
     * Prompt figé utilisé UNIQUEMENT pour le widget de démo de la landing page.
     * Ici, l'IA se présente comme la réceptionniste d'IA DIAL elle-même
     * (auto-démonstration du produit), pas comme celle d'un client.
     */
    private function iarecepDemoSystemPrompt(): string
    {
        $today = Carbon::today()->translatedFormat('d/m/Y');

        return <<<PROMPT
Tu es Léa, la réceptionniste virtuelle officielle d'IA DIAL.

À PROPOS D'IA DIAL :
IA DIAL est une entreprise qui conçoit et met en place des réceptionnistes IA sur-mesure pour le compte
d'autres entreprises (commerces, cabinets, artisans, professions libérales, etc.). Concrètement, IA DIAL
fournit deux briques complémentaires, propulsées par le même moteur d'intelligence artificielle :
- un chat en ligne (widget texte) directement intégré au site du client, pour répondre aux visiteurs et
  prendre des rendez-vous automatiquement ;
- une réceptionniste vocale, joignable par téléphone, capable de répondre aux appels, renseigner les
  clients et planifier des rendez-vous, sans jamais laisser un appel sans réponse.

NOTRE PROMESSE :
- Mise en place en quelques heures seulement : pas de développement long ni de configuration complexe,
  le client fournit ses informations (activité, horaires, services) et son assistant est opérationnel
  très rapidement.
- Toujours là pour vous : l'assistant IA ne prend jamais de pause, ne rate jamais un appel ou un message,
  et reste disponible même en dehors des horaires humains classiques.
- Un accompagnement humain reste possible : notre équipe support est joignable de 8h à 18h pour toute
  question, ajustement ou assistance technique.

Nous sommes aujourd'hui le {$today}.

TON RÔLE DANS CETTE CONVERSATION :
Cette conversation est elle-même une démonstration live du produit : la personne qui te parle est un
visiteur du site qui teste concrètement ce qu'IA DIAL peut construire pour sa propre entreprise. Tu dois :
- Te présenter brièvement comme un exemple de ce qu'IA DIAL peut mettre en place pour n'importe quelle
  entreprise.
- Répondre avec enthousiasme et clarté aux questions sur le fonctionnement, les délais de mise en place,
  les deux canaux disponibles (chat + vocal), et les bénéfices concrets (aucun appel manqué, gain de temps,
  image professionnelle).
- Si la personne veut "prendre rendez-vous" pendant cette démo, explique-lui que ceci est une simulation :
  propose-lui plutôt de laisser son nom et son besoin pour qu'un membre de l'équipe IA DIAL la recontacte
  rapidement, en horaires d'ouverture (8h-18h).
- Rester concise (3 à 4 phrases maximum par réponse), chaleureuse, professionnelle, et donner envie de
  passer à l'étape suivante (essai gratuit / prise de contact).
- Répondre en français, sauf si la personne écrit dans une autre langue.
- Ne jamais inventer de tarif précis si on te le demande : indique qu'un devis personnalisé est établi
  selon les besoins, et proposer de mettre la personne en contact avec l'équipe.
PROMPT;
    }

    /**
     * Chat de démonstration de la landing page (widget "Mode test").
     * Contrairement à chat(), aucun IarecepTest n'est requis : l'historique
     * est simplement conservé en session, et le prompt système est figé
     * (celui d'IA DIAL lui-même, pas celui d'un client).
     */
    public function demoChat(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $apiKey = config('services.iarecep_ai.key');
        $model  = config('services.iarecep_ai.model', 'claude-sonnet-4-6');

        $history = session('iarecep_demo_history', []);
        $history[] = ['role' => 'user', 'content' => $data['message']];

        if (! $apiKey) {
            $reply = "(Mode démo sans clé API) Merci pour votre message ! IA DIAL met en place votre "
                ."réceptionniste IA en quelques heures. Notre équipe est joignable de 8h à 18h pour en discuter.";
        } else {
            try {
                $response = $this->callAnthropic(
                    $apiKey,
                    $model,
                    $this->iarecepDemoSystemPrompt(),
                    $history,
                    [] // pas d'outil de réservation dans cette démo
                );

                $blocks = $response?->json('content', []) ?? [];
                $reply = collect($blocks)->firstWhere('type', 'text')['text']
                    ?? "Pouvez-vous reformuler votre question ?";
            } catch (\Throwable $e) {
                Log::error('Erreur chat démo IA DIAL: '.$e->getMessage());
                $reply = "Désolée, un souci technique momentané. Pouvez-vous réessayer ?";
            }
        }

        $history[] = ['role' => 'assistant', 'content' => $reply];

        // On garde un historique raisonnable en session
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        session(['iarecep_demo_history' => $history]);

        return response()->json(['reply' => $reply]);
    }

    /**
     * Réinitialise la conversation de démo (ex: si le visiteur relance le test).
     */
    public function demoReset(Request $request)
    {
        session()->forget('iarecep_demo_history');

        return response()->json(['ok' => true]);
    }

    /**
     * Construit un assistant Vapi éphémère pour le mode vocal de l'essai gratuit.
     * Contrairement à l'assistant "Léa" de la landing (fixe, créé dans le dashboard
     * Vapi, qui utilise l'outil book_appointment réel), celui-ci est généré à la
     * volée avec le prompt de l'entreprise testée et un outil séparé
     * (book_appointment_essai) pour ne JAMAIS toucher aux rendez-vous clients réels.
     */
    public function vapiConfig(Request $request)
    {
        $data = $request->validate(['token' => 'required|string']);

        $test = IarecepTest::where('token', $data['token'])
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (! $test) {
            return response()->json(['error' => "Session de test introuvable."], 404);
        }

        $publicKey = config('services.vapi.public_key');

        if (! $publicKey) {
            return response()->json(['error' => "Le mode vocal n'est pas disponible pour le moment."], 503);
        }

        $today = Carbon::today()->toDateString();
        $slotsList = implode(', ', self::SLOTS);

        $systemPrompt = "Tu es la réceptionniste virtuelle de l'entreprise \"{$test->company_name}\""
            .($test->sector ? ", active dans le secteur : {$test->sector}." : '.')
            ." Voici la description fournie par le responsable de l'entreprise : \"{$test->about}\"."
            ." Nous sommes aujourd'hui le {$today}."
            ." Ceci est un appel de démonstration : la personne qui te parle teste ce que la réceptionniste"
            ." vocale d'IA DIAL peut faire pour {$test->company_name}. Incarne pleinement cette réceptionniste,"
            ." ne dis jamais que tu es une IA de démonstration."
            ." Si le client souhaite prendre rendez-vous, propose-lui un créneau parmi : {$slotsList}."
            ." Dès que la date, l'heure (parmi cette liste) et le nom complet du client sont confirmés"
            ." oralement, utilise l'outil book_appointment_essai. Si le créneau est indisponible, propose-en"
            ." un autre de la liste. Reste professionnelle, concise (2-3 phrases par réponse), vouvoie"
            ." systématiquement, n'utilise ni abréviation ni emoji à l'oral. Réponds en français, sauf si"
            ." le client parle une autre langue.";

        $assistant = [
            'name' => 'IADial – Essai '.$test->company_name,
            'firstMessage' => "Bonjour, je suis la réceptionniste virtuelle de {$test->company_name}. Comment puis-je vous aider aujourd'hui ?",
            'transcriber' => [
                'provider' => 'deepgram',
                'model' => 'nova-2',
                'language' => 'fr',
            ],
            'voice' => [
                // Voix "Pauline" (Cartesia, FR, féminine) : la même que
                // celle de l'assistant "Léa" du widget principal, récupérée
                // via GET https://api.vapi.ai/assistant/{id} pour garantir
                // une cohérence de voix entre la démo et le produit final.
                'provider' => 'cartesia',
                'voiceId' => '65b25c5d-ff07-4687-a04c-da2f43ef6fa9',
                'model' => 'sonic-3.5',
            ],
            'model' => [
                // TODO: vérifier dans le dashboard Vapi que "anthropic" + ce modèle
                // sont bien activés sur ton compte
                'provider' => 'anthropic',
                'model' => config('services.iarecep_ai.model', 'claude-sonnet-4-6'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                ],
                'tools' => [[
                    'type' => 'function',
                    'function' => [
                        'name' => 'book_appointment_essai',
                        'description' => "Réserve un rendez-vous de test une fois la date, l'heure et le nom confirmés.",
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'date' => ['type' => 'string', 'description' => 'Date au format YYYY-MM-DD'],
                                'time' => ['type' => 'string', 'description' => 'Heure au format HH:MM'],
                                'full_name' => ['type' => 'string', 'description' => 'Nom complet du client'],
                                'phone' => ['type' => 'string', 'description' => 'Téléphone, si donné'],
                                'notes' => ['type' => 'string', 'description' => 'Motif, si donné'],
                            ],
                            'required' => ['date', 'time', 'full_name'],
                        ],
                    ],
                    'server' => ['url' => url('/vapi/webhook')],
                ]],
            ],
            'server' => [
                'url' => url('/vapi/webhook'),
                'secret' => config('services.vapi.webhook_secret'),
            ],
            // Permet à VapiController de retrouver le bon IarecepTest lors du tool-call
            'metadata' => [
                'token' => $test->token,
                'source' => 'essai_gratuit_vocal',
            ],
        ];

        return response()->json([
            'publicKey' => $publicKey,
            'assistant' => $assistant,
        ]);
    }
}