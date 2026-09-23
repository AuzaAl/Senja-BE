<?php

use App\Http\Controllers\Api\V1\AboutController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactInquiryController;
use App\Http\Controllers\Api\V1\HeroController;
use App\Http\Controllers\Api\V1\PartnerController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\UploadController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes  (auto-prefixed with /api/v1)
|--------------------------------------------------------------------------
| Public endpoints (landing page) are declared outside the auth group.
| Admin endpoints (CMS) require auth:api + spatie permission middleware.
| Content resources share the same path; the HTTP method decides
| whether a request is a public read or an admin write.
*/

// --- Health check (public) ---
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'version' => 'v1',
]));

// --- Auth (Passport) ---
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// --- Public content endpoints (consumed by the landing page) ---
Route::get('/hero', [HeroController::class, 'show']);
Route::get('/about', [AboutController::class, 'show']);

Route::get('/partners', [PartnerController::class, 'index']);
Route::get('/partners/{partner:slug}', [PartnerController::class, 'show']);

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project:slug}', [ProjectController::class, 'show']);

Route::post('/contact-inquiries', [ContactInquiryController::class, 'store']);

// --- Admin content management (CMS) ---
Route::middleware(['auth:api', 'permission:uploads.create'])->group(function () {
    Route::post('/uploads', [UploadController::class, 'store']);
});

Route::middleware('auth:api')->group(function () {
    Route::middleware('permission:hero.update')->group(function () {
        Route::put('/hero', [HeroController::class, 'update']);
    });

    Route::middleware('permission:about.update')->group(function () {
        Route::put('/about', [AboutController::class, 'update']);
    });

    Route::middleware('permission:partners.create')->group(function () {
        Route::post('/partners', [PartnerController::class, 'store']);
    });

    Route::middleware('permission:partners.update')->group(function () {
        Route::match(['put', 'patch'], '/partners/{partner}', [PartnerController::class, 'update']);
    });

    Route::middleware('permission:partners.delete')->group(function () {
        Route::delete('/partners/{partner}', [PartnerController::class, 'destroy']);
    });

    Route::middleware('permission:projects.create')->group(function () {
        Route::post('/projects', [ProjectController::class, 'store']);
    });

    Route::middleware('permission:projects.update')->group(function () {
        Route::match(['put', 'patch'], '/projects/{project}', [ProjectController::class, 'update']);
    });

    Route::middleware('permission:projects.delete')->group(function () {
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    });

    Route::middleware('permission:contact-inquiries.view')->group(function () {
        Route::get('/contact-inquiries', [ContactInquiryController::class, 'index']);
    });

    Route::middleware('permission:contact-inquiries.delete')->group(function () {
        Route::delete('/contact-inquiries/{inquiry}', [ContactInquiryController::class, 'destroy']);
    });

    // --- User management (admin only) ---
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });

    Route::middleware('permission:users.create')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });

    Route::middleware('permission:users.update')->group(function () {
        Route::match(['put', 'patch'], '/users/{user}', [UserController::class, 'update']);
    });

    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    Route::middleware('permission:users.assign-role')->group(function () {
        Route::post('/users/{user}/roles', [UserController::class, 'assignRoles']);
        Route::delete('/users/{user}/roles', [UserController::class, 'revokeRoles']);
    });
});
