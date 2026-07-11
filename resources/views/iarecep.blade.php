@extends('layouts.app')

@section('title', "Essai gratuit - IADial")

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-20 pb-28 text-center">

    <div class="hero-in-1">
        <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-8">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-400"></span>
            </span>
            Essai gratuit &middot; sans engagement
        </span>
    </div>

    <h1 class="hero-in-2 font-display text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight leading-[1.15]">
        Créez votre réceptionniste IA
        <span class="block mt-2 bg-gradient-to-r from-sky-400 via-blue-400 to-indigo-400 bg-clip-text text-transparent text-gradient-animated">
            en moins de 2 minutes
        </span>
    </h1>

    <p class="hero-in-3 mt-5 text-white/50 text-base sm:text-lg leading-relaxed max-w-lg mx-auto">
        Renseignez les informations de votre entreprise ci-dessous. Aucune carte bancaire requise, vous pouvez annuler à tout moment.
    </p>

    {{-- Formulaire --}}
    <form action="{{ route('iarecep.store') }}" method="POST"
        class="hero-in-4 mt-10 text-left rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl p-6 sm:p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-white/60 mb-2">Nom de l'entreprise</label>
            <input type="text" name="company_name" required placeholder="Ex : Cabinet Dupont"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-white/60 mb-2">Votre nom</label>
                <input type="text" name="full_name" required placeholder="Jean Dupont"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
            </div>
            <div>
                <label class="block text-sm text-white/60 mb-2">Email professionnel</label>
                <input type="email" name="email" required placeholder="jean@entreprise.com"
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50 transition">
            </div>
        </div>

        <div>
            <label class="block text-sm text-white/60 mb-2">Secteur d'activité</label>
            <select name="sector"
                class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-sm text-white focus:outline-none focus:border-sky-400/50 transition">
                <option class="bg-[#0a0a0c]">Cabinet médical / dentaire</option>
                <option class="bg-[#0a0a0c]">Salon de beauté / coiffure</option>
                <option class="bg-[#0a0a0c]">Immobilier</option>
                <option class="bg-[#0a0a0c]">Restauration</option>
                <option class="bg-[#0a0a0c]">Autre</option>
            </select>
        </div>

        <button type="submit"
            class="btn-shine w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                px-6 py-3.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(56,189,248,0.35)]
                hover:shadow-[0_0_30px_rgba(56,189,248,0.55)] transition-shadow">
            Créer mon réceptionniste IA
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
        </button>

        <p class="text-xs text-white/30 text-center flex items-center justify-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Vos données sont chiffrées et hébergées en Europe.
        </p>
    </form>

    <div class="hero-in-5 mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm text-white/50">
        @foreach (['Sans engagement', 'Installation rapide', 'Aucune carte bancaire'] as $item)
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $item }}
            </span>
        @endforeach
    </div>
</section>
@endsection