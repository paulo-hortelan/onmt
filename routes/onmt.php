<?php

use Illuminate\Support\Facades\Route;
use PauloHortelan\Onmt\Http\Controllers\CeoController;
use PauloHortelan\Onmt\Http\Controllers\CeoSplitterController;
use PauloHortelan\Onmt\Http\Controllers\CtoController;
use PauloHortelan\Onmt\Http\Controllers\DioController;
use PauloHortelan\Onmt\Http\Controllers\OltController;
use PauloHortelan\Onmt\Http\Controllers\OntController;

/*
|--------------------------------------------------------------------------
| ONM Routes
|--------------------------------------------------------------------------
|
*/

Route::controller(OltController::class)->group(function () {
    Route::get('/olts', 'index')->name('olts.index');
    Route::post('/olts', 'store')->name('olts.store');
    Route::get('/olts/{olt}', 'show')->name('olts.show');
    Route::put('/olts/{olt}', 'update')->name('olts.update');
    Route::delete('/olts/{olt}', 'destroy')->name('olts.destroy');
});

Route::controller(DioController::class)->group(function () {
    Route::get('/dios', 'index')->name('dios.index');
    Route::post('/dios', 'store')->name('dios.store');
    Route::get('/dios/{dio}', 'show')->name('dios.show');
    Route::put('/dios/{dio}', 'update')->name('dios.update');
    Route::delete('/dios/{dio}', 'destroy')->name('dios.destroy');
});

Route::controller(CeoController::class)->group(function () {
    Route::get('/ceos', 'index')->name('ceos.index');
    Route::post('/ceos', 'store')->name('ceos.store');
    Route::get('/ceos/{ceo}', 'show')->name('ceos.show');
    Route::put('/ceos/{ceo}', 'update')->name('ceos.update');
    Route::delete('/ceos/{ceo}', 'destroy')->name('ceos.destroy');
});

Route::controller(CeoSplitterController::class)->group(function () {
    Route::get('/ceo-splitters', 'index')->name('ceo-splitters.index');
    Route::post('/ceo-splitters', 'store')->name('ceo-splitters.store');
    Route::get('/ceo-splitters/{ceoSplitter}', 'show')->name('ceo-splitters.show');
    Route::put('/ceo-splitters/{ceoSplitter}', 'update')->name('ceo-splitters.update');
    Route::delete('/ceo-splitters/{ceoSplitter}', 'destroy')->name('ceo-splitters.destroy');
});

Route::controller(CtoController::class)->group(function () {
    Route::get('/ctos', 'index')->name('ctos.index');
    Route::post('/ctos', 'store')->name('ctos.store');
    Route::get('/ctos/{cto}', 'show')->name('ctos.show');
    Route::put('/ctos/{cto}', 'update')->name('ctos.update');
    Route::delete('/ctos/{cto}', 'destroy')->name('ctos.destroy');
});

Route::controller(OntController::class)->group(function () {
    Route::get('/onts', 'index')->name('onts.index');
    Route::post('/onts', 'store')->name('onts.store');
    Route::get('/onts/{ont}', 'show')->name('onts.show');
    Route::put('/onts/{ont}', 'update')->name('onts.update');
    Route::delete('/onts/{ont}', 'destroy')->name('onts.destroy');
});
