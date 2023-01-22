<?php

namespace App\Http\Controllers\PenilaiIndhan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RiskHeader;
use App\Models\DefendidUser;
use App\Models\RiskHeaderIndhan;
use App\Models\RiskDetail;
use App\Models\SRisiko;
use App\Models\PengukuranIndhan;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Auth;
use PDF;
use Redirect;
use Illuminate\Support\Facades\Crypt;
use DNS2D;
use Session;
use Illuminate\Support\Facades\Route;
use App\Models\ShortUrl;
use Illuminate\Support\Str;

class RiskRegisterIndhanController extends Controller
{
    public function index()
    {
        $headers = RiskHeaderIndhan::all();
        // $jml_risk = RiskDetail::join('risk_header', 'risk_header.id_riskh', 'risk_detail.id_riskh')
        // ->where('risk_header.tahun', '=', date('Y'))
        // ->where('status_indhan', '=', 1)
        // ->count();
        $jml_risk = [];
        foreach($headers as $h) {
            $jml_risk[] = RiskDetail::where('tahun', '=', $h->tahun)
                ->where('status_indhan', '=', 1)
                ->count();
        }
        return view('penilai-indhan.risk-register-indhan', compact('headers', 'jml_risk'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        RiskHeaderIndhan::insert([
            'tahun' => $request->tahun,
            'target' => $request->target,
            'penyusun' => Auth::user()->name,
        ]);
        return redirect()->route('penilai-indhan.risk-register-indhan.index')->with(['success-swal' => 'Risk Header INDHAN berhasil disimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $riskheader = RiskHeaderIndhan::where('id_riskh', '=', $id)->first();
        $riskheader->update([
            'tahun' => $request->tahun,
            'target' => $request->target,
            'pemeriksa' => $request->pemeriksa
        ]);
        return redirect()->route('penilai-indhan.risk-register-indhan.index')->with(['success-swal' => 'Risk Header INDHAN berhasil diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        RiskHeaderIndhan::destroy($id);
        return redirect()->route('penilai-indhan.risk-register-indhan.index')->with(['success-swal' => 'Risk Header INDHAN berhasil dihapus!']);
    }


    public function show($id)
    {
        $headers = RiskHeaderIndhan::where('id_riskh', '=', $id)->first();
        // dd($headers);
        $detail_risk = RiskHeader::selectRaw('*,avg(pi.nilai_L) as avg_nilai_l, avg(pi.nilai_C) as avg_nilai_c')
                ->join('divisi', 'risk_header.divisi_id', 'divisi.divisi_id')
                ->join('risk_detail', 'risk_header.id_riskh', 'risk_detail.id_riskh' )
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
                ->leftJoin('pengukuran_indhan as pi', 'pi.id_s_risiko', 's_risiko.id_s_risiko')
                ->where('risk_detail.status_indhan', '=', 1)
                ->where('risk_detail.divisi_id', '!=', 6)
                ->whereNull('risk_detail.deleted_at')
                ->where('risk_header.tahun', '=', $headers->tahun)
                ->whereNull('risk_header.deleted_at')
                ->groupBy('id_riskd')
                ->get();
        $detail_risk_indhan = RiskDetail::selectRaw('*,avg(pi.nilai_L) as avg_nilai_l, avg(pi.nilai_C) as avg_nilai_c')
                ->join('divisi as p', 'p.divisi_id', '=', 'risk_detail.divisi_id')
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks' )
                ->leftJoin('pengukuran_indhan as pi', 'pi.id_s_risiko', 's_risiko.id_s_risiko')
                ->where('risk_detail.status_indhan', '=', 1)
                ->where('risk_detail.divisi_id', '=', 6)
                ->whereNull('risk_detail.deleted_at')
                ->where('risk_detail.tahun', '=', $headers->tahun)
                ->groupBy('id_riskd')
                ->get();
        return view('penilai-indhan.detail-risk-register-indhan', compact('headers', 'detail_risk', 'detail_risk_indhan'));
    }

    public function uploadLampiran(Request $request) {
        $id = $request->id;
        $riskheader = RiskHeaderIndhan::where('id_riskh', '=', $id)->first();
        $filename = $request->file('lampiran')->getClientOriginalName();
        $folder = '/document/lampiran/';
        $request->file('lampiran')->storeAs($folder, $filename, 'public');
        $riskheader->update([
            'lampiran' => $filename,
        ]);
        return redirect()->route('penilai-indhan.risk-register-indhan.show', $id)->with(['success-swal' => 'Lampiran berhasil diupload!']);
    }

    public function print($id) {
        $header = RiskHeaderIndhan::where('id_riskh', '=', $id)->first();
        $document_type = 'risk_register_indhan_penilai_indhan';
        $url = "url='penilai-indhan/print-risk-register-indhan/".$header->id_riskh."';".
            "signed_by=".($header->pemeriksa ? $header->pemeriksa : '-').";".
            "instansi=".'Industri Pertahanan'.";".
            "tahun=".$header->tahun.";".
            "created_at=".$header->created_at.";".
            "penyusun=".($header->penyusun ? $header->penyusun : '-').";";
        $short_url = ShortUrl::where(
            [
                'jenis_dokumen' => $document_type,
                'id_dokumen' => $id,
            ],
        )->first();
        if ($short_url) {
            $short_url->update([
                'url' => $url,
            ]);
        }
        $short_url = ShortUrl::firstOrCreate(
            [
                'jenis_dokumen' => $document_type,
                'id_dokumen' => $id,
            ],
            [
                'jenis_dokumen' => $document_type,
                'id_dokumen' => $id,
                'url' => $url,
                'short_code' => Str::random(10),
            ],
        );
        $detail_risk = RiskDetail::selectRaw("risk_detail.*, s_risiko.*, konteks.*, risk.*, CONCAT(konteks.id_risk, '-', konteks.no_k) AS risk_code, (SELECT 0) as avg_nilai_l, (SELECT 0) as avg_nilai_c")
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks' )
                ->join('risk', 'konteks.id_risk', 'risk.id_risk' )
                ->where('risk_detail.status_indhan', '=', 1)
                ->whereNull('risk_detail.deleted_at')
                ->where('risk_detail.tahun', '=', $header->tahun)
                ->orderBy('konteks.id_risk', 'ASC')
                ->orderBy('konteks.no_k', 'ASC')
                ->get();
        foreach ($detail_risk as $key => $value) {
            if ($detail_risk[$key]->divisi_id === 6) {
                $detail_risk[$key]->avg_nilai_l = $detail_risk[$key]->l_awal;
                $detail_risk[$key]->avg_nilai_c = $detail_risk[$key]->c_awal;
            } else {
                $temp_pi = PengukuranIndhan::where('id_s_risiko', '=', $value->id_s_risiko)->selectRaw('avg(nilai_L) as avg_nilai_l, avg(nilai_C) as avg_nilai_c')->first();
                $detail_risk[$key]->avg_nilai_l = number_format($temp_pi->avg_nilai_l, 2) + 0;
                $detail_risk[$key]->avg_nilai_c = number_format($temp_pi->avg_nilai_c, 2) + 0;
            }
        }
        $user = DefendidUser::where('id_user', '=', $header->id_user)->first();
        $encrypted = url('document/verify/').'/'.$short_url->short_code;
        $qrcode = DNS2D::getBarcodePNG($encrypted, 'QRCODE');
        $pdf = PDF::loadView('penilai-indhan.pdf-risk-register-indhan', compact('header', 'user', 'qrcode', 'detail_risk'))->setPaper('a4', 'landscape');
        Session::forget('is_bypass');
        return $pdf->stream('Laporan Manajemen Risiko INDHAN Tahun '.$header->tahun.'.pdf');
    }

    public function approval($id)
    {
        $risk_header = RiskHeaderIndhan::where('id_riskh', '=', $id)->first();
        $risk_header->update([
            'status_h' => 1
        ]);
        // dd($risk_header);
        return Redirect::back()->with(['success-swal' => 'Risk Header INDHAN berhasil disetujui.']);
    }
}
