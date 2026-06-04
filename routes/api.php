<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/migrate-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return response()->json(['message' => 'Database migrated and seeded successfully!']);
});

Route::get('/test-db', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        return response()->json(['status' => 'success', 'message' => 'Terkoneksi ke database Supabase!']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

use App\Http\Controllers\ProfileController;
Route::get('/schools', [ProfileController::class, 'schools']);
use App\Http\Controllers\SkillController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\ApplicationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('profile');
    });

    // Profile (update is enough since create is done on register)
    Route::post('/profile', [ProfileController::class, 'update']);

    // Skills
    Route::apiResource('skills', SkillController::class)->except(['show']);
    
    // Portfolios
    Route::apiResource('portfolios', PortfolioController::class)->except(['show']);

    // Vacancies
    Route::apiResource('vacancies', VacancyController::class);
    
    // Applications
    Route::apiResource('applications', ApplicationController::class)->except(['show']);
    
    // Additional features
    Route::get('/students', [ProfileController::class, 'students']); // For School/Industry to view students
    Route::get('/schools/skill-gap', [ProfileController::class, 'skillGap']); // For School AI Analysis
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index']); // For School Analytics
    
    // Journals
    Route::get('/journals', [\App\Http\Controllers\JournalController::class, 'index']);
    Route::post('/journals', [\App\Http\Controllers\JournalController::class, 'store']);
    Route::put('/journals/{id}', [\App\Http\Controllers\JournalController::class, 'update']);
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::put('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});
