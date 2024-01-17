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

Route::controller(OltController::class)->group(function () {
    Route::get('/olts', 'index')->name('olts.index');
    Route::post('/olts', 'store')->name('olts.store');
    Route::get('/olts/{olt}', 'show')->name('olts.show');
    Route::put('/olts/{olt}', 'update')->name('olts.update');
    Route::delete('/olts/{olt}', 'destroy')->name('olts.destroy');
});
