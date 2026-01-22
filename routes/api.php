<?php

declare(strict_types=1);
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\api\BuyerController;
use App\Http\Controllers\api\LicenseController;
use App\Http\Controllers\api\ProductController;

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
    Route::post('register', [LicenseController::class, 'register'])->name('register');
    Route::post('validate', [LicenseController::class, 'validate'])->name('validate');
    Route::post('get-active-domain', [LicenseController::class, 'getDomain'])->name('get-active-domain');
    Route::post('check-update', [ProductController::class, 'checkUpdate'])->name('check-update');
    Route::post('reset-license', [LicenseController::class, 'resetLicense'])->name('reset-license');
    Route::get('buyers/{buyer?}', [BuyerController::class, 'buyers'])->name('buyers');
    Route::get('api-requests', [ApiController::class, 'apiRequests'])->name('api-requests');
    Route::get('api-activities', [ApiController::class, 'apiActivities'])->name('api-activities');
    Route::get('download-update/sql/{vid} ', [ProductController::class, 'downloadSql'])->name('downloadsql');
    Route::get('download-update/main/{vid} ', [ProductController::class, 'downloadMain'])->name('downloadmain');
    Route::get('products/{product?}', [ProductController::class, 'products'])->name('products');
    Route::get('blocked-ips/{ip?}', [ApiController::class, 'blockedIps'])->name('blocked-ips');
    Route::get('license-report', [LicenseController::class, 'licenseReport'])->name('license-report');

});