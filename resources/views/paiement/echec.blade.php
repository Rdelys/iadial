@extends('layouts.app')

@section('title', "Paiement échoué - IADial")

@section('content')
<section class="max-w-lg mx-auto px-4 sm:px-6 pt-24 pb-24 text-center">
    <div class="reveal w-16 h-16 mx-auto rounded-full bg-red-400/15 flex items-center justify-center text-red-300 mb-6">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    <h1 class="reveal font-display text-2xl sm:text-3xl font-bold">Le paiement a échoué</h1>
    <p class="reveal mt-3 text-white/50 text-sm sm:text-base">
        Votre paiement n'a pas pu être finalisé. Aucun montant n'a été débité (ou sera remboursé automatiquement selon votre opérateur). Vous pouvez réessayer.
    </p>
    @if ($payment)
        <a href="{{ route('paiement.checkout', $payment->plan) }}" class="reveal mt-8 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-6 py-3 text-sm font-semibold text-black">
            Réessayer le paiement
        </a>
    @else
        <a href="{{ route('tarifs') }}" class="reveal mt-8 inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500 px-6 py-3 text-sm font-semibold text-black">
            Voir les tarifs
        </a>
    @endif
</section>
@endsection