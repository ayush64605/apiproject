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
    Route::post('reset-license', [ApiController::class, 'resetLicense'])->name('reset-license');
    Route::get('buyers', [ApiController::class, 'buyers'])->name('buyers');
    Route::get('buyerdetails/{buyer}', [ApiController::class, 'buyerdetails'])->name('buyerdetails');
    Route::get('api-requests', [ApiController::class, 'apiRequests'])->name('api-requests');
    Route::get('api-activities', [ApiController::class, 'apiActivities'])->name('api-activities');
    Route::get('download-update/sql/{vid} ', [ApiController::class, 'downloadSql'])->name('downloadsql');
    Route::get('download-update/main/{vid} ', [ApiController::class, 'downloadMain'])->name('downloadmain');
    Route::get('products', [ApiController::class, 'products'])->name('products');
    Route::get('productdetails/{product}', [ApiController::class, 'productDetails'])->name('productdetails');
    Route::get('blocked-ips', [ApiController::class, 'blockedIps'])->name('blocked-ips');
    Route::get('blockipdetails/{ip}', [ApiController::class, 'blockipDetails'])->name('blockipdetails');
    Route::get('license-report', [ApiController::class, 'licenseReport'])->name('license-report');

});