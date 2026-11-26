<?php

use App\Http\Controllers\Audit\PkaController;
use App\Http\Controllers\Audit\PkaLangkahKerjaController;
use App\Http\Controllers\Audit\SprinController;
use App\Http\Controllers\Master\KodeController;
use App\Http\Controllers\Master\PlottingController;
use App\Http\Controllers\Master\SubKodeController;
use App\Http\Controllers\Master\TemplateKkaController;
use App\Http\Controllers\Master\TemplateLangkahKerjaController;
use App\Http\Controllers\Master\TemplateLangkahKerjaDetailController;
use App\Http\Controllers\Master\TemplatePkaController;
use App\Http\Controllers\Master\TimController;
use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\WilayahController;
use App\Http\Controllers\Pelaksanaan\KkaController;
use App\Http\Controllers\ProfileController;
use App\Models\Kode;
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

    Route::prefix('master')->where(['jenis' => implode('|', Kode::JENIS)])->group(function () {
        Route::get('/kode/{jenis}', [KodeController::class, 'index'])->name('master.kode.index');
        Route::post('/kode/{jenis}', [KodeController::class, 'store'])->name('master.kode.store');
        Route::put('/kode/{jenis}/{kode}', [KodeController::class, 'update'])->name('master.kode.update');
        Route::delete('/kode/{jenis}/{kode}', [KodeController::class, 'destroy'])->name('master.kode.destroy');

        Route::get('/sub-kode/{jenis}', [SubKodeController::class, 'index'])->name('master.sub-kode.index');
        Route::post('/sub-kode/{jenis}', [SubKodeController::class, 'store'])->name('master.sub-kode.store');
        Route::put('/sub-kode/{jenis}/{subKode}', [SubKodeController::class, 'update'])->name('master.sub-kode.update');
        Route::delete('/sub-kode/{jenis}/{subKode}', [SubKodeController::class, 'destroy'])->name('master.sub-kode.destroy');
    });

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

    Route::get('/master/template/kka', [TemplateKkaController::class, 'index'])->name('master.template.kka.index');
    Route::post('/master/template/kka', [TemplateKkaController::class, 'store'])->name('master.template.kka.store');
    Route::get('/master/template/kka/{kka}', [TemplateKkaController::class, 'show'])->name('master.template.kka.show');
    Route::put('/master/template/kka/{kka}', [TemplateKkaController::class, 'update'])->name('master.template.kka.update');
    Route::delete('/master/template/kka/{kka}', [TemplateKkaController::class, 'destroy'])->name('master.template.kka.destroy');
    Route::post('/master/template/kka/{kka}/duplicate', [TemplateKkaController::class, 'duplicate'])->name('master.template.kka.duplicate');

    Route::get('/master/template/langkah-kerja', [TemplateLangkahKerjaController::class, 'index'])->name('master.template.langkah-kerja.index');
    Route::post('/master/template/langkah-kerja', [TemplateLangkahKerjaController::class, 'store'])->name('master.template.langkah-kerja.store');
    Route::get('/master/template/langkah-kerja/{langkahKerja}', [TemplateLangkahKerjaController::class, 'show'])->name('master.template.langkah-kerja.show');
    Route::put('/master/template/langkah-kerja/{langkahKerja}', [TemplateLangkahKerjaController::class, 'update'])->name('master.template.langkah-kerja.update');
    Route::delete('/master/template/langkah-kerja/{langkahKerja}', [TemplateLangkahKerjaController::class, 'destroy'])->name('master.template.langkah-kerja.destroy');
    Route::post('/master/template/langkah-kerja/{langkahKerja}/duplicate', [TemplateLangkahKerjaController::class, 'duplicate'])->name('master.template.langkah-kerja.duplicate');

    Route::get('/master/template/langkah-kerja/{langkahKerja}/detail', [TemplateLangkahKerjaDetailController::class, 'index'])->name('master.template.langkah-kerja.details.index');
    Route::post('/master/template/langkah-kerja/{langkahKerja}/detail', [TemplateLangkahKerjaDetailController::class, 'store'])->name('master.template.langkah-kerja.details.store');
    Route::post('/master/template/langkah-kerja/{langkahKerja}/detail/bulk-delete', [TemplateLangkahKerjaDetailController::class, 'bulkDestroy'])->name('master.template.langkah-kerja.details.bulk-destroy');
    Route::put('/master/template/langkah-kerja/{langkahKerja}/detail/{detail}', [TemplateLangkahKerjaDetailController::class, 'update'])->name('master.template.langkah-kerja.details.update');
    Route::delete('/master/template/langkah-kerja/{langkahKerja}/detail/{detail}', [TemplateLangkahKerjaDetailController::class, 'destroy'])->name('master.template.langkah-kerja.details.destroy');

    Route::get('/audit/sprin', [SprinController::class, 'index'])->name('audit.sprin.index');
    Route::post('/audit/sprin', [SprinController::class, 'store'])->name('audit.sprin.store');
    Route::get('/audit/sprin/{sprin}/reviews', [SprinController::class, 'getReviews'])->name('audit.sprin.reviews.index');
    Route::post('/audit/sprin/{sprin}/reviews', [SprinController::class, 'storeReview'])->name('audit.sprin.reviews.store');
    Route::put('/audit/sprin/{sprin}', [SprinController::class, 'update'])->name('audit.sprin.update');
    Route::delete('/audit/sprin/{sprin}', [SprinController::class, 'destroy'])->name('audit.sprin.destroy');

    Route::get('/audit/pka', [PkaController::class, 'index'])->name('audit.pka.index');
    Route::post('/audit/pka', [PkaController::class, 'store'])->name('audit.pka.store');
    Route::get('/audit/pka/{pka}/reviews/{type}', [PkaController::class, 'getReviews'])->name('audit.pka.reviews.index');
    Route::post('/audit/pka/{pka}/reviews/{type}', [PkaController::class, 'storeReview'])->name('audit.pka.reviews.store');
    Route::get('/audit/pka/{pka}/print', [PkaController::class, 'print'])->name('audit.pka.print');
    Route::get('/audit/pka/{pka}', [PkaController::class, 'show'])->name('audit.pka.show');
    Route::match(['put', 'post'], '/audit/pka/{pka}', [PkaController::class, 'update'])->name('audit.pka.update');
    Route::delete('/audit/pka/{pka}', [PkaController::class, 'destroy'])->name('audit.pka.destroy');
    Route::post('/audit/pka/{pka}/duplicate', [PkaController::class, 'duplicate'])->name('audit.pka.duplicate');
    Route::match(['patch', 'post'], '/audit/pka/{pka}/status', [PkaController::class, 'updateStatus'])->name('audit.pka.update-status');

    Route::get('/audit/pka/{pka}/langkah-kerja', [PkaLangkahKerjaController::class, 'index'])->name('audit.pka.langkah-kerja.index');
    Route::post('/audit/pka/{pka}/langkah-kerja', [PkaLangkahKerjaController::class, 'store'])->name('audit.pka.langkah-kerja.store');
    Route::post('/audit/pka/{pka}/langkah-kerja/apply-template', [PkaLangkahKerjaController::class, 'applyTemplate'])->name('audit.pka.langkah-kerja.apply-template');
    Route::post('/audit/pka/{pka}/langkah-kerja/bulk-delete', [PkaLangkahKerjaController::class, 'bulkDestroy'])->name('audit.pka.langkah-kerja.bulk-destroy');
    Route::delete('/audit/pka/{pka}/langkah-kerja/clear', [PkaLangkahKerjaController::class, 'clear'])->name('audit.pka.langkah-kerja.clear');
    Route::put('/audit/pka/{pka}/langkah-kerja/{langkahKerja}', [PkaLangkahKerjaController::class, 'update'])->name('audit.pka.langkah-kerja.update');
    Route::delete('/audit/pka/{pka}/langkah-kerja/{langkahKerja}', [PkaLangkahKerjaController::class, 'destroy'])->name('audit.pka.langkah-kerja.destroy');

    Route::get('/pelaksanaan/kka', [KkaController::class, 'index'])->name('pelaksanaan.kka.index');
    Route::post('/pelaksanaan/kka', [KkaController::class, 'store'])->name('pelaksanaan.kka.store');
    Route::get('/pelaksanaan/kka/{kka}/reviews/{type}', [KkaController::class, 'getReviews'])->name('pelaksanaan.kka.reviews.index');
    Route::post('/pelaksanaan/kka/{kka}/reviews/{type}', [KkaController::class, 'storeReview'])->name('pelaksanaan.kka.reviews.store');
    Route::get('/pelaksanaan/kka/{kka}', [KkaController::class, 'show'])->name('pelaksanaan.kka.show');
    Route::put('/pelaksanaan/kka/{kka}', [KkaController::class, 'update'])->name('pelaksanaan.kka.update');
    Route::delete('/pelaksanaan/kka/{kka}', [KkaController::class, 'destroy'])->name('pelaksanaan.kka.destroy');
    Route::post('/pelaksanaan/kka/{kka}/duplicate', [KkaController::class, 'duplicate'])->name('pelaksanaan.kka.duplicate');
    Route::match(['patch', 'post'], '/pelaksanaan/kka/{kka}/status', [KkaController::class, 'updateStatus'])->name('pelaksanaan.kka.update-status');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
