<?php

use App\Http\Controllers\Master\PlottingController;
use App\Http\Controllers\Master\WilayahController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? view('welcome') : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/master/wilayah', [WilayahController::class, 'index'])->name('master.wilayah.index');

    Route::get('/master/plotting', [PlottingController::class, 'index'])->name('master.plotting.index');
    Route::post('/master/plotting', [PlottingController::class, 'store'])->name('master.plotting.store');
    Route::put('/master/plotting/{plotting}', [PlottingController::class, 'update'])->name('master.plotting.update');
    Route::delete('/master/plotting/{plotting}', [PlottingController::class, 'destroy'])->name('master.plotting.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
