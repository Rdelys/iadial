@extends('layouts.admin')

@section('title', 'Dashboard')

@push('head')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
@endpush

@section('content')

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-slate-500 text-sm">Visites totales</p>
                <i class="fa-solid fa-eye text-slate-600"></i>
            </div>
            <p class="text-3xl font-bold mt-2">{{ number_format($totalVisits, 0, ',', ' ') }}</p>
            <p class="text-emerald-400 text-xs mt-1">{{ $visitsToday }} aujourd'hui · {{ $visitsLast30Days }} sur 30j</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-slate-500 text-sm">Essais lancés</p>
                <i class="fa-solid fa-flask text-slate-600"></i>
            </div>
            <p class="text-3xl font-bold mt-2">{{ number_format($totalTests, 0, ',', ' ') }}</p>
            <p class="text-indigo-400 text-xs mt-1">{{ $testsToday }} aujourd'hui · {{ $conversionRate }}% des visites</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-slate-500 text-sm">Rendez-vous réels reçus</p>
                <i class="fa-solid fa-calendar-check text-slate-600"></i>
            </div>
            <p class="text-3xl font-bold mt-2">{{ number_format($totalRealAppointments, 0, ',', ' ') }}</p>
            <p class="text-amber-400 text-xs mt-1">{{ $realAppointmentsThisMonth }} ce mois-ci</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <p class="text-slate-500 text-sm">Essai — texte / vocal</p>
                <i class="fa-solid fa-microphone text-slate-600"></i>
            </div>
            <p class="text-3xl font-bold mt-2">{{ $testsTexte }} / {{ $testsVocal }}</p>
            <p class="text-slate-500 text-xs mt-1">{{ $trialAppointments }} RDV pris en essai (démo)</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4">Visites — 14 derniers jours</h2>
            <canvas id="visitsChart" height="140"></canvas>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-slate-300 mb-4">Essais lancés — 14 derniers jours</h2>
            <canvas id="testsChart" height="140"></canvas>
        </div>
    </div>

    {{-- Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 overflow-x-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-300">Derniers rendez-vous réels</h2>
                <a href="{{ route('admin.appointments') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Voir tout →</a>
            </div>
            @if($recentRealAppointments->isEmpty())
                <p class="text-slate-500 text-sm">Aucun rendez-vous pour le moment.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-500 text-left border-b border-slate-800">
                            <th class="pb-2">Client</th>
                            <th class="pb-2">Date</th>
                            <th class="pb-2">Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRealAppointments as $a)
                            <tr class="border-b border-slate-800/50">
                                <td class="py-2">{{ $a->full_name }}</td>
                                <td class="py-2 text-slate-400">{{ $a->date->format('d/m/Y') }} · {{ substr($a->time,0,5) }}</td>
                                <td class="py-2 text-slate-400">{{ $a->phone ?: $a->email ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 overflow-x-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-300">Derniers essais</h2>
                <a href="{{ route('admin.tests') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Voir tout →</a>
            </div>
            @if($recentTests->isEmpty())
                <p class="text-slate-500 text-sm">Aucun essai pour le moment.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-500 text-left border-b border-slate-800">
                            <th class="pb-2">Entreprise</th>
                            <th class="pb-2">Mode</th>
                            <th class="pb-2">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTests as $t)
                            <tr class="border-b border-slate-800/50">
                                <td class="py-2">{{ $t->company_name }}</td>
                                <td class="py-2 text-slate-400 capitalize">{{ $t->mode ?? 'text' }}</td>
                                <td class="py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs {{ $t->status === 'in_progress' ? 'bg-amber-500/10 text-amber-400' : 'bg-slate-700/30 text-slate-400' }}">
                                        {{ $t->status === 'in_progress' ? 'En cours' : 'Terminé' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
<script>
    const chartOptions = {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#64748b' } },
            y: { grid: { color: '#1e293b' }, ticks: { color: '#64748b', precision: 0 } },
        },
    };

    new Chart(document.getElementById('visitsChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($visitsChart)) !!},
            datasets: [{
                data: {!! json_encode(array_values($visitsChart)) !!},
                borderColor: '#818cf8',
                backgroundColor: 'rgba(129,140,248,0.15)',
                tension: 0.35,
                fill: true,
            }],
        },
        options: chartOptions,
    });

    new Chart(document.getElementById('testsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($testsChart)) !!},
            datasets: [{
                data: {!! json_encode(array_values($testsChart)) !!},
                backgroundColor: '#34d399',
                borderRadius: 4,
            }],
        },
        options: chartOptions,
    });
</script>
@endpush