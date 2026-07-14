<?php

namespace App\Http\Controllers;

use App\Mail\IarecepVapiNotificationMail;
use App\Models\IarecepAppointment;
use App\Models\IarecepTest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VapiController extends Controller
{
    private const SLOTS = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

    /**
     * Point d'entrée unique pour tous les événements serveur envoyés par Vapi
     * (appels d'outil "tool-calls" pendant la conversation, et
     * "end-of-call-report" à la fin de l'appel/chat).
     */
    public function webhook(Request $request)
    {
        if (! $this->isAuthentic($request)) {
            Log::warning('Vapi webhook: en-tête x-vapi-secret invalide ou absent.');
            return response()->json(['error' => 'unauthorized'], 401);
        }

        $message = $request->input('message', []);
        $type = $message['type'] ?? null;

        Log::info('Vapi webhook reçu', ['type' => $type]);

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
            // Aucun secret configuré : à éviter en production, mais on ne bloque
            // pas le développement local si tu n'as pas encore réglé .env.
            return true;
        }

        $received = $request->header('x-vapi-secret', '');

        return hash_equals($expected, $received);
    }

    /**
     * Gère les appels d'outils déclenchés pendant la conversation
     * (actuellement uniquement book_appointment).
     */
    private function handleToolCalls(array $message)
{
    $results = [];

    foreach ($message['toolCallList'] ?? [] as $toolCall) {
        $id = $toolCall['id'] ?? null;
        $function = $toolCall['function'] ?? [];
        $name = $function['name'] ?? null;

        $rawArguments = $function['arguments'] ?? [];
        $arguments = is_string($rawArguments)
            ? (json_decode($rawArguments, true) ?? [])
            : $rawArguments;

        Log::info('Tool call reçu', ['name' => $name, 'arguments' => $arguments, 'raw' => $toolCall]);

        $result = match ($name) {
            'book_appointment' => $this->bookAppointment($arguments, $message),
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

   private function bookAppointment(array $args, array $message): string
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

    $alreadyTaken = IarecepAppointment::where('date', $date)
        ->where('time', $time)
        ->whereIn('status', ['confirmed', 'confirmed_vapi'])
        ->exists();

    if ($alreadyTaken) {
        Log::info('bookAppointment: créneau déjà pris', compact('date', 'time'));
        return "Le créneau du {$date} à {$time} vient d'être pris. Merci de proposer un autre "
            .'horaire parmi : '.implode(', ', self::SLOTS);
    }

    try {
        $vapiTest = $this->vapiSystemTest();

        $appointment = IarecepAppointment::create([
            'iarecep_test_id' => $vapiTest->id,
            'token' => $vapiTest->token,
            'source' => 'vapi',
            'date' => $date,
            'time' => $time,
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'notes' => $notes,
            'status' => 'confirmed_vapi',
        ]);

        Log::info('bookAppointment: RDV créé avec succès', ['id' => $appointment->id]);
    } catch (\Throwable $e) {
        Log::error('bookAppointment: échec insertion', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return "Désolé, une erreur technique m'empêche de finaliser la réservation. Merci de réessayer.";
    }

    $this->notifyByEmail('Nouveau rendez-vous pris via l\'assistant vocal Vapi', [
        'Nom du client' => $fullName,
        'Email' => $email,
        'Téléphone' => $phone ?: '—',
        'Date' => $appointment->date->format('d/m/Y'),
        'Heure' => substr($appointment->time, 0, 5),
        'Motif' => $notes ?: '—',
        'ID appel Vapi' => $message['call']['id'] ?? '—',
    ]);

    return "Rendez-vous confirmé pour {$fullName} le {$date} à {$time}.";
}
    /**
     * À la fin de chaque appel/chat Vapi, on envoie systématiquement un email
     * récapitulatif (avec ou sans rendez-vous pris).
     */
    private function handleEndOfCallReport(array $message)
    {
        $summary = $message['analysis']['summary'] ?? null;
        $transcriptMessages = $message['artifact']['messages'] ?? [];
        $transcriptText = collect($transcriptMessages)
            ->filter(fn ($m) => isset($m['message']) && in_array($m['role'] ?? null, ['user', 'bot', 'assistant']))
            ->map(fn ($m) => ($m['role'] ?? '?').' : '.$m['message'])
            ->implode("\n");

        $customerNumber = $message['call']['customer']['number'] ?? null;

        $this->notifyByEmail('Nouvelle conversation Vapi terminée', [
            'Résumé' => $summary ?: 'Non disponible',
            'Téléphone client' => $customerNumber ?: '—',
            'Raison de fin' => $message['endedReason'] ?? '—',
            'ID appel' => $message['call']['id'] ?? '—',
            'Transcript' => $transcriptText ?: 'Non disponible',
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * "Client" système représentant l'assistant Vapi de la landing page,
     * pour rattacher proprement les rendez-vous en base sans toucher au schéma.
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

    private function notifyByEmail(string $subject, array $data): void
    {
        $to = config('services.vapi.notify_email');

        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send(new IarecepVapiNotificationMail($subject, $data));
        } catch (\Throwable $e) {
            Log::error('Erreur envoi mail Vapi: '.$e->getMessage());
        }
    }
}