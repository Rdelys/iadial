@extends('layouts.app')

@section('title', 'Mon Récep IA - IADial')

@section('content')
<section class="max-w-4xl mx-auto px-4 sm:px-6 pt-16 pb-24" x-data="{ copied: false }">
    <div class="reveal mb-10">
        <h1 class="font-display text-2xl sm:text-3xl font-bold flex items-center gap-3">
            Mon Récep IA
            <span class="listen-wave" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></span>
        </h1>
        <p class="text-white/50 text-sm mt-2">Votre assistant vocal et chat, configuré pour {{ $user->company_name ?? 'votre entreprise' }}.</p>
    </div>

    @if($user->hasVapiAssistant())
        <div class="reveal rounded-2xl border border-sky-400/25 bg-sky-400/[0.05] p-6 mb-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="relative flex h-2 w-2">
                    <span class="online-dot absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
                <span class="text-sm text-emerald-300 font-medium">Assistant actif</span>
            </div>
            <p class="text-white/60 text-sm">Votre réceptionniste IA est configuré et opérationnel. Il répond via le widget ci-dessous, et par téléphone si vous avez activé le renvoi d'appel vers votre numéro dédié.</p>
        </div>

        <div class="reveal rounded-2xl border border-white/10 bg-white/[0.03] p-6 mb-8">
            <h3 class="font-display font-semibold mb-4">Lien de réservation à partager</h3>
            <p class="text-white/50 text-sm mb-4">Partagez ce lien en lecture seule à vos propres clients pour qu'ils puissent prendre rendez-vous directement avec votre assistant, sans avoir besoin d'un compte.</p>
            <div class="flex flex-col sm:flex-row gap-3">
                <input readonly type="text" value="{{ $user->bookingUrl() }}"
                       class="flex-1 rounded-lg bg-black/40 border border-white/10 px-4 py-2.5 text-sm text-sky-300 font-mono"
                       onclick="this.select()">
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $user->bookingUrl() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="btn-shine inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-5 py-2.5 text-sm font-medium text-black shrink-0">
                    <span x-show="!copied">Copier le lien</span>
                    <span x-show="copied" x-cloak>Copié !</span>
                </button>
            </div>
        </div>

        <div class="reveal rounded-2xl border border-white/10 bg-white/[0.03] p-6">
            <h3 class="font-display font-semibold mb-4">Aperçu de votre assistant</h3>
            <p class="text-white/50 text-sm mb-2">Testez-le directement ici, comme le feraient vos clients.</p>
        </div>

        {{-- Widget Vapi scopé au client (shortcode ajouté par l'admin) --}}
        <script src="https://unpkg.com/@vapi-ai/client-sdk-react/dist/embed/widget.umd.js" async type="text/javascript"></script>
        <vapi-widget
            public-key="{{ $user->vapi_public_key }}"
            assistant-id="{{ $user->vapi_assistant_id }}"
            mode="hybrid"
            theme="dark"
            base-bg-color="#0f172a"
            accent-color="#34E2C0"
            cta-button-color="#34E2C0"
            cta-button-text-color="#020617"
            border-radius="large"
            size="compact"
            position="bottom-right"
            title="Mon Récep IA"
            cta-title="Essayer mon assistant"
            cta-subtitle="Aperçu en direct"
            start-button-text="Parler"
            end-button-text="Raccrocher"
        ></vapi-widget>
    @else
        <div class="reveal rounded-2xl border border-amber-400/25 bg-amber-400/[0.05] p-8 text-center">
            <i class="fa-solid fa-circle-exclamation text-amber-400 text-2xl mb-3"></i>
            <p class="font-display font-semibold text-amber-200 text-lg mb-2">Votre assistant n'est pas encore activé</p>
            <p class="text-white/50 text-sm max-w-md mx-auto">Notre équipe configure votre réceptionniste IA après validation de votre abonnement. Vous recevrez une notification dès qu'il sera prêt — généralement sous 24 à 48h.</p>
        </div>
    @endif
</section>
@endsection