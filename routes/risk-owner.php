<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\RiskOwner\{
  PengukuranRisikoController,
  RiskController,
  RiskRegisterKorporasiController,
  PetaRisikoController,
};

use \App\Http\Controllers\{
  HomeController
};

Route::middleware(['auth', 'cekRiskOwner'])->name('risk-owner.')->group(function () {
  Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
  Route::get('pengukuran-risiko', [PengukuranRisikoController::class, 'index'])->name('pengukuran-risiko');
  Route::get('generate-pdf', [PengukuranRisikoController::class, 'generatePDF'])->name('pengukuran-generatePDF');
  Route::post('penilaian-risiko', [PengukuranRisikoController::class, 'penilaianRisiko'])->name('penilaian-risiko');
  Route::post('penilaian-risiko-store', [PengukuranRisikoController::class, 'penilaianRisikoStore'])->name('penilaian-risiko-store');
  Route::resource('risiko', RiskController::class);
  Route::get('risiko/toggle-korporasi/{id}', [RiskController::class, 'toggleKorporasi'])->name('toggleKorporasi');
  Route::get('risiko/approve/{id}', [RiskController::class, 'approve'])->name('risiko.approve');
  Route::get('risiko/print/{id}', [RiskController::class, 'print'])->name('risiko.print');
  Route::get('peta-risiko/{id}', [PetaRisikoController::class, 'show'])->name('peta-risiko');

  Route::resource('risk-register-korporasi', RiskRegisterKorporasiController::class);
  Route::post('risk-register-korporasi/import', [RiskRegisterKorporasiController::class, 'import'])->name('risk-detail.import');
  Route::post('upload-lampiran-risk-register-korporasi', [RiskRegisterKorporasiController::class, 'uploadLampiran'])->name('upload-lampiran-risk-register-korporasi');
  Route::get('print-risk-register-korporasi/{id}', [RiskRegisterKorporasiController::class, 'print'])->name('print-risk-register-korporasi');
});
