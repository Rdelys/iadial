@extends('layouts.admin')
@section('title', 'Clients')

@section('content')
<div class="flex items-center justify-between flex-wrap gap-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client…"
               class="bg-slate-900 border border-slate-800 rounded-lg px-4 py-2 text-sm w-64 focus:outline-none focus:border-indigo-500">
        <button class="bg-slate-800 hover:bg-slate-700 text-sm rounded-lg px-4 py-2">Filtrer</button>
    </form>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-slate-500 text-left border-b border-slate-800">
                <th class="px-6 py-3">Entreprise</th>
                <th class="px-6 py-3">Contact</th>
                <th class="px-6 py-3">Offre</th>
                <th class="px-6 py-3">Statut</th>
                <th class="px-6 py-3">Assistant Vapi</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr class="border-b border-slate-800/50">
                    <td class="px-6 py-3 font-medium">{{ $client->company_name ?? '—' }}</td>
                    <td class="px-6 py-3 text-slate-400">
                        {{ $client->name }}<br>
                        <span class="text-xs">{{ $client->email }}</span>
                    </td>
                    <td class="px-6 py-3 text-slate-400">{{ $client->plan_label ?? '—' }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $client->isSubscribed() ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                            {{ $client->isSubscribed() ? 'Actif' : 'En attente' }}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        @if($client->hasVapiAssistant())
                            <span class="px-2 py-0.5 rounded-full text-xs bg-sky-500/10 text-sky-400">Configuré</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-700/30 text-slate-400">Non configuré</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right">
                        <a href="{{ route('admin.clients.edit', $client) }}" class="text-indigo-400 hover:text-indigo-300 text-xs">Gérer →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">Aucun client pour le moment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>{{ $clients->links() }}</div>
@endsection