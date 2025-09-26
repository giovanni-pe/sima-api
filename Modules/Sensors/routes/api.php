<?php

use Illuminate\Support\Facades\Route;
use Modules\Sensors\Http\Controllers\Api\SensorsController;

Route::prefix('sensors')->group(function () {
    Route::get('/',        [SensorsController::class, 'index']);
    Route::get('/active',  [SensorsController::class, 'active']);
    Route::post('/',       [SensorsController::class, 'store']);
    Route::get('/{id}',    [SensorsController::class, 'show']);
    Route::put('/{id}',    [SensorsController::class, 'update']);
    Route::delete('/{id}', [SensorsController::class, 'destroy']);
});