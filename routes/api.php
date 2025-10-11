<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SrsController;

Route::middleware('auth:sanctum')->group(function () {
    // Pull due cards (and auto-seed “new” cards if needed)
    Route::get('/srs/due', [SrsController::class, 'due']);

    // Submit a review result (Again/Hard/Good/Easy)
    Route::post('/srs/review', [SrsController::class, 'review']);
});