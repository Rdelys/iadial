<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IarecepController;
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
