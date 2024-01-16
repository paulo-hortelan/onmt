<?php

use Illuminate\Support\Facades\Route;
use PauloHortelan\OltMonitoring\Http\Controllers\OltController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->controller(OltController::class)->group(function () {
    Route::get('/olts', 'index');
    Route::post('/olts', 'store');
    Route::get('/olts/{olt}', 'show');
    Route::put('/olts/{olt}', 'update');
    Route::delete('/olts/{olt}', 'destroy');
});
