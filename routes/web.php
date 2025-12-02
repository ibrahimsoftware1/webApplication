<?php

use App\Http\Controllers\Api\AuthController;
use App\Models\chatting\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Route;

// Email verification route (web route for browser redirects)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmailWeb'])
    ->name('verification.verify.web');


