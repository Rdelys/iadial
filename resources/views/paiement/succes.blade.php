@extends('layouts.app')

@section('title', "Paiement en cours de confirmation - IADial")

@section('content')
<section class="max-w-lg mx-auto px-4 sm:px-6 pt-24 pb-24 text-center">
    <div class="reveal w-16 h-16 mx-auto rounded-full bg-emerald-400/15 flex items-center justify-center text-emerald-300 mb-6">
        <svg class="w-8 h-8 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
    </div>
    <h1 class="reveal font-display text-2xl sm:text-3xl font-bold">Paiement reçu, confirmation en cours</h1>
    <p class="reveal mt-3 text-white/50 text-sm sm:text-base">
        Nous finalisons l'activation de votre compte @if($payment) ({{ $payment->plan_label }}) @endif.
        Cette page se met à jour automatiquement, merci de patienter quelques secondes.
    </p>

    <div id="statut-message" class="reveal mt-6 text-xs text-white/30"></div>

    <a href="{{ route('home') }}" class="reveal mt-8 inline-flex items-center gap-2 rounded-lg border border-white/15 px-6 py-3 text-sm font-medium text-white/80 hover:bg-white/5">
        Retour à l'accueil
    </a>
</section>

@if ($payment)
<script>
    (function () {
        const reference = @json($payment->reference);
        const statutUrl = @json(route('paiement.statut', $payment->reference));
        const succesUrl = window.location.href;
        const messageEl = document.getElementById('statut-message');
        let tentative = 0;

        const poll = setInterval(async () => {
            tentative++;
            try {
                const res = await fetch(statutUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();

                if (data.ready) {
                    clearInterval(poll);
                    messageEl.textContent = 'Compte activé, redirection...';
                    window.location.href = succesUrl;
                } else if (data.status === 'failed') {
                    clearInterval(poll);
                    window.location.href = "{{ route('paiement.echec') }}?reference=" + encodeURIComponent(reference);
                } else {
                    messageEl.textContent = 'Vérification du paiement... (' + tentative + ')';
                }
            } catch (e) {
                messageEl.textContent = 'Nouvelle tentative de vérification...';
            }

            if (tentative >= 30) {
                clearInterval(poll);
                messageEl.textContent = "La confirmation prend plus de temps que prévu. Actualisez la page dans un instant.";
            }
        }, 3000);
    })();
</script>
@endif
@endsection