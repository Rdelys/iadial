<?php

namespace App\Http\Controllers;

use App\Mail\IarecepVapiNotificationMail;
use App\Models\IarecepAppointment;
use App\Models\IarecepTest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VapiController extends Controller
{
    private const SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

    /**
     * Point d'entrée unique pour tous les événements serveur envoyés par Vapi,
     * pour TOUS les assistants (landing "Léa" + un assistant par client).
     */
    public function webhook(Request $request)
    {
        if (! $this->isAuthentic($request)) {
            Log::warning('Vapi webhook: en-tête x-vapi-secret invalide ou absent.');
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $message = $request->input('message', []);
        $type = $message['type'] ?? null;

        Log::info('Vapi webhook reçu', ['type' => $type, 'assistantId' => $this->extractAssistantId($message)]);

        return match ($type) {
            'tool-calls' => $this->handleToolCalls($message),
            'end-of-call-report' => $this->handleEndOfCallReport($message),
            default => response()->json(['ok' => true]),
        };
    }

    private function isAuthentic(Request $request): bool
    {
        $expected = config('services.vapi.webhook_secret');

        if (! $expected) {
            return true;
        }

        $received = $request->header('x-vapi-secret', '');

        return hash_equals($expected, $received);
    }

    /**
     * Récupère l'assistantId quel que soit l'endroit où Vapi le place selon
     * le type d'événement (tool-calls vs end-of-call-report).
     */
    private function extractAssistantId(array $message): ?string
    {
        return $message['assistant']['id']
            ?? $message['call']['assistantId']
            ?? $message['assistantId']
            ?? null;
    }

    /**
     * Identifie le client propriétaire de l'appel via l'assistantId Vapi.
     * Retourne null si c'est l'assistant "Léa" de la landing (config globale)
     * ou si aucun client ne correspond.
     */
    private function resolveOwnerUser(array $message): ?User
    {
        $assistantId = $this->extractAssistantId($message);

        if (! $assistantId) {
            return null;
        }

        // Si c'est l'assistant global de la landing IA DIAL, ce n'est pas un client.
        if ($assistantId === config('services.vapi.assistant_id')) {
            return null;
        }

        return User::where('vapi_assistant_id', $assistantId)->first();
    }

    /**
     * Gère les appels d'outils déclenchés pendant la conversation.
     * - book_appointment        : landing "Léa" OU un assistant client (résolu via assistantId)
     * - book_appointment_essai  : mode vocal de l'essai gratuit (simple test)
     */
    private function handleToolCalls(array $message)
    {
        $results = [];
        $ownerUser = $this->resolveOwnerUser($message);

        foreach ($message['toolCallList'] ?? [] as $toolCall) {
            $id = $toolCall['id'] ?? null;
            $function = $toolCall['function'] ?? [];
            $name = $function['name'] ?? null;

            $rawArguments = $function['arguments'] ?? [];
            $arguments = is_string($rawArguments)
                ? (json_decode($rawArguments, true) ?? [])
                : $rawArguments;

            Log::info('Tool call reçu', [
                'name' => $name,
                'arguments' => $arguments,
                'owner_user_id' => $ownerUser?->id,
                'raw' => $toolCall,
            ]);

            $result = match ($name) {
                'book_appointment' => $this->bookAppointment($arguments, $message, $ownerUser),
                'book_appointment_essai' => $this->bookAppointmentEssai($arguments, $message),
                default => "Outil inconnu : {$name}",
            };

            Log::info('Résultat tool call', ['name' => $name, 'result' => $result]);

            $results[] = [
                'toolCallId' => $id,
                'result' => $result,
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Réservation réelle.
     * - Si $ownerUser est renseigné : c'est l'assistant d'un CLIENT (configuré
     *   par l'admin dans /admin/clients). Le RDV est rattaché à ce client et
     *   l'email part vers le client (pas vers IA DIAL).
     * - Si $ownerUser est null : c'est l'assistant "Léa" de la landing IA DIAL
     *   (comportement historique, inchangé).
     */
    private function bookAppointment(array $args, array $message, ?User $ownerUser = null): string
    {
        $date = $args['date'] ?? null;
        $time = $args['time'] ?? null;
        $fullName = $args['full_name'] ?? null;
        $email = $args['email'] ?? null;
        $phone = $args['phone'] ?? null;
        $notes = $args['notes'] ?? null;

        $isValidDate = false;
        if ($date) {
            try {
                $parsed = Carbon::createFromFormat('Y-m-d', $date);
                $isValidDate = $parsed->format('Y-m-d') === $date
                    && $parsed->greaterThanOrEqualTo(Carbon::today());
            } catch (\Throwable $e) {
                $isValidDate = false;
            }
        }

        $isValidTime = $time && in_array($time, self::SLOTS, true);
        $isValidEmail = $email && filter_var($email, FILTER_VALIDATE_EMAIL);

        if (! $isValidDate || ! $isValidTime || ! $fullName || ! $isValidEmail) {
            Log::warning('bookAppointment: validation échouée', compact(
                'date', 'time', 'fullName', 'email', 'isValidDate', 'isValidTime', 'isValidEmail'
            ));
            return "Je n'ai pas pu réserver : la date, l'heure, le nom ou l'email sont manquants ou "
                .'invalides. Créneaux valides : '.implode(', ', self::SLOTS);
        }

        // Les créneaux déjà pris sont vérifiés PAR CLIENT : deux clients différents
        // peuvent avoir un RDV sur le même créneau, chacun dans son propre agenda.
        $slotQuery = IarecepAppointment::where('date', $date)
            ->where('time', $time)
            ->whereIn('status', ['confirmed', 'confirmed_vapi']);

        $slotQuery = $ownerUser
            ? $slotQuery->where('user_id', $ownerUser->id)
            : $slotQuery->whereNull('user_id');

        if ($slotQuery->exists()) {
            Log::info('bookAppointment: créneau déjà pris', ['date' => $date, 'time' => $time, 'owner_user_id' => $ownerUser?->id]);
            return "Le créneau du {$date} à {$time} vient d'être pris. Merci de proposer un autre "
                .'horaire parmi : '.implode(', ', self::SLOTS);
        }

        try {
            $vapiTest = $this->vapiSystemTest();

            $appointment = IarecepAppointment::create([
                'user_id' => $ownerUser?->id,
                'iarecep_test_id' => $vapiTest->id,
                'token' => $vapiTest->token,
                'source' => $ownerUser ? 'vapi_client' : 'vapi',
                'date' => $date,
                'time' => $time,
                'full_name' => $fullName,
                'phone' => $phone,
                'email' => $email,
                'notes' => $notes,
                'status' => 'confirmed_vapi',
            ]);

            Log::info('bookAppointment: RDV créé avec succès', ['id' => $appointment->id, 'owner_user_id' => $ownerUser?->id]);
        } catch (\Throwable $e) {
            Log::error('bookAppointment: échec insertion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return "Désolé, une erreur technique m'empêche de finaliser la réservation. Merci de réessayer.";
        }

        $notificationData = [
            'Nom du client' => $fullName,
            'Email' => $email,
            'Téléphone' => $phone ?: '—',
            'Date' => $appointment->date->format('d/m/Y'),
            'Heure' => substr($appointment->time, 0, 5),
            'Motif' => $notes ?: '—',
            'ID appel Vapi' => $message['call']['id'] ?? '—',
        ];

        if ($ownerUser) {
            // Assistant d'un client IA DIAL : on notifie CE client sur son email de compte.
            if ($ownerUser->email) {
                $this->notifyByEmail(
                    'Nouveau rendez-vous pris via votre assistant IA',
                    $notificationData + ['Entreprise' => $ownerUser->company_name ?? $ownerUser->name],
                    $ownerUser->email
                );
            }
        } else {
            // Assistant "Léa" de la landing IA DIAL : comportement historique.
            $this->notifyByEmail('Nouveau rendez-vous pris via l\'assistant vocal Vapi', $notificationData);
        }

        return "Rendez-vous confirmé pour {$fullName} le {$date} à {$time}.";
    }

    /**
     * Réservation de TEST : uniquement pour le mode vocal de l'essai gratuit
     * (assistant Vapi éphémère généré par IarecepController::vapiConfig).
     * Inchangé.
     */
    private function bookAppointmentEssai(array $args, array $message): string
    {
        $date = $args['date'] ?? null;
        $time = $args['time'] ?? null;
        $fullName = $args['full_name'] ?? null;
        $phone = $args['phone'] ?? null;
        $notes = $args['notes'] ?? null;

        $isValidDate = false;
        if ($date) {
            try {
                $parsed = Carbon::createFromFormat('Y-m-d', $date);
                $isValidDate = $parsed->format('Y-m-d') === $date
                    && $parsed->greaterThanOrEqualTo(Carbon::today());
            } catch (\Throwable $e) {
                $isValidDate = false;
            }
        }

        $isValidTime = $time && in_array($time, self::SLOTS, true);

        if (! $isValidDate || ! $isValidTime || ! $fullName) {
            Log::warning('bookAppointmentEssai: validation échouée', compact(
                'date', 'time', 'fullName', 'isValidDate', 'isValidTime'
            ));
            return "Je n'ai pas pu réserver : la date, l'heure ou le nom sont manquants ou "
                .'invalides. Créneaux valides : '.implode(', ', self::SLOTS);
        }

        $token = $message['call']['metadata']['token']
            ?? $message['assistant']['metadata']['token']
            ?? null;

        $test = $token ? IarecepTest::where('token', $token)->latest()->first() : null;

        if (! $test) {
            Log::warning('bookAppointmentEssai: session de test introuvable', ['token' => $token]);
            return "Désolé, je ne retrouve pas votre session d'essai. Merci de recharger la page et de réessayer.";
        }

        $alreadyTaken = IarecepAppointment::where('iarecep_test_id', $test->id)
            ->where('date', $date)
            ->where('time', $time)
            ->where('status', 'confirmed')
            ->exists();

        if ($alreadyTaken) {
            Log::info('bookAppointmentEssai: créneau déjà pris', compact('date', 'time'));
            return "Le créneau du {$date} à {$time} vient d'être pris. Merci de proposer un autre "
                .'horaire parmi : '.implode(', ', self::SLOTS);
        }

        try {
            $appointment = IarecepAppointment::create([
                'iarecep_test_id' => $test->id,
                'token' => $test->token,
                'source' => 'vapi_essai',
                'date' => $date,
                'time' => $time,
                'full_name' => $fullName,
                'phone' => $phone,
                'notes' => $notes,
                'status' => 'confirmed',
            ]);

            Log::info('bookAppointmentEssai: RDV créé avec succès', ['id' => $appointment->id, 'test_id' => $test->id]);
        } catch (\Throwable $e) {
            Log::error('bookAppointmentEssai: échec insertion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return "Désolé, une erreur technique m'empêche de finaliser la réservation. Merci de réessayer.";
        }

        return "Rendez-vous confirmé pour {$fullName} le {$date} à {$time}.";
    }

    /**
     * À la fin de chaque appel/chat Vapi, on envoie un email récapitulatif
     * — sauf pour les essais gratuits, qui n'intéressent que le test lui-même.
     * Pour un assistant CLIENT, le récap part au client, pas à IA DIAL.
     */
    private function handleEndOfCallReport(array $message)
    {
        $token = $message['call']['metadata']['token']
            ?? $message['assistant']['metadata']['token']
            ?? null;

        // Appel d'essai gratuit : aucun récap.
        if ($token) {
            return response()->json(['ok' => true]);
        }

        $summary = $message['analysis']['summary'] ?? null;
        $transcriptMessages = $message['artifact']['messages'] ?? [];
        $transcriptText = collect($transcriptMessages)
            ->filter(fn ($m) => isset($m['message']) && in_array($m['role'] ?? null, ['user', 'bot', 'assistant']))
            ->map(fn ($m) => ($m['role'] ?? '?').' : '.$m['message'])
            ->implode("\n");

        $customerNumber = $message['call']['customer']['number'] ?? null;

        $data = [
            'Résumé' => $summary ?: 'Non disponible',
            'Téléphone client' => $customerNumber ?: '—',
            'Raison de fin' => $message['endedReason'] ?? '—',
            'ID appel' => $message['call']['id'] ?? '—',
            'Transcript' => $transcriptText ?: 'Non disponible',
        ];

        $ownerUser = $this->resolveOwnerUser($message);

        if ($ownerUser) {
            if ($ownerUser->email) {
                $this->notifyByEmail(
                    'Nouvelle conversation avec votre assistant IA',
                    $data + ['Entreprise' => $ownerUser->company_name ?? $ownerUser->name],
                    $ownerUser->email
                );
            }
        } else {
            $this->notifyByEmail('Nouvelle conversation Vapi terminée', $data);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * "Client" système représentant l'assistant Vapi de la landing page,
     * pour rattacher proprement les rendez-vous en base sans toucher au schéma.
     * Utilisé aussi bien pour Léa que pour les assistants clients (le lien
     * réel avec le client se fait via iarecep_appointments.user_id).
     */
    private function vapiSystemTest(): IarecepTest
    {
        return IarecepTest::firstOrCreate(
            ['token' => 'vapi-global-assistant'],
            [
                'company_name' => 'IA DIAL',
                'full_name' => 'Assistant Vapi',
                'email' => config('services.vapi.notify_email'),
                'status' => 'in_progress',
            ]
        );
    }

    /**
     * Envoie un email de notification. Si $to est omis, part vers l'email
     * admin global configuré dans services.vapi.notify_email.
     */
    private function notifyByEmail(string $subject, array $data, ?string $to = null): void
    {
        $recipient = $to ?: config('services.vapi.notify_email');

        if (! $recipient) {
            return;
        }

        try {
            Mail::to($recipient)->send(new IarecepVapiNotificationMail($subject, $data));
        } catch (\Throwable $e) {
            Log::error('Erreur envoi mail Vapi: '.$e->getMessage());
        }
    }
}