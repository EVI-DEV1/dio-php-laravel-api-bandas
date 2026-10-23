<?php

use App\Http\Controllers\BandController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function (): void {
    Route::get('/bands', [BandController::class, 'index'])->name('bands.index');
    Route::get('/bands/gender/{gender}', [BandController::class, 'byGender'])->name('bands.gender');
    Route::get('/bands/{band}', [BandController::class, 'show'])->whereNumber('band')->name('bands.show');
    Route::post('/bands/store', [BandController::class, 'store'])->name('bands.store');
});
