<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\PenilaiIndhan\{
  PengukuranRisikoIndhanController,
  RiskRegisterDivisiController,
  RiskRegisterIndhanController,
  PetaRisikoController,
};

use \App\Http\Controllers\{
  HomeController
};

Route::name('penilai-indhan.')->group(function () {
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('pengukuran-risiko-indhan', [PengukuranRisikoIndhanController::class, 'index'])->name('pengukuran-risiko-indhan');
    Route::post('penilaian-risiko-indhan', [PengukuranRisikoindhanController::class, 'penilaianRisiko'])->name('penilaian-risiko-indhan');
    Route::post('penilaian-risiko-indhan-store', [PengukuranRisikoIndhanController::class, 'penilaianRisikoStore'])->name('penilaian-risiko-indhan-store');

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

    Route::resource('risk-register-indhan', RiskRegisterIndhanController::class);
    Route::post('risk-register-indhan/import', [RiskRegisterIndhanController::class, 'import'])->name('risk-detail.import');
    Route::post('upload-lampiran-risk-register-indhan', [RiskRegisterIndhanController::class, 'uploadLampiran'])->name('upload-lampiran-risk-register-indhan');
    Route::get('print-risk-register-indhan/{id}', [RiskRegisterIndhanController::class, 'print'])->name('print-risk-register-indhan');
});
