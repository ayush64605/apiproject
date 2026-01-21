<?php

declare(strict_types=1);
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes are versioned using grazulex/laravel-apiroute v2.x.
| Versions are defined in config/apiroute.php and route files are
| located in routes/api/{version}.php
|
| Supports URI path, header, query, and Accept header detection.
| See config/apiroute.php for configuration options.
|
*/

// Routes are now loaded automatically from config/apiroute.php
// See routes/api/v1.php for version 1 routes

Route::prefix('license')->group(function () {
    Route::post('register', [ApiController::class, 'register'])->name('register');
    Route::post('validate', [ApiController::class, 'validate'])->name('validate');
    Route::post('get-active-domain', [ApiController::class, 'getDomain'])->name('get-active-domain');
    Route::post('check-update', [ApiController::class, 'checkUpdate'])->name('check-update');
});