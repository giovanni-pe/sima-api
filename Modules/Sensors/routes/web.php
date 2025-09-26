<?php

use Illuminate\Support\Facades\Route;
use Modules\Sensors\Http\Controllers\SensorsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sensors', SensorsController::class)->names('sensors');
});
