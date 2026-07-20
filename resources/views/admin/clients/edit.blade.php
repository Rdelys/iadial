@extends('layouts.admin')
@section('title', 'Client — ' . ($user->company_name ?? $user->name))

@section('content')
@if(session('success'))
    <div class="rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-300">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('admin.clients') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white">
    <i class="fa-solid fa-arrow-left"></i> Retour aux clients
</a>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Colonne info --}}
    <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-3 text-sm">
        <h3 class="font-semibold text-slate-300 mb-2">Informations</h3>
        <div class="flex justify-between"><span class="text-slate-500">Entreprise</span><span>{{ $user->company_name ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Contact</span><span>{{ $user->name }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Email</span><span>{{ $user->email }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Téléphone</span><span>{{ $user->phone ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Secteur</span><span>{{ $user->sector ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Ville</span><span>{{ $user->city ?? '—' }}</span></div>
        <div class="flex justify-between"><span class="text-slate-500">Offre</span><span>{{ $user->plan_label ?? '—' }}</span></div>
        <div class="flex justify-between">
            <span class="text-slate-500">Statut paiement</span>
            <span class="px-2 py-0.5 rounded-full text-xs {{ $user->isSubscribed() ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                {{ $user->isSubscribed() ? 'Actif' : 'En attente' }}
            </span>
        </div>
        <div class="flex justify-between"><span class="text-slate-500">Rendez-vous reçus</span><span>{{ $appointmentsCount }}</span></div>

        @if($user->hasVapiAssistant())
            <div class="pt-3 border-t border-slate-800">
                <span class="text-slate-500 block mb-1">Lien de réservation</span>
                <a href="{{ $user->bookingUrl() }}" target="_blank" class="text-sky-400 hover:underline text-xs break-all">{{ $user->bookingUrl() }}</a>
            </div>
        @endif
    </div>

    {{-- Colonne shortcode Vapi --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h3 class="font-semibold text-slate-300 mb-1">Configuration de l'assistant Vapi</h3>
            <p class="text-slate-500 text-xs mb-5">Colle ici les identifiants générés dans le tableau de bord Vapi.ai pour l'assistant dédié à ce client.</p>

            <form method="POST" action="{{ route('admin.clients.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">Clé publique Vapi (public key)</label>
                    <input type="text" name="vapi_public_key" value="{{ old('vapi_public_key', $user->vapi_public_key) }}"
                           placeholder="ex: 8b2e1c4a-..."
                           class="w-full bg-black/40 border border-slate-800 rounded-lg px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-indigo-500">
                    @error('vapi_public_key') <p class="text-coral-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1.5">ID de l'assistant (assistant ID)</label>
                    <input type="text" name="vapi_assistant_id" value="{{ old('vapi_assistant_id', $user->vapi_assistant_id) }}"
                           placeholder="ex: a1f3d9e0-..."
                           class="w-full bg-black/40 border border-slate-800 rounded-lg px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-indigo-500">
                    @error('vapi_assistant_id') <p class="text-coral-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-sm font-medium rounded-lg px-5 py-2.5 transition">
                    Enregistrer
                </button>
            </form>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h3 class="font-semibold text-slate-300 mb-4">Derniers paiements</h3>
            @if($payments->isEmpty())
                <p class="text-slate-500 text-sm">Aucun paiement enregistré.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-500 text-left border-b border-slate-800">
                            <th class="pb-2">Référence</th>
                            <th class="pb-2">Montant</th>
                            <th class="pb-2">Statut</th>
                            <th class="pb-2">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                            <tr class="border-b border-slate-800/50">
                                <td class="py-2 font-mono text-xs">{{ $p->reference }}</td>
                                <td class="py-2">{{ number_format($p->amount_eur, 2, ',', ' ') }} €</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs
                                        {{ $p->status === 'success' ? 'bg-emerald-500/10 text-emerald-400' : ($p->status === 'pending' ? 'bg-amber-500/10 text-amber-400' : 'bg-coral-500/10 text-coral-400') }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>
                                <td class="py-2 text-slate-400">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection