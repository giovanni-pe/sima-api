<?php

use Illuminate\Support\Facades\Route;
use Modules\Parcels\Http\Controllers\Api\ParcelsController;

Route::prefix('parcels')->group(function () {
    Route::get('/',        [ParcelsController::class, 'index']);
    Route::get('/active',  [ParcelsController::class, 'active']);
    Route::post('/',       [ParcelsController::class, 'store']);
    Route::get('/{id}',    [ParcelsController::class, 'show']);
    Route::put('/{id}',    [ParcelsController::class, 'update']);
    Route::delete('/{id}', [ParcelsController::class, 'destroy']);
});
