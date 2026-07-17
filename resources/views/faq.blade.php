@extends('layouts.app')

@section('title', "FAQ - IADial")

@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-16 pb-24" x-data="{ open: null }">
    <div class="reveal text-center mb-12">
        <h1 class="font-display text-2xl sm:text-3xl md:text-4xl font-bold">Questions fréquentes</h1>
        <p class="mt-3 text-white/50 text-sm sm:text-base">Tout ce qu'il faut savoir sur IADial.</p>
    </div>

    <div class="reveal-group space-y-3">
        @foreach ([
            ['q' => "Comment fonctionne le réceptionniste IA ?", 'a' => "IADial répond à vos clients par téléphone et par chat, comprend leur demande grâce à l'intelligence artificielle, répond aux questions fréquentes, prend des rendez-vous et transfère vers un humain si besoin."],
            ['q' => "Ai-je besoin d'une carte bancaire pour l'essai gratuit ?", 'a' => "Non, l'essai gratuit ne nécessite aucune carte bancaire. Vous pouvez tester toutes les fonctionnalités en mode démo."],
            ['q' => "Quels moyens de paiement sont acceptés ?", 'a' => "Le paiement se fait via Papi : Mvola, Orange Money, Airtel Money et carte bancaire."],
            ['q' => "Puis-je changer d'offre à tout moment ?", 'a' => "Oui, vous pouvez changer d'offre ou résilier à tout moment depuis votre profil, sans engagement."],
            ['q' => "Comment fonctionne la prise de rendez-vous automatique ?", 'a' => "Votre réceptionniste IA propose des créneaux disponibles, confirme le rendez-vous avec le client et l'ajoute automatiquement à votre calendrier, sans risque de double réservation."],
            ['q' => "L'offre Business est-elle facturée au même tarif que les autres ?", 'a' => "Non, l'offre Business est établie sur devis selon les fonctionnalités que vous souhaitez (site vitrine, galerie, avis, e-commerce, etc.)."],
        ] as $i => $item)
            <div class="reveal rounded-xl border border-white/10 bg-white/[0.03] overflow-hidden">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left">
                    <span class="font-medium text-sm sm:text-base">{{ $item['q'] }}</span>
                    <svg class="w-4 h-4 text-sky-300 shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse class="px-5 pb-4 text-sm text-white/50 leading-relaxed">
                    {{ $item['a'] }}
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection