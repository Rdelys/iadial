<?php
// app/Http/Controllers/AdminDashboardController.php

namespace App\Http\Controllers;

use App\Models\IarecepAppointment;
use App\Models\IarecepTest;
use App\Models\IarecepVisit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // --- Visites ---
        $totalVisits = IarecepVisit::count();
        $visitsToday = IarecepVisit::whereDate('created_at', today())->count();
        $visitsLast30Days = IarecepVisit::where('created_at', '>=', now()->subDays(30))->count();

        // --- Essais (tests) ---
        $totalTests = IarecepTest::count();
        $testsToday = IarecepTest::whereDate('created_at', today())->count();
        $testsByMode = IarecepTest::select('mode', DB::raw('count(*) as total'))
            ->groupBy('mode')
            ->pluck('total', 'mode');

        $testsTexte = $testsByMode->get('text', 0);
        $testsVocal = $testsByMode->get('vocal', 0);

        // --- Rendez-vous réels reçus (widget Léa / vocal landing, PAS l'essai gratuit) ---
        $realAppointments = IarecepAppointment::where('status', 'confirmed_vapi');
        $totalRealAppointments = (clone $realAppointments)->count();
        $realAppointmentsThisMonth = (clone $realAppointments)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $recentRealAppointments = (clone $realAppointments)
            ->latest()
            ->take(8)
            ->get(['full_name', 'phone', 'email', 'date', 'time', 'notes', 'created_at']);

        // --- Rendez-vous pris pendant les essais gratuits (indicatif, pas "réels") ---
        $trialAppointments = IarecepAppointment::where('status', 'confirmed')
            ->whereIn('source', ['vapi_essai', null])
            ->count();

        // --- Taux de conversion visite -> essai ---
        $conversionRate = $totalVisits > 0
            ? round(($totalTests / $totalVisits) * 100, 1)
            : 0;

        // --- Graphique visites (14 derniers jours) ---
        $visitsChart = $this->dailySeries(IarecepVisit::query(), 14);

        // --- Graphique essais (14 derniers jours) ---
        $testsChart = $this->dailySeries(IarecepTest::query(), 14);

        // --- Derniers essais ---
        $recentTests = IarecepTest::latest()
            ->take(8)
            ->get(['company_name', 'full_name', 'email', 'mode', 'status', 'created_at']);

        return view('admin.dashboard', compact(
            'totalVisits', 'visitsToday', 'visitsLast30Days',
            'totalTests', 'testsToday', 'testsTexte', 'testsVocal',
            'totalRealAppointments', 'realAppointmentsThisMonth', 'recentRealAppointments',
            'trialAppointments', 'conversionRate',
            'visitsChart', 'testsChart', 'recentTests'
        ));
    }

    /**
     * Construit une série [date => count] pour les $days derniers jours,
     * en comblant les jours sans données avec 0.
     */
    private function dailySeries($query, int $days): array
    {
        $raw = $query
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as d, count(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $series[$date] = (int) ($raw[$date] ?? 0);
        }

        return $series;
    }

    public function appointments()
{
    $appointments = IarecepAppointment::where('status', 'confirmed_vapi')
        ->latest()
        ->paginate(15);

    return view('admin.appointments', compact('appointments'));
}

public function tests()
{
    $tests = IarecepTest::latest()->paginate(15);

    return view('admin.tests', compact('tests'));
}
}