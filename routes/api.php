<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WorkingHourController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Availability Routes
Route::prefix('availability')->group(function () {
    Route::get('/slots', [AvailabilityController::class, 'getAvailableSlots']);
    Route::get('/check', [AvailabilityController::class, 'checkSlotAvailability']);
});

// Appointment Routes
Route::prefix('appointments')->group(function () {
    Route::post('/', [AppointmentController::class, 'store']);
    Route::get('/range', [AppointmentController::class, 'getAppointmentsForDateRange']);
    Route::get('/{id}', [AppointmentController::class, 'show']);
    Route::delete('/{id}/cancel', [AppointmentController::class, 'cancel']);
});

// Service Routes
Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/{id}', [ServiceController::class, 'show']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::put('/{id}', [ServiceController::class, 'update']);
    Route::delete('/{id}', [ServiceController::class, 'destroy']);
});

// Working Hours Routes
Route::prefix('working-hours')->group(function () {
    Route::get('/', [WorkingHourController::class, 'index']);
    Route::get('/{id}', [WorkingHourController::class, 'show']);
    Route::post('/', [WorkingHourController::class, 'store']);
    Route::put('/{id}', [WorkingHourController::class, 'update']);
    Route::delete('/{id}', [WorkingHourController::class, 'destroy']);
});
