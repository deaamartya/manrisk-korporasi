<?php

namespace App\Http\Controllers\RiskOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RiskHeader;
use App\Models\DefendidUser;
use App\Models\RiskHeaderKorporasi;
use App\Models\RiskDetail;
use App\Models\SRisiko;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Auth;
use PDF;
use Redirect;
use DNS2D;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Route;
use App\Models\ShortUrl;
use Illuminate\Support\Str;
use App\Models\PengukuranKorporasi;

class RiskRegisterKorporasiController extends Controller
{
    public function index()
    {
        $headers = RiskHeaderKorporasi::all();
        // $jml_risk = RiskDetail::join('risk_header', 'risk_header.id_riskh', 'risk_detail.id_riskh')
        // ->where('risk_header.tahun', '=', date('Y'))
        // ->where('status_korporasi', '=', 1)
        // ->count();

        $jml_risk = [];
        foreach($headers as $h) {
            $jml_risk[] = RiskDetail::where('tahun', '=', $h->tahun)
                ->where('status_korporasi', '=', 1)
                ->count();
        }
        // dd($jml_risk);
        return view('risk-owner.risk-register-korporasi', compact('headers', 'jml_risk'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        RiskHeaderKorporasi::insert([
            'tahun' => $request->tahun,
            'target' => $request->target,
            'penyusun' => $request->penyusun,
            'pemeriksa' => $request->pemeriksa,
        ]);
        return redirect()->route('risk-owner.risk-register-korporasi.index')->with(['success-swal' => 'Risk Header KORPORASI berhasil disimpan!']);
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
        $riskheader = RiskHeaderKorporasi::where('id_riskh', '=', $id)->first();
        $riskheader->update([
            'tahun' => $request->tahun,
            'target' => $request->target,
            'penyusun' => $request->penyusun,
            'pemeriksa' => $request->pemeriksa,
        ]);
        return redirect()->route('risk-owner.risk-register-korporasi.index')->with(['success-swal' => 'Risk Header KORPORASI berhasil diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        RiskHeaderKorporasi::destroy($id);
        return redirect()->route('risk-owner.risk-register-korporasi.index')->with(['success-swal' => 'Risk Header KORPORASI berhasil dihapus!']);
    }


    public function show($id)
    {
        $headers = RiskHeaderKorporasi::where('id_riskh', '=', $id)->first();
        // dd($headers);
        $detail_risk = RiskHeader::selectRaw('*,avg(pi.nilai_L) as avg_nilai_l, avg(pi.nilai_C) as avg_nilai_c')
                ->join('divisi', 'risk_header.divisi_id', 'divisi.divisi_id')
                ->join('risk_detail', 'risk_header.id_riskh', 'risk_detail.id_riskh' )
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
                ->leftJoin('pengukuran_korporasi as pi', 'pi.id_s_risiko', 's_risiko.id_s_risiko')
                ->where('risk_detail.status_korporasi', '=', 1)
                ->where('risk_detail.divisi_id', '!=', 6)
                ->whereNull('risk_detail.deleted_at')
                ->where('risk_header.tahun', '=', $headers->tahun)
                ->whereNull('risk_header.deleted_at')
                ->groupBy('id_riskd')
                ->get();
        $detail_risk_korporasi = RiskDetail::selectRaw('*,avg(pi.nilai_L) as avg_nilai_l, avg(pi.nilai_C) as avg_nilai_c')
                ->join('divisi as p', 'p.divisi_id', '=', 'risk_detail.divisi_id')
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks' )
                ->leftJoin('pengukuran_korporasi as pi', 'pi.id_s_risiko', 's_risiko.id_s_risiko')
                ->where('risk_detail.status_korporasi', '=', 1)
                ->where('risk_detail.divisi_id', '=', 6)
                ->whereNull('risk_detail.deleted_at')
                ->where('risk_detail.tahun', '=', $headers->tahun)
                ->groupBy('id_riskd')
                ->get();
        return view('risk-owner.detail-risk-register-korporasi', compact('headers', 'detail_risk', 'detail_risk_korporasi'));
    }

    public function uploadLampiran(Request $request) {
        $id = $request->id;
        $riskheader = RiskHeaderKorporasi::where('id_riskh', '=', $id)->first();
        $filename = $request->file('lampiran')->getClientOriginalName();
        $folder = '/document/lampiran/';
        $request->file('lampiran')->storeAs($folder, $filename, 'public');
        $riskheader->update([
            'lampiran' => $filename,
        ]);
        return redirect()->route('risk-owner.risk-register-korporasi.show', $id)->with(['success-swal' => 'Lampiran berhasil diupload!']);
    }

    public function print($id) {
        $header = RiskHeaderKorporasi::where('id_riskh', '=', $id)->first();
        $detail_risk = RiskDetail::selectRaw("risk_detail.*, s_risiko.*, konteks.*, risk.*, CONCAT(konteks.id_risk, '-', konteks.no_k) AS risk_code, (SELECT 0) as avg_nilai_l, (SELECT 0) as avg_nilai_c")
                ->join('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko' )
                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks' )
                ->join('risk', 'konteks.id_risk', 'risk.id_risk' )
                ->where('risk_detail.status_korporasi', '=', 1)
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
                $temp_pi = PengukuranKorporasi::where('id_s_risiko', '=', $value->id_s_risiko)->selectRaw('avg(nilai_L) as avg_nilai_l, avg(nilai_C) as avg_nilai_c')->first();
                $detail_risk[$key]->avg_nilai_l = number_format($temp_pi->avg_nilai_l, 2) + 0;
                $detail_risk[$key]->avg_nilai_c = number_format($temp_pi->avg_nilai_c, 2) + 0;
            }
        }
        $document_type = 'risk_register_korporasi_ro';
        $url = "url='risk-owner/print-risk-register-korporasi/".$header->id_riskh."';".
            "signed_by=".($header->pemeriksa ? $header->pemeriksa : '-').";".
            "divisi=".'Industri Pertahanan'.";".
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
        $encrypted = url('document/verify/').'/'.$short_url->short_code;
        $qrcode = DNS2D::getBarcodePNG($encrypted, 'QRCODE');
        $pdf = PDF::loadView('risk-owner.pdf-risk-register-korporasi',  compact('header', 'detail_risk', 'qrcode'))->setPaper('a4', 'landscape');
        // return $pdf->stream('Laporan Manajemen Risiko '.$user->divisi.' Tahun '.$header->tahun.'.pdf');
        return $pdf->stream('Laporan Manajemen Risiko KORPORASI Tahun '.$header->tahun.'.pdf');
    }
}
