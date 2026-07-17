@extends('layouts.app')

@section('title', "Mon profil - IADial")

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 pt-16 pb-24">
    <div class="reveal flex items-center justify-between flex-wrap gap-4 mb-10">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-3">
                <span class="relative flex h-2 w-2">
                    <span class="online-dot absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
                Compte actif &middot; {{ $user->plan_label ?? 'Aucune offre' }}
            </span>
            <h1 class="font-display text-2xl sm:text-3xl font-bold">Bonjour {{ $user->name }} 👋</h1>
        </div>
        <a href="{{ route('iarecep.calendrier') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/15 px-5 py-2.5 text-sm text-white/80 hover:bg-white/5">
            Voir mes rendez-vous
        </a>
    </div>

    <div class="reveal-group grid sm:grid-cols-2 gap-6">
        <div class="reveal rounded-2xl border border-white/10 bg-white/[0.03] p-6">
            <h3 class="font-display font-semibold mb-4">Informations de l'entreprise</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-white/40">Raison sociale</dt><dd class="text-white/80">{{ $user->company_name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-white/40">Secteur</dt><dd class="text-white/80">{{ $user->sector ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-white/40">Adresse</dt><dd class="text-white/80 text-right">{{ $user->address ?? '—' }}, {{ $user->city ?? '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-white/40">Téléphone</dt><dd class="text-white/80">{{ $user->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-white/40">E-mail</dt><dd class="text-white/80">{{ $user->email }}</dd></div>
            </dl>
        </div>

        <div class="reveal rounded-2xl border border-sky-400/25 bg-sky-400/[0.05] p-6">
            <h3 class="font-display font-semibold mb-4">Votre abonnement</h3>
            <p class="font-display text-xl font-bold">{{ $user->plan_label ?? 'Aucune offre active' }}</p>
            <p class="text-sm text-white/50 mt-1">
                Actif depuis le {{ $user->subscribed_at?->translatedFormat('d F Y') ?? '—' }}
            </p>
            <a href="{{ route('tarifs') }}" class="mt-5 inline-flex items-center gap-2 text-sm text-sky-300 hover:underline">
                Changer d'offre
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>
@endsection