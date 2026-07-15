@extends('layouts.admin')

@section('title', 'Rendez-vous')

@section('content')
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 overflow-x-auto">
        @if($appointments->isEmpty())
            <p class="text-slate-500 text-sm">Aucun rendez-vous pour le moment.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-slate-500 text-left border-b border-slate-800">
                        <th class="pb-3">Client</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3">Heure</th>
                        <th class="pb-3">Contact</th>
                        <th class="pb-3">Motif</th>
                        <th class="pb-3">Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $a)
                        <tr class="border-b border-slate-800/50">
                            <td class="py-3">{{ $a->full_name }}</td>
                            <td class="py-3 text-slate-400">{{ $a->date->format('d/m/Y') }}</td>
                            <td class="py-3 text-slate-400">{{ substr($a->time, 0, 5) }}</td>
                            <td class="py-3 text-slate-400">{{ $a->phone ?: $a->email ?: '—' }}</td>
                            <td class="py-3 text-slate-400">{{ $a->notes ?: '—' }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-500/10 text-indigo-300">
                                    {{ $a->source ?? 'vapi' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-6">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>
@endsection