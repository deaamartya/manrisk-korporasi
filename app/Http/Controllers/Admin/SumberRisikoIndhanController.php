<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DefendidUser;
use Illuminate\Http\Request;
use App\Models\SRisiko;
use App\Models\Risk;
use App\Models\PengukuranIndhan;
use App\Models\Pengukuran;
use App\Models\RiskDetail;
use App\Models\Divisi;
use Auth;
use Illuminate\Support\Facades\Redirect;

class SumberRisikoIndhanController extends Controller
{
    public function index()
    {
    //   $divisi = DefendidUser::join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')->where('is_admin', 0)->groupBy('defendid_user.divisi_id')->orderBy('divisi_code')->get();
        $divisi = Divisi::orderBy('divisi_id')->get();
        $sumber_risiko = SRisiko::join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
                    ->join('defendid_user', 's_risiko.id_user', 'defendid_user.id_user')
                    ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                    ->where('s_risiko.status_s_risiko', 0)
                    ->whereNull('s_risiko.deleted_at')
                    ->orderBy('s_risiko.id_s_risiko')
                    ->get();
        $divisi_filter = null;
        $tahun_filter = null;
        $risiko = Risk::join('konteks', 'risk.id_risk', 'konteks.id_risk')
        ->orderBy('risk.id_risk')
        ->get();
        return view('admin.sumber-risiko-indhan', compact('divisi','sumber_risiko', 'divisi_filter', 'risiko', 'tahun_filter'));
    }
    
    public function store(Request $request) {
        $request->validate([
          'tahun' => 'required',
          'id_konteks' => 'required',
          's_risiko' => 'required',
        ]);
  
        SRisiko::insert([
          's_risiko' => $request->s_risiko,
          'divisi_id' => Auth::user()->divisi_id,
          'id_konteks' => $request->id_konteks,
          'id_user' => Auth::user()->id_user,
          'tahun' => $request->tahun,
          'status_s_risiko' => 1,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
  
        return Redirect::back()->with('created-alert', 'Data sumber risiko INDHAN berhasil disimpan.');

    }

    public function update(Request $request, $id)
    {
      $request->validate([
        'id_konteks' => 'required',
        's_risiko' => 'required',
      ]);

      SRisiko::find($id)->update([
        's_risiko' => $request->s_risiko,
        'id_konteks' => $request->id_konteks,
        'updated_at' => now(),
      ]);

      return Redirect::back()->with('updated-alert', 'Data sumber risiko INDHAN berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $count = PengukuranIndhan::where('id_s_risiko', '=', $id)->count('id_s_risiko');
      $count += Pengukuran::where('id_s_risiko', '=', $id)->count('id_s_risiko');
      $count += RiskDetail::where('id_s_risiko', '=', $id)->count('id_s_risiko');
      if ($count > 0) {
        return back()->with(["error-swal" => 'Data ini masih digunakan pada detail risiko, pengukuran dan atau pengukuran Indhan. Mohon hapus data yang menggunakan sumber risiko ini terlebih dahulu.']);
      }
      SRisiko::find($id)->delete();
      return Redirect::back()->with('deleted-alert', 'Data sumber risiko INDHAN telah dihapus.');
    }

    public function searchRisiko(Request $request)
    {
        $request->validate([
        'divisi_id' => 'required',
        'tahun' => 'required',
        ]);
        $sumber_risiko = SRisiko::join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
                    ->join('defendid_user', 's_risiko.id_user', 'defendid_user.id_user')
                    ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                    ->where('s_risiko.divisi_id',  $request->divisi_id) // divisi yg dipilih
                    ->where('s_risiko.tahun', $request->tahun)
                    ->whereNull('s_risiko.deleted_at')
                    ->orderBy('s_risiko.id_s_risiko')
                    ->get();
        // dd($sumber_risiko);
        $divisi_filter = $request->divisi_id;
        $tahun_filter = $request->tahun;
        // $divisi = DefendidUser::join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')->where('is_admin', 0)->groupBy('defendid_user.divisi_id')->orderBy('divisi_code')->get();
        $divisi = Divisi::orderBy('divisi_id')->get();
        $risiko = Risk::join('konteks', 'risk.id_risk', 'konteks.id_risk')
        ->orderBy('risk.id_risk')
        ->get();
        return view('admin.sumber-risiko-indhan', compact('divisi','sumber_risiko', 'divisi_filter', 'risiko', 'tahun_filter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approvalRisiko(Request $request, $id) {
        $request->validate([
            'status_verifikasi' => 'required',
        ]);

        SRisiko::find($id)->update([
            'status_s_risiko' => $request->status_verifikasi,
            'catatan' => $request->catatan,
        ]);

        // return redirect()->route('admin.sumber-risiko-indhan')->with('updated-alert', 'Status verifikasi sumber risiko berhasil diubah.');

        return Redirect::back()->with('updated-alert', 'Status verifikasi sumber risiko berhasil diubah.');

        
    }
}
