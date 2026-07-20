<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminClientController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientReceptionController;
use App\Http\Controllers\DevisController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IarecepController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/essai-gratuit', [IarecepController::class, 'index'])->name('iarecep.index');
Route::post('/essai-gratuit', [IarecepController::class, 'store'])->name('iarecep.store');
Route::post('/essai-gratuit/chat', [IarecepController::class, 'chat'])->name('iarecep.chat');
Route::post('/essai-gratuit/close', [IarecepController::class, 'close'])->name('iarecep.close');
Route::post('/essai-gratuit/demande', [IarecepController::class, 'requestDemo'])->name('iarecep.demande');
Route::get('/essai-gratuit/appointments', [IarecepController::class, 'appointmentsIndex'])->name('iarecep.appointments.index');
Route::post('/essai-gratuit/appointments', [IarecepController::class, 'appointmentsStore'])->name('iarecep.appointments.store');
Route::post('/iarecep/demo/chat', [IarecepController::class, 'demoChat'])->name('iarecep.demo.chat');
Route::post('/iarecep/demo/reset', [IarecepController::class, 'demoReset'])->name('iarecep.demo.reset');

Route::get('/calendrier', [IarecepController::class, 'calendrier'])->name('iarecep.calendrier');
Route::get('/tarifs', [HomeController::class, 'tarifs'])->name('tarifs');
Route::post('/essai-gratuit/vapi-config', [IarecepController::class, 'vapiConfig'])->name('iarecep.vapi.config');

// Page publique de réservation en lecture seule (partagée par un client à ses propres clients)
Route::get('/r/{slug}', [PublicBookingController::class, 'show'])->name('public.booking');

// Authentification client
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ClientAuthController::class, 'showLogin'])->name('connexion');
    Route::post('/connexion', [ClientAuthController::class, 'login'])->name('connexion.submit');
});
Route::post('/deconnexion', [ClientAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('deconnexion');

// Espace client connecté
Route::middleware('auth')->group(function () {
    Route::get('/mon-recep-ia', [ClientReceptionController::class, 'show'])->name('client.recep-ia');
});

// Paiement Papi
Route::get('/paiement/{plan}', [PaymentController::class, 'checkout'])
    ->whereIn('plan', ['starter', 'pro'])
    ->name('paiement.checkout');
Route::post('/paiement/{plan}', [PaymentController::class, 'store'])
    ->whereIn('plan', ['starter', 'pro'])
    ->name('paiement.store');
Route::get('/paiement/succes', [PaymentController::class, 'succes'])->name('paiement.succes');
Route::get('/paiement/echec', [PaymentController::class, 'echec'])->name('paiement.echec');
Route::post('/paiement/notification', [PaymentController::class, 'notification'])->name('paiement.notification');
Route::get('/paiement/statut/{reference}', [PaymentController::class, 'statut'])->name('paiement.statut');

// Devis Business
Route::get('/devis', [DevisController::class, 'create'])->name('devis.create');
Route::post('/devis', [DevisController::class, 'store'])->name('devis.store');

// Pages statiques
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/mentions-legales', [PageController::class, 'mentionsLegales'])->name('mentions-legales');
Route::get('/cgu-cgv', [PageController::class, 'cgu'])->name('cgu');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/rendez-vous', [AdminDashboardController::class, 'appointments'])->name('appointments');
        Route::get('/essais', [AdminDashboardController::class, 'tests'])->name('tests');

        Route::get('/clients', [AdminClientController::class, 'index'])->name('clients');
        Route::get('/clients/{user}', [AdminClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{user}', [AdminClientController::class, 'update'])->name('clients.update');
    });
});