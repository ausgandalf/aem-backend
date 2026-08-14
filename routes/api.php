<?php

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\StageController as AdminStageController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicOrganizationController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\Officer\OfficerApplicationController;

// ── Admin routes ────────────────────────────────────
Route::middleware(['auth:sanctum', 'active', 'role:admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::get('/users/{user}/logs', [UserController::class, 'logs']);
        Route::patch('/users/{user}/allow', [UserController::class, 'allow']);
        Route::patch('/users/{user}/block', [UserController::class, 'block']);
        Route::patch('/users/{user}/verify-email', [UserController::class, 'toggleEmailVerification']);
        Route::put('/users/{user}/password', [UserController::class, 'updatePassword']);
        Route::patch('/users/{user}', [UserController::class, 'update']);

        Route::get('/organizations', [OrganizationController::class, 'index']);
        Route::post('/organizations', [OrganizationController::class, 'store']);
        Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
        Route::patch('/organizations/{organization}', [OrganizationController::class, 'update']);
        Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy']);

        // Stage / Sector management
        Route::get('/stages', [AdminStageController::class, 'index']);
        Route::post('/stages', [AdminStageController::class, 'store']);
        Route::post('/stages/reorder', [AdminStageController::class, 'reorder']);
        Route::patch('/stages/{stage}', [AdminStageController::class, 'update']);

        Route::post('/stages/{stage}/sectors', [SectorController::class, 'store']);
        Route::post('/stages/{stage}/sectors/reorder', [SectorController::class, 'reorder']);
        Route::patch('/sectors/{sector}', [SectorController::class, 'update']);
        Route::delete('/sectors/{sector}', [SectorController::class, 'destroy']);
    });

    
// ── Public routes ──────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::get('/roles', [RoleController::class, 'index']);

// ── Quick Apply (public) ───────────────────────────
Route::get('/organizations', [PublicOrganizationController::class, 'index'])->middleware('throttle:30,1');
Route::post('/apply', [ApplicationController::class, 'quickApply'])->middleware('throttle:5,1');

// ── Email verification ─────────────────────────────
// The link in the email points here
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // marks email as verified + fires Verified event
    
    // Redirect to frontend login page with success flag
    return redirect(config('app.frontend_url') . '/login?verified=1');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Resend verification email
Route::post('/email/resend', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified'], 400);
    }
    
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Verification link sent']);
})->middleware(['auth:sanctum', 'throttle:6,1']);

// ── Protected routes ───────────────────────────────
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Self-service profile & password (any authenticated, active user)
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);

    // Workflow stages (cached, read-only)
    Route::get('/stages', [StageController::class, 'index']);

    // Applicant's own applications
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::patch('/applications/{application}', [ApplicationController::class, 'update']);

    // Documents (direct-to-S3 upload flow, keyed under documents/{application_id}/)
    Route::post('/applications/{application}/documents/presign', [DocumentController::class, 'presign']);
    Route::get('/applications/{application}/documents', [DocumentController::class, 'index']);
    Route::post('/applications/{application}/documents', [DocumentController::class, 'store']);
    Route::patch('/applications/{application}/documents/{document}', [DocumentController::class, 'update']);
    Route::delete('/applications/{application}/documents/{document}', [DocumentController::class, 'destroy']);

    // Officer review queue + per-stage Process page
    Route::get('/officer/applications', [OfficerApplicationController::class, 'index']);
    Route::get('/applications/{application}/process/{stage}', [OfficerApplicationController::class, 'process']);
    Route::patch('/inspections/{inspection}', [InspectionController::class, 'update']);
});