<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\PenilaiKorporasi\{
  PengukuranRisikoKorporasiController,
  RiskRegisterDivisiController,
  RiskRegisterKorporasiController,
  PetaRisikoController,
};

use \App\Http\Controllers\{
  HomeController
};

Route::name('penilai-korporasi.')->group(function () {
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('pengukuran-risiko-korporasi', [PengukuranRisikoKorporasiController::class, 'index'])->name('pengukuran-risiko-korporasi');
    Route::post('penilaian-risiko-korporasi', [PengukuranRisikokorporasiController::class, 'penilaianRisiko'])->name('penilaian-risiko-korporasi');
    Route::post('penilaian-risiko-korporasi-store', [PengukuranRisikoKorporasiController::class, 'penilaianRisikoStore'])->name('penilaian-risiko-korporasi-store');

    Route::get('risk-register-divisi', [RiskRegisterDivisiController::class, 'index'])->name('risk-register-divisi');
    Route::get('search-risk-header', [RiskRegisterDivisiController::class, 'searchRiskHeader'])->name('search-risk-header');
    Route::get('all-risk-header', [RiskRegisterDivisiController::class, 'allRiskHeader'])->name('all-risk-header');
    Route::get('detail-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'show'])->name('detail-risk-register');
    Route::get('print-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'print'])->name('print-risk-register');
    Route::post('approval-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'approval'])->name('approval-risk-register');
    Route::post('risk-detail-korporate/{id}', [RiskRegisterDivisiController::class, 'korporate'])->name('korporate');
    Route::post('risk-detail-unkorporate/{id}', [RiskRegisterDivisiController::class, 'unKorporate'])->name('unkorporate');
    Route::post('risk-detail-mitigation/{id}', [RiskRegisterDivisiController::class, 'mitigation'])->name('mitigation');
    Route::post('risk-detail-not-mitigation/{id}', [RiskRegisterDivisiController::class, 'notMitigation'])->name('not-mitigation');
    Route::delete('risk-detail-delete/{id}', [RiskRegisterDivisiController::class, 'deleteRiskDetail'])->name('risk-detail-delete');

    Route::get('peta-risiko/{id}', [PetaRisikoController::class, 'show'])->name('peta-risiko');

    Route::resource('risk-register-korporasi', RiskRegisterKorporasiController::class);
    Route::post('risk-register-korporasi/import', [RiskRegisterKorporasiController::class, 'import'])->name('risk-detail.import');
    Route::post('upload-lampiran-risk-register-korporasi', [RiskRegisterKorporasiController::class, 'uploadLampiran'])->name('upload-lampiran-risk-register-korporasi');
    Route::get('print-risk-register-korporasi/{id}', [RiskRegisterKorporasiController::class, 'print'])->name('print-risk-register-korporasi');
});
