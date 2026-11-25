<?php

use App\Http\Controllers\Audit\SprinController;
use App\Http\Controllers\Master\PlottingController;
use App\Http\Controllers\Master\TemplatePkaController;
use App\Http\Controllers\Master\TimController;
use App\Http\Controllers\Master\UserController;
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
    Route::get('/master/user', [UserController::class, 'index'])->name('master.user.index');
    Route::post('/master/user', [UserController::class, 'store'])->name('master.user.store');
    Route::post('/master/user/leave-impersonate', [UserController::class, 'leaveImpersonate'])->name('master.user.leave-impersonate');
    Route::post('/master/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('master.user.reset-password');
    Route::post('/master/user/{user}/impersonate', [UserController::class, 'impersonate'])->name('master.user.impersonate');
    Route::put('/master/user/{user}', [UserController::class, 'update'])->name('master.user.update');
    Route::delete('/master/user/{user}', [UserController::class, 'destroy'])->name('master.user.destroy');

    Route::get('/master/tim', [TimController::class, 'index'])->name('master.tim.index');
    Route::post('/master/tim', [TimController::class, 'store'])->name('master.tim.store');
    Route::post('/master/tim/sync', [TimController::class, 'syncAll'])->name('master.tim.sync-all');
    Route::post('/master/tim/{tim}/sync', [TimController::class, 'sync'])->name('master.tim.sync');
    Route::put('/master/tim/{tim}', [TimController::class, 'update'])->name('master.tim.update');
    Route::delete('/master/tim/{tim}', [TimController::class, 'destroy'])->name('master.tim.destroy');

    Route::get('/master/wilayah', [WilayahController::class, 'index'])->name('master.wilayah.index');
    Route::post('/master/wilayah', [WilayahController::class, 'store'])->name('master.wilayah.store');
    Route::put('/master/wilayah/{level}/{code}', [WilayahController::class, 'update'])->name('master.wilayah.update');
    Route::delete('/master/wilayah/{level}/{code}', [WilayahController::class, 'destroy'])->name('master.wilayah.destroy');

    Route::get('/master/plotting', [PlottingController::class, 'index'])->name('master.plotting.index');
    Route::post('/master/plotting', [PlottingController::class, 'store'])->name('master.plotting.store');
    Route::put('/master/plotting/{plotting}', [PlottingController::class, 'update'])->name('master.plotting.update');
    Route::delete('/master/plotting/{plotting}', [PlottingController::class, 'destroy'])->name('master.plotting.destroy');

    Route::get('/master/template/pka', [TemplatePkaController::class, 'index'])->name('master.template.pka.index');
    Route::post('/master/template/pka', [TemplatePkaController::class, 'store'])->name('master.template.pka.store');
    Route::get('/master/template/pka/{pka}', [TemplatePkaController::class, 'show'])->name('master.template.pka.show');
    Route::put('/master/template/pka/{pka}', [TemplatePkaController::class, 'update'])->name('master.template.pka.update');
    Route::delete('/master/template/pka/{pka}', [TemplatePkaController::class, 'destroy'])->name('master.template.pka.destroy');
    Route::post('/master/template/pka/{pka}/duplicate', [TemplatePkaController::class, 'duplicate'])->name('master.template.pka.duplicate');

    Route::get('/audit/sprin', [SprinController::class, 'index'])->name('audit.sprin.index');
    Route::post('/audit/sprin', [SprinController::class, 'store'])->name('audit.sprin.store');
    Route::get('/audit/sprin/{sprin}/reviews', [SprinController::class, 'getReviews'])->name('audit.sprin.reviews.index');
    Route::post('/audit/sprin/{sprin}/reviews', [SprinController::class, 'storeReview'])->name('audit.sprin.reviews.store');
    Route::put('/audit/sprin/{sprin}', [SprinController::class, 'update'])->name('audit.sprin.update');
    Route::delete('/audit/sprin/{sprin}', [SprinController::class, 'destroy'])->name('audit.sprin.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
