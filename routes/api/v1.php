<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes  (auto-prefixed with /api/v1)
|--------------------------------------------------------------------------
| Public endpoints (landing page) are declared outside the auth group.
| Admin endpoints (CMS) require auth:api + spatie permission middleware.
*/

// --- Health check (public) ---
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'version' => 'v1',
]));

// --- Public content endpoints (consumed by the landing page) ---
Route::prefix('public')->group(function () {
    // Route::get('/hero', [HeroController::class, 'show']);
    // Route::get('/about', [AboutController::class, 'show']);
    // Route::get('/partners', [PartnerController::class, 'index']);
    // Route::get('/partners/{slug}', [PartnerController::class, 'show']);
    // Route::get('/projects', [ProjectController::class, 'index']);
    // Route::get('/projects/{slug}', [ProjectController::class, 'show']);
    // Route::post('/contact-inquiries', [ContactInquiryController::class, 'store']);
});

// --- Auth (Passport) ---
Route::prefix('auth')->group(function () {
    // Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:api')->group(function () {
        // Route::get('/me', [AuthController::class, 'me']);
        // Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// --- Admin content management (CMS) ---
Route::middleware('auth:api')->group(function () {
    // Route::post('/uploads', [UploadController::class, 'store']);
    // Route::get('/hero', [HeroController::class, 'show']);
    // Route::put('/hero', [HeroController::class, 'update']);
    //
    // Route::apiResource('partners', PartnerController::class);
    // Route::apiResource('projects', ProjectController::class);
    // Route::apiResource('users', UserController::class)->middleware('role:admin');
    //
    // Route::get('/contact-inquiries', [ContactInquiryController::class, 'index']);
    // Route::delete('/contact-inquiries/{id}', [ContactInquiryController::class, 'destroy']);
});
