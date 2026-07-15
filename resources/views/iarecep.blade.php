{{-- resources/views/iarecep.blade.php --}}
@extends('layouts.app')

@section('title', "Essai gratuit - IADial")

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-20 pb-28 text-center"
    id="iarecep-app"
    data-token="{{ $token }}"
    data-existing="{{ $existingTest ? '1' : '0' }}"
    data-existing-mode="{{ $existingTest->mode ?? 'text' }}">

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
        <span class="block mt-2 bg-gradient-to-r from-sky-400 via-indigo-400 to-indigo-500 bg-clip-text text-transparent text-gradient-animated">
            en moins de 2 minutes
        </span>
    </h1>

    <p class="hero-in-3 mt-5 text-white/50 text-base sm:text-lg leading-relaxed max-w-lg mx-auto">
        Renseignez les informations de votre entreprise ci-dessous. Aucune carte bancaire requise, vous pouvez annuler à tout moment.
    </p>

    {{-- Sélecteur de mode --}}
    <div class="hero-in-4 mt-8 flex justify-center gap-3" id="iarecep-mode-switch">
        <button type="button" data-mode="text"
            class="iarecep-mode-btn is-active rounded-full px-5 py-2 text-sm font-medium border border-sky-400/40 bg-sky-400/10 text-sky-300 transition">
            💬 Chat texte
        </button>
        <button type="button" data-mode="vocal"
            class="iarecep-mode-btn rounded-full px-5 py-2 text-sm font-medium border border-white/10 bg-white/5 text-white/60 transition">
            🎙️ Chat vocal
        </button>
    </div>

    {{-- Formulaire --}}
    <div id="iarecep-form-wrapper" class="hero-in-4">
        @include('partials.iarecep-form')
    </div>

    {{-- Chat texte --}}
    <div id="iarecep-text-wrapper" class="hidden mt-10">
        @include('partials.iarecep-text')
    </div>

    {{-- Chat vocal --}}
    <div id="iarecep-vocal-wrapper" class="hidden mt-10">
        @include('partials.iarecep-vocal')
    </div>
    <div id="iarecep-calendar-wrapper" class="hidden mt-8">
        @include('partials.iarecep-calendar')
    </div>
    {{-- Formulaire de satisfaction / contact --}}
    <div id="iarecep-satisfaction-wrapper" class="hidden mt-8">
        @include('partials.iarecep-satisfaction')
    </div>
</section>

<script>
window.IARECEP = {
    token: @json($token),
    csrf: document.querySelector('meta[name="csrf-token"]')?.content,
    routes: {
        store: @json(route('iarecep.store')),
        chat: @json(route('iarecep.chat')),
        close: @json(route('iarecep.close')),
        demande: @json(route('iarecep.demande')),
        vapiConfig: @json(route('iarecep.vapi.config')),
    },
    existingMessages: @json($existingMessages->map(fn($m) => ['role' => $m->role, 'content' => $m->content])),
};

(function () {
    const switchWrap = document.getElementById('iarecep-mode-switch');
    const buttons = switchWrap.querySelectorAll('.iarecep-mode-btn');
    let currentMode = 'text';

    function setMode(mode) {
        currentMode = mode;
        buttons.forEach(b => {
            const active = b.dataset.mode === mode;
            b.classList.toggle('is-active', active);
            b.classList.toggle('border-sky-400/40', active);
            b.classList.toggle('bg-sky-400/10', active);
            b.classList.toggle('text-sky-300', active);
            b.classList.toggle('border-white/10', !active);
            b.classList.toggle('bg-white/5', !active);
            b.classList.toggle('text-white/60', !active);
        });
        document.getElementById('iarecep-hidden-mode').value = mode;
    }

    buttons.forEach(b => b.addEventListener('click', () => setMode(b.dataset.mode)));
    window.IARECEP.getMode = () => currentMode;

    // Fermeture propre de la session au départ du visiteur.
    function closeSession() {
        const token = window.IARECEP.token;
        if (!token) return;
        const fd = new FormData();
        fd.append('token', token);
        fd.append('_token', window.IARECEP.csrf);
        navigator.sendBeacon(window.IARECEP.routes.close, fd);
    }
    window.addEventListener('pagehide', closeSession);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') closeSession();
    });

    // Reprise automatique si un test est déjà en cours pour ce token.
    const app = document.getElementById('iarecep-app');
    if (app.dataset.existing === '1') {
        setMode(app.dataset.existingMode);
        window.IARECEP.resumeExisting?.();
    }
})();
</script>
@endsection