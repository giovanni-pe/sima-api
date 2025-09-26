<?php

use Illuminate\Support\Facades\Route;
use Modules\SensorReadings\Http\Controllers\SensorReadingsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sensorreadings', SensorReadingsController::class)->names('sensorreadings');
});
