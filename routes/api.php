<?php

use App\Http\Controllers\Zitadel\ClaimsController;
use App\Http\Controllers\Zitadel\EdulogIntentController;
use App\Http\Controllers\Zitadel\EventsController;
use Illuminate\Support\Facades\Route;

// Aufrufe aus Zitadel (Actions v2), signiert mit ZITADEL-Signature – kein Token, eigenes Abruflimit
Route::prefix('v1/zitadel')->middleware('throttle:api')->group(function () {
    Route::post('claims', ClaimsController::class)->middleware('zitadel.signature:claims');
    Route::post('edulog-intent', EdulogIntentController::class)->middleware('zitadel.signature:intent');
    Route::post('events', EventsController::class)->middleware('zitadel.signature:events');
});
