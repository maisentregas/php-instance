<?php

use App\Http\Controllers\Api\{
    LifeInsuranceController,
    RaiaDrogasilTrackingController
};
use App\Http\Middleware\ValidateInternalToken;

use Illuminate\Support\Facades\Route;

Route::prefix('raia-drogasil')->name('raia_drogasil.')->middleware(ValidateInternalToken::class)->group(function () {
    Route::post('send-tracking', [RaiaDrogasilTrackingController::class, 'sendTracking'])->name('send_tracking');
});

Route::prefix('life-insurance')->name('life-insurance.')->middleware(ValidateInternalToken::class)->group(function () {
    Route::post('insure-person', [LifeInsuranceController::class, 'insurePerson'])->name('insure_person');
    Route::post('add-geolocation', [LifeInsuranceController::class, 'addGeolocation'])->name('add_geolocation');
    Route::post('finalize', [LifeInsuranceController::class, 'finalizeInsurance'])->name('finalize_insurance');
    Route::post('cancel', [LifeInsuranceController::class, 'cancelOrFinalizeInsurance'])->name('cancel_insurance');
});