@extends('layouts.app')

@section('title', "Paiement - {$plan['label']} - IADial")

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 pt-16 pb-24">
    <div class="reveal text-center mb-10">
        <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-xs text-sky-300 mb-5">
            Étape 1/2 &middot; Vos informations
        </span>
        <h1 class="font-display text-2xl sm:text-3xl font-bold">Souscrire à {{ $plan['label'] }}</h1>
        <p class="mt-3 text-white/50 text-sm sm:text-base">
            Renseignez vos informations d'entreprise. Vous serez ensuite redirigé vers la page de paiement sécurisée Papi (Mvola, Orange Money, Airtel Money, carte bancaire).
        </p>
    </div>

    @if ($errors->any())
        <div class="reveal mb-6 rounded-xl border border-red-400/30 bg-red-400/10 px-4 py-3 text-sm text-red-300">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('paiement.store', $planKey) }}" method="POST" class="reveal space-y-5">
        @csrf

        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 sm:p-8 space-y-5">
            <div>
                <label class="block text-sm text-white/60 mb-1.5">Nom du contact *</label>
                <input type="text" name="client_name" value="{{ old('client_name') }}" required
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
            </div>

            <div>
                <label class="block text-sm text-white/60 mb-1.5">Raison sociale / nom de l'entreprise *</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required
                       class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
            </div>

            <div>
                <label class="block text-sm text-white/60 mb-1.5">Secteur d'activité *</label>
                <select name="sector" required
                        class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-sky-400/50">
                    <option value="" disabled {{ old('sector') ? '' : 'selected' }}>Choisissez un secteur</option>
                    @foreach (['Santé & bien-être', 'Restauration', 'Immobilier', 'Beauté & esthétique', 'Artisanat & BTP', 'Commerce / vente', 'Services professionnels', 'Autre'] as $sector)
                        <option value="{{ $sector }}" {{ old('sector') === $sector ? 'selected' : '' }}>{{ $sector }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm text-white/60 mb-1.5">Adresse *</label>
                    <input type="text" name="address" value="{{ old('address') }}" required placeholder="Ex. Lot II M 12, Ankorondrano"
                           class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1.5">Ville *</label>
                    <input type="text" name="city" value="{{ old('city', 'Antananarivo') }}" required
                           class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm text-white/60 mb-1.5">E-mail professionnel *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
                </div>
                <div>
                    <label class="block text-sm text-white/60 mb-1.5">Téléphone *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+261 34 00 000 00"
                           class="w-full rounded-lg border border-white/10 bg-black/40 px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-sky-400/50">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-sky-400/25 bg-sky-400/[0.05] p-6 flex items-center justify-between">
            <div>
                <p class="text-sm text-white/60">Total à payer</p>
                <p class="font-display text-2xl font-bold">{{ number_format($plan['amount_eur'], 0, ',', ' ') }} €<span class="text-white/40 text-sm font-normal">/mois</span></p>
                <p class="text-xs text-white/30 mt-1">Le règlement s'effectue via Mvola, Orange Money, Airtel Money ou carte bancaire (contre-valeur en Ariary calculée automatiquement).</p>
            </div>
            <svg class="w-10 h-10 text-sky-300/60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>

        <label class="reveal flex items-start gap-3 text-sm text-white/50">
            <input type="checkbox" name="accept_cgu" value="1" {{ old('accept_cgu') ? 'checked' : '' }}
                   class="mt-1 rounded border-white/20 bg-black/40 text-sky-400 focus:ring-sky-400">
            <span>J'ai lu et j'accepte les <a href="{{ route('cgu') }}" target="_blank" class="text-sky-300 hover:underline">Conditions Générales d'Utilisation et de Vente</a>.</span>
        </label>

        <button type="submit"
                class="btn-shine w-full inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-sky-400 to-indigo-500
                    px-6 py-3.5 text-sm font-semibold text-black shadow-[0_0_20px_rgba(52,226,192,0.35)]
                    hover:shadow-[0_0_30px_rgba(52,226,192,0.55)] transition-shadow">
            Procéder au paiement sécurisé
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7M21 12H3"/></svg>
        </button>
        <p class="text-center text-xs text-white/30">Vous allez être redirigé vers la page de paiement sécurisée Papi.</p>
    </form>
</section>
@endsection