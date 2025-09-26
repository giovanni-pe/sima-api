<?php

use Illuminate\Support\Facades\Route;
use Modules\SensorReadings\Http\Controllers\SensorReadingsController;


 Route::prefix('sensor-readings')->group(function () {
    Route::get('/',        [SensorReadingsController::class, 'index']);
    Route::get('/active',  [SensorReadingsController::class, 'active']);
    Route::post('/',       [SensorReadingsController::class, 'store']);
    Route::get('/{id}',    [SensorReadingsController::class, 'show']);
    Route::put('/{id}',    [SensorReadingsController::class, 'update']);
    Route::delete('/{id}', [SensorReadingsController::class, 'destroy']);
});   


