@extends('layouts.admin')

@section('title', 'Essais')

@section('content')
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 overflow-x-auto">
        @if($tests->isEmpty())
            <p class="text-slate-500 text-sm">Aucun essai pour le moment.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-500 text-left border-b border-slate-800">
                        <th class="pb-3">Entreprise</th>
                        <th class="pb-3">Contact</th>
                        <th class="pb-3">Email</th>
                        <th class="pb-3">Mode</th>
                        <th class="pb-3">Statut</th>
                        <th class="pb-3">Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tests as $t)
                        <tr class="border-b border-slate-800/50">
                            <td class="py-3">{{ $t->company_name }}</td>
                            <td class="py-3 text-slate-400">{{ $t->full_name }}</td>
                            <td class="py-3 text-slate-400">{{ $t->email }}</td>
                            <td class="py-3 text-slate-400 capitalize">{{ $t->mode ?? 'text' }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $t->status === 'in_progress' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700/30 text-slate-400' }}">
                                    {{ $t->status === 'in_progress' ? 'En cours' : 'Terminé' }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-400">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-6">
                {{ $tests->links() }}
            </div>
        @endif
    </div>
@endsection