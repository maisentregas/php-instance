<?php

use App\Http\Controllers\Api\RaiaDrogasilTrackingController;

use Illuminate\Support\Facades\Route;

Route::prefix('raia-drogasil')->name('raia_drogasil.')->group(function () {
    Route::post('tracking', [RaiaDrogasilTrackingController::class, 'sendTracking'])->name('tracking');
})/*->middleware()*/;