@extends('layouts.app')

@section('title', "IADial - Votre réceptionniste IA, toujours à l'écoute")

@section('content')

{{-- ================= HERO ================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 sm:pt-20 pb-16 sm:pb-24 grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">

    {{-- Colonne texte --}}
    <div class="text-center lg:text-left">
        <span class="hero-in-1 inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-6 sm:mb-8">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-400"></span>
            </span>
            Disponible 24h/24 et 7j/7
        </span>

        <h1 class="hero-in-2 font-display text-3xl sm:text-4xl md:text-5xl xl:text-6xl font-bold tracking-tight leading-[1.15] sm:leading-[1.1]">
            Le réceptionniste IA
            <span class="block mt-1 sm:mt-2 bg-gradient-to-r from-sky-400 via-blue-400 to-indigo-400 bg-clip-text text-transparent text-gradient-animated">
                de votre entreprise,
            </span>
            <span class="block mt-1 sm:mt-2">disponible à tout moment</span>
        </h1>

        <p class="hero-in-3 mt-5 sm:mt-6 text-white/50 text-base sm:text-lg leading-relaxed max-w-lg mx-auto lg:mx-0">
            Accueillez, informez et prenez rendez-vous automatiquement par chat ou par téléphone grâce à l'intelligence artificielle.
        </p>

        <div class="hero-in-4 mt-7 sm:mt-8 flex flex-col sm:flex-row justify-center lg:justify-start gap-3 sm:gap-4">
            <a href="#mode-test" class="btn-shine inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                px-6 py-3.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(56,189,248,0.35)]
                hover:shadow-[0_0_30px_rgba(56,189,248,0.55)] transition-shadow">
                Essayer en mode test
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
            </a>

            <a href="#demo-video" class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/15
                px-6 py-3.5 text-sm font-semibold text-white/90 hover:bg-white/5 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                Voir la démo vidéo
            </a>
        </div>

        <ul class="hero-in-5 mt-7 sm:mt-8 flex flex-wrap justify-center lg:justify-start gap-x-6 gap-y-2 text-sm text-white/50">
            @foreach (['Sans engagement', 'Installation rapide', 'Aucune carte bancaire'] as $item)
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Colonne visuelle --}}
    <div class="relative flex flex-col items-center">

        <div class="relative flex justify-center items-center py-4 sm:py-10 w-full">
            <div class="absolute w-56 h-56 sm:w-72 sm:h-72 rounded-full bg-sky-400/20 blur-3xl"></div>

            {{-- Cartes flottantes : visibles seulement à partir de lg --}}
            <div class="card-hover hidden lg:block absolute -top-2 left-2 bg-white/5 border border-white/10 backdrop-blur-xl rounded-xl p-3.5 w-56 shadow-xl" style="animation: float 5s ease-in-out infinite;">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-sky-400/15 flex items-center justify-center text-sky-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.06 0-2.077-.163-3.02-.465L3 21l1.535-3.905C3.56 15.897 3 14.482 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-xs text-white/70 leading-snug">Parle avec vos clients par téléphone ou par chat</p>
                </div>
            </div>

            <div class="card-hover hidden lg:block absolute top-8 right-0 bg-white/5 border border-white/10 backdrop-blur-xl rounded-xl p-3.5 w-52 shadow-xl" style="animation: float 6s ease-in-out infinite 0.5s;">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-indigo-400/15 flex items-center justify-center text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-xs text-white/70 leading-snug">Prend des rendez-vous automatiquement</p>
                </div>
            </div>

            {{-- Robot central (SVG) --}}
            <div class="relative w-52 h-52 sm:w-64 sm:h-64 md:w-80 md:h-80">
                <svg viewBox="0 0 300 300" class="w-full h-full drop-shadow-[0_0_40px_rgba(56,189,248,0.25)]">
                    <defs>
                        <linearGradient id="botBody" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#1e293b"/>
                            <stop offset="100%" stop-color="#0f172a"/>
                        </linearGradient>
                    </defs>
                    <circle cx="150" cy="150" r="130" fill="url(#botBody)" opacity="0.5"/>
                    <rect x="90" y="70" width="120" height="100" rx="28" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                    <rect x="106" y="98" width="88" height="44" rx="18" fill="#020617"/>
                    <circle cx="132" cy="120" r="7" fill="#38bdf8"/>
                    <circle cx="168" cy="120" r="7" fill="#38bdf8"/>
                    <line x1="150" y1="70" x2="150" y2="48" stroke="#38bdf8" stroke-width="3"/>
                    <circle cx="150" cy="42" r="7" fill="#38bdf8"/>
                    <path d="M84 110 a66 66 0 0 1 132 0" fill="none" stroke="#38bdf8" stroke-width="4"/>
                    <rect x="76" y="104" width="16" height="28" rx="6" fill="#38bdf8"/>
                    <rect x="208" y="104" width="16" height="28" rx="6" fill="#38bdf8"/>
                    <rect x="105" y="178" width="90" height="70" rx="20" fill="#0f172a" stroke="#334155" stroke-width="2"/>
                    <path d="M195 200 q35 -10 40 -45" stroke="#0f172a" stroke-width="16" stroke-linecap="round" fill="none"/>
                    <path d="M195 200 q35 -10 40 -45" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" fill="none"/>
                    <circle cx="236" cy="155" r="12" fill="#0f172a" stroke="#38bdf8" stroke-width="2"/>
                    <path d="M105 200 q-35 5 -38 35" stroke="#0f172a" stroke-width="16" stroke-linecap="round" fill="none"/>
                </svg>
            </div>

            <div class="card-hover hidden lg:block absolute bottom-6 left-0 bg-white/5 border border-white/10 backdrop-blur-xl rounded-xl p-3.5 w-52 shadow-xl" style="animation: float 5.5s ease-in-out infinite 0.3s;">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-amber-400/15 flex items-center justify-center text-amber-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xs text-white/70 leading-snug">Répond aux questions fréquentes 24h/24</p>
                </div>
            </div>

            <div class="card-hover hidden lg:block absolute -bottom-4 right-0 bg-white/5 border border-white/10 backdrop-blur-xl rounded-xl p-3.5 w-56 shadow-xl" style="animation: float 6.5s ease-in-out infinite 0.7s;">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 shrink-0 rounded-lg bg-fuchsia-400/15 flex items-center justify-center text-fuchsia-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.13a4 4 0 10-4-4 4 4 0 004 4z"/></svg>
                    </div>
                    <p class="text-xs text-white/70 leading-snug">Transfère vers un humain si nécessaire</p>
                </div>
            </div>
        </div>

        {{-- Sur mobile/tablette : mêmes infos, en grille statique sous le robot --}}
        <div class="lg:hidden reveal-group grid grid-cols-2 gap-3 w-full mt-4">
            @foreach ([
                ['color' => 'sky', 'text' => 'Parle par tél. ou par chat', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.06 0-2.077-.163-3.02-.465L3 21l1.535-3.905C3.56 15.897 3 14.482 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ['color' => 'indigo', 'text' => 'Prend rendez-vous', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['color' => 'amber', 'text' => 'Répond 24h/24', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['color' => 'fuchsia', 'text' => 'Transfère vers un humain', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.13a4 4 0 10-4-4 4 4 0 004 4z'],
            ] as $card)
                <div class="reveal bg-white/5 border border-white/10 rounded-xl p-3 flex items-center gap-2.5">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-{{ $card['color'] }}-400/15 flex items-center justify-center text-{{ $card['color'] }}-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card['icon'] }}"/></svg>
                    </div>
                    <p class="text-xs text-white/70 leading-snug">{{ $card['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= BANDEAU FONCTIONNALITÉS ================= --}}
<section id="fonctionnalites" class="max-w-7xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
    <div class="reveal-group rounded-2xl border border-white/10 bg-white/[0.03] backdrop-blur-xl grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y divide-white/10 lg:divide-y-0 lg:divide-x">
        @foreach ([
            ['color' => 'sky', 'title' => 'Mode texte & vocal', 'desc' => 'Discutez par chat sur votre site ou par téléphone avec notre IA.', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8-1.06 0-2.077-.163-3.02-.465L3 21l1.535-3.905C3.56 15.897 3 14.482 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['color' => 'emerald', 'title' => 'Prise de rendez-vous', 'desc' => 'Réservation automatique dans votre calendrier en temps réel.', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['color' => 'indigo', 'title' => 'Transfert humain', 'desc' => 'Transfert en un clic vers un membre de votre équipe.', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-3.13a4 4 0 10-4-4 4 4 0 004 4z'],
            ['color' => 'amber', 'title' => 'Suivi & statistiques', 'desc' => 'Tableau de bord complet pour analyser les conversations.', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14'],
        ] as $f)
            <div class="reveal card-hover p-5 sm:p-6 flex flex-col gap-3">
                <div class="w-10 h-10 rounded-lg bg-{{ $f['color'] }}-400/15 flex items-center justify-center text-{{ $f['color'] }}-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $f['icon'] }}"/></svg>
                </div>
                <h3 class="font-display font-semibold">{{ $f['title'] }}</h3>
                <p class="text-sm text-white/50 leading-relaxed">{{ $f['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= MODE TEST (fichier séparé) ================= --}}
@include('partials.mode-test')

{{-- ================= POURQUOI NOUS CHOISIR ================= --}}
<section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24 text-center">
    <h2 class="reveal font-display text-xl sm:text-2xl md:text-3xl font-semibold">Pourquoi choisir notre Réceptionniste IA ?</h2>

    <div class="reveal-group mt-10 sm:mt-12 grid grid-cols-2 lg:grid-cols-5 gap-6 sm:gap-8">
        @foreach ([
            ['color' => 'sky', 'title' => '24h/24 et 7j/7', 'desc' => 'Vos clients sont toujours accueillis', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['color' => 'amber', 'title' => 'Réponses instantanées', 'desc' => 'Moins d\'attente, plus de satisfaction', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['color' => 'emerald', 'title' => 'Zéro double réservation', 'desc' => 'Calendrier intelligent et sécurisé', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['color' => 'indigo', 'title' => 'Productivité boostée', 'desc' => 'Moins de tâches répétitives, plus d\'efficacité', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['color' => 'fuchsia', 'title' => 'Données sécurisées', 'desc' => 'Conforme RGPD et hébergé en Europe', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ] as $reason)
            <div class="reveal flex flex-col items-center gap-3 {{ $loop->last ? 'col-span-2 lg:col-span-1' : '' }}">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-{{ $reason['color'] }}-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $reason['icon'] }}"/></svg>
                </div>
                <h3 class="font-medium text-sm">{{ $reason['title'] }}</h3>
                <p class="text-xs text-white/40 leading-relaxed">{{ $reason['desc'] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= CTA FINAL ================= --}}
<section id="essai" class="reveal max-w-6xl mx-auto px-4 sm:px-6 pb-20 sm:pb-28">
    <div class="rounded-2xl sm:rounded-3xl border border-white/10 bg-gradient-to-b from-white/[0.05] to-transparent p-6 sm:p-10 md:p-14 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="text-center md:text-left">
            <h2 class="font-display text-xl sm:text-2xl md:text-3xl font-semibold">Prêt à automatiser votre accueil dès maintenant ?</h2>
            <p class="mt-2 text-white/50 text-sm sm:text-base">Testez gratuitement notre réceptionniste IA sans engagement.</p>
            <a href="{{ route('iarecep') }}" target="_blank" rel="noopener"
               class="btn-shine mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                px-6 py-3.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(56,189,248,0.35)]
                hover:shadow-[0_0_30px_rgba(56,189,248,0.55)] transition-shadow">
                Essayer en mode test
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
            </a>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <div class="w-11 h-11 rounded-full bg-sky-400/15 flex items-center justify-center text-sky-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div class="text-sm">
                <p class="font-medium">Aucune carte bancaire requise</p>
                <p class="text-white/40 text-xs">Testez librement toutes les fonctionnalités.</p>
            </div>
        </div>
    </div>
</section>

@endsection