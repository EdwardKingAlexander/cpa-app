<?php

// Verify routes: php artisan route:list --path=api -v

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SrsController;
use Illuminate\Support\Facades\Route;

// ============================================================================
// API v1 Routes (versioned, production endpoints)
// ============================================================================
Route::prefix('v1')->group(function () {
    // Public authentication route
    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.v1.login');

    // Protected routes (require auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        // Authentication
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('api.v1.logout');

        // SRS endpoints
        Route::get('/srs/due', [SrsController::class, 'due'])
            ->name('api.v1.srs.due');

        Route::get('/srs/practice', [SrsController::class, 'practice'])
            ->name('api.v1.srs.practice');

        Route::post('/srs/review', [SrsController::class, 'review'])
            ->name('api.v1.srs.review');
    });
});

// ============================================================================
// Legacy redirects (permanent 308 redirects to versioned endpoints)
// ============================================================================
// Preserve HTTP method and request body via 308 Permanent Redirect
Route::get('/srs/due', function () {
    return redirect()->to('/api/v1/srs/due', 308);
})->middleware('auth:sanctum');

Route::get('/srs/practice', function () {
    return redirect()->to('/api/v1/srs/practice', 308);
})->middleware('auth:sanctum');

Route::post('/srs/review', function () {
    return redirect()->to('/api/v1/srs/review', 308);
})->middleware('auth:sanctum');

// ============================================================================
// TESTING & VERIFICATION
// ============================================================================
// Clear route cache and verify:
//   php artisan route:clear
//   php artisan route:list --path=api -v
//
// Expected output (5 main routes + 3 legacy redirects):
//   POST   api/v1/login          → Api\AuthController@login
//   POST   api/v1/logout         → Api\AuthController@logout       [auth:sanctum]
//   GET    api/v1/srs/due        → Api\SrsController@due           [auth:sanctum]
//   GET    api/v1/srs/practice   → Api\SrsController@practice      [auth:sanctum]
//   POST   api/v1/srs/review     → Api\SrsController@review        [auth:sanctum]
//   GET    api/srs/due           → Closure (308 redirect)          [auth:sanctum]
//   GET    api/srs/practice      → Closure (308 redirect)          [auth:sanctum]
//   POST   api/srs/review        → Closure (308 redirect)          [auth:sanctum]
//
// Test login:
//   curl -i -X POST http://127.0.0.1:8000/api/v1/login \
//     -H "Content-Type: application/json" \
//     -d "{\"email\":\"edward@rockymountainweb.design\",\"password\":\"password\"}"
//
// Test protected routes (replace <token> with actual token):
//   curl -i -X GET http://127.0.0.1:8000/api/v1/srs/due \
//     -H "Authorization: Bearer <token>"
//
//   curl -i -X GET http://127.0.0.1:8000/api/v1/srs/practice?subject=TAX&limit=10 \
//     -H "Authorization: Bearer <token>"
//
// Test legacy redirect (should return 308 and Location header):
//   curl -i -X GET http://127.0.0.1:8000/api/srs/due \
//     -H "Authorization: Bearer <token>"
