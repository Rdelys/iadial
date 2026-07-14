{{-- resources/views/tarifs.blade.php --}}
@extends('layouts.app')

@section('title', "Tarifs - IADial")
@section('description', "Découvrez les offres IADial : Starter, Pro et Business. Un réceptionniste IA disponible 24h/24, sans engagement.")

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 pt-20 pb-14 text-center">
    <div class="hero-in-1">
        <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-8">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-400"></span>
            </span>
            Sans engagement &middot; Sans carte bancaire pour l'essai
        </span>
    </div>

    <h1 class="hero-in-2 font-display text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight leading-[1.15]">
        Des tarifs clairs,
        <span class="block mt-2 bg-gradient-to-r from-sky-400 via-indigo-400 to-indigo-500 bg-clip-text text-transparent text-gradient-animated">
            adaptés à votre activité
        </span>
    </h1>

    <p class="hero-in-3 mt-5 text-white/50 text-base sm:text-lg leading-relaxed max-w-xl mx-auto">
        Choisissez la formule qui correspond à votre volume d'appels. Vous pouvez changer d'offre ou résilier à tout moment.
    </p>
</section>

{{-- ===================== CARTES TARIFS ===================== --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <div class="reveal-group grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 items-stretch">

        {{-- STARTER --}}
        <div class="reveal card-hover relative flex flex-col rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm p-8">
            <h3 class="font-display text-xl font-semibold">IADial Starter</h3>
            <p class="text-white/40 text-sm mt-1">Pour démarrer sereinement</p>

            <div class="mt-6 flex items-baseline gap-1">
                <span class="font-display text-4xl font-bold">399&nbsp;€</span>
                <span class="text-white/40 text-sm">/mois</span>
            </div>
            <p class="mt-2 font-mono text-[11px] text-sky-400/70">1200&nbsp;min &middot; ~40 appels</p>

            <ul class="mt-8 space-y-3 text-sm text-white/70 flex-1">
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Répondeur IA 24h/24, 7j/7
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Prise de RDV automatique (Google Agenda)
                </li>
                <li class="flex items-start gap-2.5 text-white/30">
                    <x-tarif-cross />
                    Accueil et échange par chat
                </li>
                <li class="flex items-start gap-2.5 text-white/30">
                    <x-tarif-cross />
                    Transfert d'urgence
                </li>
                <li class="flex items-start gap-2.5 text-white/30">
                    <x-tarif-cross />
                    Création de site web
                </li>
            </ul>

            <a href="{{ route('iarecep.index') }}"
               class="btn-shine mt-8 inline-flex items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/5
                   px-5 py-3 text-sm font-medium text-white hover:bg-white/10 transition">
                Choisir Starter
            </a>
            <p class="mt-3 text-[11px] text-white/30">Sans engagement</p>
        </div>

        {{-- PRO (mis en avant) --}}
        <div class="reveal card-hover relative flex flex-col rounded-2xl border border-sky-400/40 bg-gradient-to-b from-sky-400/[0.07] to-indigo-500/[0.05] backdrop-blur-sm p-8 md:-translate-y-3 shadow-[0_0_40px_-10px_rgba(52,226,192,0.25)]">
            <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 px-4 py-1 text-[11px] font-semibold text-black">
                Le plus populaire
            </span>

            <h3 class="font-display text-xl font-semibold">IADial Pro</h3>
            <p class="text-white/40 text-sm mt-1">Pour les entreprises en croissance</p>

            <div class="mt-6 flex items-baseline gap-1">
                <span class="font-display text-4xl font-bold">599&nbsp;€</span>
                <span class="text-white/40 text-sm">/mois</span>
            </div>
            <p class="mt-2 font-mono text-[11px] text-sky-400/70">3600&nbsp;min &middot; ~120 appels</p>

            <ul class="mt-8 space-y-3 text-sm text-white/70 flex-1">
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Répondeur IA 24h/24, 7j/7
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Prise de RDV automatique (Google Agenda)
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Accueil et échange par chat
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Transfert d'urgence
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Page vitrine offerte <span class="text-white/40">(livrée sous 72h)</span>
                </li>
            </ul>

            <a href="{{ route('iarecep.index') }}"
               class="btn-shine mt-8 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                   px-5 py-3 text-sm font-semibold text-black shadow-[0_0_20px_rgba(52,226,192,0.35)]
                   hover:shadow-[0_0_30px_rgba(52,226,192,0.55)] transition-shadow">
                Choisir Pro
            </a>
            <p class="mt-3 text-[11px] text-white/30">Sans engagement</p>
        </div>

        {{-- BUSINESS --}}
        <div class="reveal card-hover relative flex flex-col rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-sm p-8">
            <h3 class="font-display text-xl font-semibold">IADial Business</h3>
            <p class="text-white/40 text-sm mt-1">Pour une présence complète</p>

            <div class="mt-6 flex items-baseline gap-1">
                <span class="font-display text-4xl font-bold">999&nbsp;€</span>
                <span class="text-white/40 text-sm">/mois</span>
            </div>
            <p class="mt-2 font-mono text-[11px] text-sky-400/70">Appels illimités</p>

            <ul class="mt-8 space-y-3 text-sm text-white/70 flex-1">
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Répondeur IA 24h/24, 7j/7
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Prise de RDV automatique (Google Agenda)
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Accueil et échange par chat
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Transfert d'urgence
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Site vitrine + galerie + avis <span class="text-white/40">(livré sous 96h)</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <x-tarif-check />
                    Fonctionnalités sur devis
                </li>
            </ul>

            <a href="#contact"
               class="btn-shine mt-8 inline-flex items-center justify-center gap-2 rounded-lg border border-white/15 bg-white/5
                   px-5 py-3 text-sm font-medium text-white hover:bg-white/10 transition">
                Demander un devis
            </a>
            <p class="mt-3 text-[11px] text-white/30">Sans engagement</p>
        </div>

    </div>
</section>

{{-- ===================== TABLEAU COMPARATIF ===================== --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 pb-20">
    <div class="reveal text-center mb-10">
        <h2 class="font-display text-2xl sm:text-3xl font-bold">Comparatif détaillé</h2>
        <p class="text-white/40 text-sm mt-2">Toutes les fonctionnalités, offre par offre.</p>
    </div>

    <div class="reveal overflow-x-auto rounded-2xl border border-white/10">
        <table class="w-full text-sm text-left border-collapse min-w-[640px]">
            <thead>
                <tr class="bg-white/[0.04] text-white/70">
                    <th class="px-5 py-4 font-medium">Fonctionnalité</th>
                    <th class="px-5 py-4 font-display font-semibold text-center">Starter</th>
                    <th class="px-5 py-4 font-display font-semibold text-center text-sky-300">Pro</th>
                    <th class="px-5 py-4 font-display font-semibold text-center">Business</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <tr>
                    <td class="px-5 py-4 text-white/60">Tarif / mois</td>
                    <td class="px-5 py-4 text-center font-mono">399&nbsp;€</td>
                    <td class="px-5 py-4 text-center font-mono bg-sky-400/[0.04]">599&nbsp;€</td>
                    <td class="px-5 py-4 text-center font-mono">999&nbsp;€</td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Volume d'appels</td>
                    <td class="px-5 py-4 text-center">1200&nbsp;min <span class="text-white/30">(~40 appels)</span></td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04]">3600&nbsp;min <span class="text-white/30">(~120 appels)</span></td>
                    <td class="px-5 py-4 text-center">Illimité</td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Répondeur IA 24/7</td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04]"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Prise de RDV (Google Agenda)</td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04]"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Accueil et échange par chat</td>
                    <td class="px-5 py-4 text-center"><x-tarif-cross inline /></td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04]"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Transfert d'urgence</td>
                    <td class="px-5 py-4 text-center"><x-tarif-cross inline /></td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04]"><x-tarif-check inline /></td>
                    <td class="px-5 py-4 text-center"><x-tarif-check inline /></td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Offre création de site</td>
                    <td class="px-5 py-4 text-center text-white/30">Non</td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04] text-white/70">Page vitrine <span class="block text-white/30 text-xs">livrée sous 72h</span></td>
                    <td class="px-5 py-4 text-center text-white/70">Vitrine + galerie + avis <span class="block text-white/30 text-xs">livrée sous 96h</span></td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Autres fonctionnalités site</td>
                    <td class="px-5 py-4 text-center text-white/30">Non</td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04] text-white/30">Non</td>
                    <td class="px-5 py-4 text-center text-white/70">Sur devis</td>
                </tr>
                <tr>
                    <td class="px-5 py-4 text-white/60">Engagement</td>
                    <td class="px-5 py-4 text-center text-sky-300/80">Sans engagement</td>
                    <td class="px-5 py-4 text-center bg-sky-400/[0.04] text-sky-300/80">Sans engagement</td>
                    <td class="px-5 py-4 text-center text-sky-300/80">Sans engagement</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Note --}}
    <div class="reveal mt-6 rounded-xl border border-white/10 bg-white/[0.02] px-5 py-4 text-xs sm:text-sm text-white/40 leading-relaxed">
        <p>Vous souhaitez la création d'un site internet&nbsp;? Cochez l'option correspondante lors de votre commande.</p>
        <p class="mt-1">Un devis spécifique est établi pour l'offre Business en cas d'intégration de fonctionnalités e-commerce.</p>
    </div>
</section>

{{-- ===================== CTA FINAL ===================== --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 pb-24">
    <div class="reveal relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-sky-400/10 via-transparent to-indigo-500/10 px-6 sm:px-12 py-14 text-center">
        <div class="glow-orb absolute -top-24 left-1/2 -translate-x-1/2 w-[500px] h-[500px] pointer-events-none"></div>
        <div class="relative">
            <h2 class="font-display text-2xl sm:text-3xl font-bold">Prêt à ne plus manquer un appel&nbsp;?</h2>
            <p class="mt-3 text-white/50 max-w-md mx-auto">
                Testez IADial gratuitement, sans carte bancaire, et configurez votre réceptionniste IA en moins de 2 minutes.
            </p>
            <a href="{{ route('iarecep.index') }}"
               class="btn-shine mt-8 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                   px-6 py-3 text-sm font-semibold text-black shadow-[0_0_20px_rgba(52,226,192,0.35)]
                   hover:shadow-[0_0_30px_rgba(52,226,192,0.55)] transition-shadow">
                Essayer gratuitement
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection