<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Admin\{
    UserController,
    DivisiController,
    HasilKompilasiRisikoController,
    KonteksController,
    RisikoController,
    SumberRisikoKorporasiController,
    RiskRegisterDivisiController,
    RiskRegisterKorporasiController,
    ApprovalHasilMitigasiController,
    MitigasiPlanController,
    MitigasiPlanKorporasiController,
    PetaRisikoController,
    PengajuanAdminController,
};

use \App\Http\Controllers\{
  HomeController
};

Route::middleware(['auth', 'cekAdmin'])->name('admin.')->group(function () {
  Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
//   Route::get('/notifications/mark-as-read', [NotificationsController::class, 'notifAdminMark'])->name('notifications.mark-as-read');
  Route::get('/user', [UserController::class, 'index'])->name('user');
  Route::get('/user/get-user/{id}', [UserController::class, 'get_user'])->name('user-get-user');
  Route::post('/user/store/{id?}', [UserController::class, 'store'])->name('user-store');
  Route::put('/user/update-status/{id}', [UserController::class, 'update_status'])->name('user-update-status');

  Route::get('/divisi', [DivisiController::class, 'index'])->name('divisi');
  Route::get('/divisi/get-divisi/{id}', [DivisiController::class, 'get_divisi'])->name('divisi-get-divisi');
  Route::post('/divisi/store/{id?}', [DivisiController::class, 'store'])->name('divisi-store');
  Route::post('/divisi/delete-divisi', [DivisiController::class, 'delete'])->name('divisi-delete');

  Route::get('/risiko', [RisikoController::class, 'index'])->name('risiko');
  Route::get('/risiko/get-risiko/{id?}', [RisikoController::class, 'get_risiko'])->name('risiko-get-risiko');
  Route::post('/risiko/store/{id?}', [RisikoController::class, 'store'])->name('risiko-store');
  Route::post('/risiko/delete-risiko', [RisikoController::class, 'delete'])->name('risiko-delete');

  Route::get('/hasil-kompilasi-risiko', [HasilKompilasiRisikoController::class, 'index'])->name('hasil-kompilasi-risiko');
  Route::post('/delete-responden/{id}', [HasilKompilasiRisikoController::class, 'delete_responden'])->name('delete-responden');
  Route::get('/responden_datatable', [HasilKompilasiRisikoController::class, 'responden_datatable']);
  Route::get('/sumber_risiko_datatable', [HasilKompilasiRisikoController::class, 'sumber_risiko_datatable']);
  Route::get('/print-kompilasi-hasil-mitigasi/{divisi?}/{tahun?}', [HasilKompilasiRisikoController::class, 'print_kompilasi_hasil_mitigasi']);

  Route::get('/konteks', [KonteksController::class, 'index'])->name('konteks');
  Route::get('/konteks/get-konteks/{id}', [KonteksController::class, 'get_konteks'])->name('konteks-get-konteks');
  Route::post('/konteks/store/{id?}', [KonteksController::class, 'store'])->name('konteks-store');
  Route::post('/konteks/delete-konteks', [KonteksController::class, 'delete'])->name('konteks-delete');
  Route::get('sumber-risiko-korporasi', [SumberRisikoKorporasiController::class, 'index'])->name('sumber-risiko-korporasi');
  Route::post('sumber-risiko-korporasi/store', [SumberRisikoKorporasiController::class, 'store'])->name('sumber-risiko-korporasi.store');
  Route::put('sumber-risiko-korporasi/update/{id}', [SumberRisikoKorporasiController::class, 'update'])->name('sumber-risiko-korporasi.update');
  Route::delete('sumber-risiko-korporasi/destroy/{id}', [SumberRisikoKorporasiController::class, 'destroy'])->name('sumber-risiko-korporasi.destroy');
  Route::get('sumber-risiko-korporasi/search', [SumberRisikoKorporasiController::class, 'searchRisiko'])->name('search-risiko');
  Route::post('sumber-risiko-korporasi/approval/{id}', [SumberRisikoKorporasiController::class, 'approvalRisiko'])->name('approval-risiko');

  Route::get('risk-register-divisi', [RiskRegisterDivisiController::class, 'index'])->name('risk-register-divisi');
  Route::get('search-risk-header', [RiskRegisterDivisiController::class, 'searchRiskHeader'])->name('search-risk-header');
  Route::get('all-risk-header', [RiskRegisterDivisiController::class, 'allRiskHeader'])->name('all-risk-header');
  Route::get('detail-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'show'])->name('detail-risk-register');
  Route::post('risk-register-divisi/set-urut-risk', [RiskRegisterDivisiController::class, 'setUrutRisk'])->name('risk-register-divisi.set-urut-risk');
  Route::get('print-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'print'])->name('print-risk-register');
  Route::post('approval-risk-register-divisi/{id}', [RiskRegisterDivisiController::class, 'approval'])->name('approval-risk-register');
  Route::get('risk-detail-korporasi/{id}', [RiskRegisterDivisiController::class, 'korporasi'])->name('risk-register-divisi.korporasi');
  Route::get('risk-detail-non-korporasi/{id}', [RiskRegisterDivisiController::class, 'nonKorporasi'])->name('risk-register-divisi.non-korporasi');
  Route::post('risk-detail-mitigation/{id}', [RiskRegisterDivisiController::class, 'mitigation'])->name('mitigation');
  Route::post('risk-detail-not-mitigation/{id}', [RiskRegisterDivisiController::class, 'notMitigation'])->name('not-mitigation');
  Route::delete('risk-detail-delete/{id}', [RiskRegisterDivisiController::class, 'deleteRiskDetail'])->name('risk-detail-delete');
  Route::get('risk-register-divisi/approve/{id}', [RiskRegisterDivisiController::class, 'approve'])->name('risk-register-divisi.approve');

  Route::resource('risk-register-korporasi', RiskRegisterKorporasiController::class);
  Route::post('risk-register-korporasi/set-urut-risk', [RiskRegisterKorporasiController::class, 'setUrutRisk'])->name('risk-register-korporasi.set-urut-risk');
  Route::post('risk-register-korporasi/import', [RiskRegisterKorporasiController::class, 'import'])->name('risk-detail.import');
  Route::post('detail-risk-register-korporasi/store', [RiskRegisterKorporasiController::class, 'storeDetail'])->name('risk-detail.store');
  Route::post('upload-lampiran-risk-register-korporasi', [RiskRegisterKorporasiController::class, 'uploadLampiran'])->name('upload-lampiran-risk-register-korporasi');
  Route::get('print-risk-register-korporasi/{id}', [RiskRegisterKorporasiController::class, 'print'])->name('print-risk-register-korporasi');
  Route::post('approval-risk-register-korporasi/{id}', [RiskRegisterKorporasiController::class, 'approval'])->name('approval-risk-register-korporasi');

  Route::put('updateDetail/{id}', [RiskRegisterKorporasiController::class, 'updateDetail'])->name('risk-detail.update');
  Route::delete('destroyDetail/{id}', [RiskRegisterKorporasiController::class, 'destroyDetail'])->name('risk-detail.destroy');

  Route::post('fetchNilaiRisiko', [RiskRegisterKorporasiController::class, 'getNilai']);
  Route::post('getRisikoSelected', [RiskRegisterKorporasiController::class, 'getRisikoSelected']);

  Route::get('approval-mitigasi/{id}', [ApprovalHasilMitigasiController::class, 'progressMitigasi']);
  Route::put('approval-hasil-mitigasi/persetujuan-mitigasi/{id}', [ApprovalHasilMitigasiController::class, 'approvalHasilMitigasi']);
  Route::put('approval-hasil-mitigasi/approve/{id}', [ApprovalHasilMitigasiController::class, 'approvedHasilMitigasi']);
  Route::put('approval-hasil-mitigasi/not-approve/{id}', [ApprovalHasilMitigasiController::class, 'notApprovedHasilMitigasi']);
  Route::resource('approval-hasil-mitigasi', ApprovalHasilMitigasiController::class);
  Route::resource('mitigasi-plan', MitigasiPlanController::class);
  Route::resource('mitigasi-plan-korporasi', MitigasiPlanKorporasiController::class);
  Route::post('getProgressKorporasi', [MitigasiPlanKorporasiController::class, 'getProgressData']);
  Route::post('storeProgressKorporasi', [MitigasiPlanKorporasiController::class, 'insertProgress'])->name('storeProgressKorporasi');
  Route::post('getProgress', [MitigasiPlanController::class, 'getProgressData']);
  Route::post('storeProgress', [MitigasiPlanController::class, 'insertProgress'])->name('storeProgress');
  Route::get('peta-risiko/{id}', [PetaRisikoController::class, 'show'])->name('peta-risiko');

  Route::resource('pengajuan-admin', PengajuanAdminController::class);
});

Route::middleware(['cekAdmin'])->name('admin.')->group(function () {
  Route::get('mitigasi-plan/print/{id}', [MitigasiPlanController::class, 'print'])->name('mitigasi-plan.print');
  Route::get('mitigasi-plan-korporasi/print/{id}', [MitigasiPlanKorporasiController::class, 'print'])->name('mitigasi-plan-korporasi.print');
});

