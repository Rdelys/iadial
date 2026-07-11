<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\IarecepController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/essai-gratuit', [IarecepController::class, 'index'])->name('iarecep');
Route::post('/essai-gratuit', [IarecepController::class, 'store'])->name('iarecep.store');