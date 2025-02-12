<?php

use App\Http\Controllers\Api\RaiaDrogasilTrackingController;
use App\Http\Middleware\ValidateInternalToken;

use Illuminate\Support\Facades\Route;

Route::prefix('raia-drogasil')->name('raia_drogasil.')->middleware(ValidateInternalToken::class)->group(function () {
    Route::post('send-tracking', [RaiaDrogasilTrackingController::class, 'sendTracking'])->name('send_tracking');
});