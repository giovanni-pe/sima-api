<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlUnits\Http\Controllers\Api\ControlUnitsController;

Route::prefix('control-units')->group(function () {
    Route::get('/',        [ControlUnitsController::class, 'index']);
    Route::get('/active',  [ControlUnitsController::class, 'active']);
    Route::post('/',       [ControlUnitsController::class, 'store']);
    Route::get('/{id}',    [ControlUnitsController::class, 'show']);
    Route::put('/{id}',    [ControlUnitsController::class, 'update']);
    Route::delete('/{id}', [ControlUnitsController::class, 'destroy']);
});