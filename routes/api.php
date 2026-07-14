<?php

use App\Http\Controllers\VapiController;
use Illuminate\Support\Facades\Route;

Route::post('/vapi/webhook', [VapiController::class, 'webhook']);