<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengukuran;
use App\Models\Divisi;
use App\Models\Responden;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use PDF;

class HasilKompilasiRisikoController extends Controller
{
    public function index()
    {
        $divisi = Divisi::where('divisi_code', '!=', 'INHAN')->get();

        return view('admin.hasil_kompilasi_risiko', compact('divisi'));
    }

    public function responden_datatable(Request $request)
    {
        $wr = "1=1";
        if($request->filled('divisi_id')){
            $wr .= " AND du.divisi_id = ".$request->divisi_id;
        }
        if($request->filled('tahun')){
            $wr .= " AND p.tahun_p = ".$request->tahun;
        }
        $data = DB::table('pengukuran as p')
        ->join('s_risiko as sr', 'sr.id_s_risiko', 'p.id_s_risiko')
        ->leftJoin('defendid_user as du', 'du.id_user', 'sr.id_user')
        ->whereRaw($wr)
        ->whereNull('p.deleted_at')
        ->get();

        return DataTables::of($data)->make(true);
    }

    public function sumber_risiko_datatable(Request $request)
    {
        $wr = "1=1";
        if($request->filled('divisi_id')){
            $wr .= " AND du.divisi_id = ".$request->divisi_id;
        }
        if($request->filled('tahun')){
            $wr .= " AND C.tahun = ".$request->tahun;
        }
        $data = DB::table('pengukuran as A')
        ->selectRaw('p.divisi_id, D.id_risk, D.konteks, B.s_risiko, B.status_s_risiko, ROUND(AVG(A.nilai_L),2) AS l, ROUND(AVG(A.nilai_C),2) AS c, ROUND((AVG(A.nilai_L)*AVG(A.nilai_C)),2) AS r, count(A.nama_responden), p.instansi, C.tahun')
        ->rightJoin('s_risiko as B', 'A.id_s_risiko', 'B.id_s_risiko')
        ->join('risk_detail as rd', 'rd.id_s_risiko', 'B.id_s_risiko')
        ->join('risk_header as C', 'rd.id_riskh', 'C.id_riskh')
        ->leftJoin('konteks as D', 'B.id_konteks', 'D.id_konteks')
        ->leftJoin('defendid_user as du', 'du.id_user', 'C.id_user')
        ->leftJoin('divisi as p', 'p.divisi_id', 'du.divisi_id')
        ->whereRaw($wr)
        ->whereNull('A.deleted_at')
        ->groupBy('B.id_s_risiko')
        ->whereNull('rd.deleted_at')
        ->whereNull('C.deleted_at')
        ->get();

        return DataTables::of($data)->make(true);
    }

    public function delete_responden($id)
    {
        Pengukuran::where('id_p', $id)->delete();

        return back()->with(['success-swal' => 'Responden berhasil dihapus!']);
    }

    public function print_kompilasi_hasil_mitigasi($instansi = null, $tahun = null)
    {
        $wr = "1=1";
        if($instansi){
            $wr .= " AND du.divisi_id = ".$instansi;
            // $instansi = Divisi::select('instansi')->where('divisi_id', $instansi)->first();
        }
        if($tahun){
            $wr .= " AND B.tahun = ".$tahun;
        }
        $data = Pengukuran::select('k.id_risk', 'k.konteks', 'sr.s_risiko', 'p.*', 'pengukuran.tahun_p', DB::raw('AVG(pengukuran.nilai_L) as L'), DB::raw('AVG(pengukuran.nilai_C) as C'), DB::raw('AVG(pengukuran.nilai_L) * AVG(pengukuran.nilai_C) as R'), DB::raw('count(pengukuran.nama_responden)'))
            ->join('s_risiko as sr', 'pengukuran.id_s_risiko', 'sr.id_s_risiko')
            ->join('konteks as k', 'sr.id_konteks', 'k.id_konteks')
            ->join('defendid_pengukur as d', 'pengukuran.id_pengukur', 'd.id_pengukur')
            ->join('divisi as p', 'd.divisi_id', 'p.divisi_id')
            ->where('pengukuran.tahun_p', $tahun)
            ->where('sr.status_s_risiko', '1')
            ->where('sr.divisi_id', $instansi)
            ->groupBy('k.id_risk', 'k.konteks',  'sr.s_risiko', 'sr.id_s_risiko')
            ->get();
        $pdf = PDF::loadView('penilai-indhan.form_kompilasi', compact('data'))->setPaper( 'a4','landscape');
        return $pdf->stream('Hasil Kompilasi Risiko'.$instansi.'_'.$tahun.'.pdf');
    }
}
