<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ============================================================
// HEALTH CHECK
// ============================================================
Route::get('/health', fn () => response()->json([
    'success' => true,
    'status'  => 'ok',
    'app'     => 'Platinum Project API',
    'version' => '1.0.0',
    'time'    => now()->toISOString(),
]));

// ============================================================
// PUBLIC ROUTES (no auth needed)
// ============================================================

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Admin login
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

// Packages (public catalog)
Route::get('/packages',       [PackageController::class, 'index']);
Route::get('/packages/{slug}', [PackageController::class, 'show']);

// Booking (guest allowed)
Route::post('/bookings', [BookingController::class, 'store'])
     ->middleware('throttle:60,1'); // 60 bookings per minute per IP

// Logout (handles token revocation gracefully)
Route::post('/logout',       [AuthController::class, 'logout']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

// ============================================================
// USER ROUTES (must be logged in)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user',             [AuthController::class, 'me']);
    Route::get('/user/bookings',    [BookingController::class, 'myBookings']);
});

// ============================================================
// ADMIN ROUTES (auth + admin role required)
// ============================================================
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/statistics',      [DashboardController::class, 'stats']);

    // Bookings management
    Route::get('/bookings',         [BookingController::class, 'index']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}', [BookingController::class, 'update']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy']);

    // Export
    Route::get('/export/excel', [ExportController::class, 'excel']);

    // Financial Management
    Route::get('/financial/dashboard',          [FinancialController::class, 'dashboard']);
    Route::get('/financial/cash-flow',          [FinancialController::class, 'cashFlow']);
    Route::get('/financial/profit-by-package',  [FinancialController::class, 'profitByPackage']);
    Route::get('/financial/bookings',           [FinancialController::class, 'bookings']);
    Route::get('/financial/booking/{id}',       [FinancialController::class, 'bookingDetail']);
    Route::post('/financial/payment',           [FinancialController::class, 'recordPayment']);
    Route::post('/financial/expense',           [FinancialController::class, 'addExpense']);
    Route::put('/financial/expense/{id}',       [FinancialController::class, 'updateExpense']);
    Route::delete('/financial/expense/{id}',    [FinancialController::class, 'deleteExpense']);
});

