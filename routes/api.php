<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\RubricController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\AuditLogController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/request-otp', [AuthController::class, 'requestOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Protected
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Team
    Route::prefix('team')->group(function () {
        Route::get('/', [TeamController::class, 'show']);
        Route::put('/', [TeamController::class, 'update']);
        Route::get('/history', [TeamController::class, 'history']);
        Route::post('/restore/{historyId}', [TeamController::class, 'restore']);
    });

    // Submission & files
    Route::apiResource('submissions', SubmissionController::class)->except(['index', 'destroy']);
    Route::post('/submissions/{submission}/files', [SubmissionController::class, 'uploadFiles']);
    Route::delete('/submission-files/{file}', [SubmissionController::class, 'deleteFile']);

    // Rubric (read only for peserta/juri, admin can write)
    Route::get('/rubrics/stage/{stageId}', [RubricController::class, 'getByStage']);
    Route::post('/rubrics', [RubricController::class, 'store'])->middleware('role:admin');
    Route::put('/rubrics/{rubric}', [RubricController::class, 'update'])->middleware('role:admin');
    Route::delete('/rubrics/{rubric}', [RubricController::class, 'destroy'])->middleware('role:admin');

    // Scores
    Route::post('/scores', [ScoreController::class, 'store'])->middleware('role:juri');
    Route::get('/scores/submission/{submissionId}', [ScoreController::class, 'getBySubmission']);
    Route::get('/scores/auto-recap/{submissionId}', [ScoreController::class, 'autoRecap']);

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('role:admin');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('role:admin');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('role:admin');

    // FAQ
    Route::get('/faqs', [FaqController::class, 'index']);
    Route::post('/faqs', [FaqController::class, 'store'])->middleware('role:admin');
    Route::put('/faqs/{faq}', [FaqController::class, 'update'])->middleware('role:admin');
    Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->middleware('role:admin');

    // Certificates
    Route::get('/certificates/team/{teamId}', [CertificateController::class, 'getByTeam']);
    Route::post('/certificates/generate/{eventId}', [CertificateController::class, 'generate'])->middleware('role:admin');

    // Dashboard
    Route::get('/dashboard/peserta', [DashboardController::class, 'peserta'])->middleware('role:peserta');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin');
    Route::get('/dashboard/juri', [DashboardController::class, 'juri'])->middleware('role:juri');

    // Exports (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/export/teams', [ExportController::class, 'teams']);
        Route::get('/export/scores', [ExportController::class, 'scores']);
        Route::get('/export/selection-results', [ExportController::class, 'selectionResults']);
        Route::get('/export/submissions', [ExportController::class, 'submissions']);
    });

    // Audit logs (admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/entity/{entityType}/{entityId}', [AuditLogController::class, 'getByEntity']);
    });
});