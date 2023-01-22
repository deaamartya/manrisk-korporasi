<?php

namespace App\Http\Controllers\PenilaiKorporasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\RiskHeader;
use App\Models\RiskDetail;
use App\Models\RiskHeaderKorporasi;
use App\Models\DefendidUser;
use App\Models\SRisiko;
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

class RiskRegisterDivisiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_headers= RiskHeader::join('defendid_user', 'risk_header.id_user', 'defendid_user.id_user')
                        ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                        ->whereNull('risk_header.deleted_at')
                        ->orderBy('risk_header.id_riskh')
                        ->get();
        $tahun = RiskHeader::select('tahun')->orderBy('tahun')->distinct()->get();
        $tahun_filter = null;
        return view('penilai-korporasi.risk-register-divisi', compact('data_headers', 'tahun', 'tahun_filter'));
    }

    public function allRiskHeader()
    {
        $data_headers= RiskHeader::join('defendid_user', 'risk_header.id_user', 'defendid_user.id_user')
                        ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                        ->orderBy('risk_header.id_riskh')
                        ->get();
        $tahun = RiskHeader::select('tahun')->orderBy('tahun')->distinct()->get();
        $tahun_filter = null;
        return view('penilai-korporasi.risk-register-divisi', compact('data_headers', 'tahun', 'tahun_filter'));
    }

    public function searchRiskHeader(Request $request)
    {
        $data_headers= RiskHeader::join('defendid_user', 'risk_header.id_user', 'defendid_user.id_user')
                    ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                    ->where('risk_header.tahun', $request->tahun)
                    ->orderBy('risk_header.id_riskh')
                    ->get();
        $tahun = RiskHeader::select('tahun')->orderBy('tahun')->distinct()->get();
        $tahun_filter = $request->tahun;
        return view('penilai-korporasi.risk-register-divisi', compact('data_headers', 'tahun', 'tahun_filter'));
    }

    public function print($id) {
        $header = RiskHeader::where('id_riskh', '=', $id)->first();
        $user = DefendidUser::where('id_user', '=', $header->id_user)->first();
        $document_type = 'risk_register_penilai_korporasi';
        $url = "url='penilai-korporasi/print-risk-register-divisi/".$header->id_riskh."';".
            "signed_by=".($header->pemeriksa ? $header->pemeriksa->name : '-').";".
            "instansi=".$header->divisi->instansi.";".
            "tahun=".$header->tahun.";".
            "created_at=".$header->created_at.";".
            "penyusun=".($header->penyusun ? $header->penyusun->name : '-').";";
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
        $pdf = PDF::loadView('penilai-korporasi.pdf-risk-register', compact('header', 'user', 'qrcode'))->setPaper('a4', 'landscape');
        Session::forget('is_bypass');
        return $pdf->stream('Laporan Manajemen Risiko '.$header->instansi.' Tahun '.$header->tahun.'.pdf');
    }

    public function approval($id)
    {
        $risk_header = RiskHeader::where('id_riskh', '=', $id)->first();
        $risk_header->update([
            'status_h_korporasi' => 1
        ]);
        // dd($risk_header);
        return Redirect::back()->with(['success-swal' => 'Risk Header berhasil disetujui.']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $headers = RiskHeader::join('defendid_user', 'risk_header.id_user', 'defendid_user.id_user')
                    ->join('divisi', 'defendid_user.divisi_id', 'divisi.divisi_id')
                    ->where('id_riskh', '=', $id)->first();
        // dd($headers_korporasi);
        return view('penilai-korporasi.detail-risk-register', compact('headers'));
    }

    public function korporate($id, Request $request)
    {
        $risk_detail = RiskDetail::where('id_riskd', '=', $id)->first();
        $risk_detail->update([
            'status_korporasi' => 1
        ]);
        $id_risk = $request->id_risk;
        return Redirect::back()->with(['success-swal' => 'Data '.$id_risk.' berhasil diubah menjadi KORPORASI.']);
    }

    public function unKorporate($id, Request $request)
    {
        $risk_detail = RiskDetail::where('id_riskd', '=', $id)->first();
        $risk_detail->update([
            'status_korporasi' => 0
        ]);
        $id_risk = $request->id_risk;
        return Redirect::back()->with(['success-swal' => 'Data '.$id_risk.' berhasil diubah menjadi Bukan KORPORASI.']);
    }

    public function mitigation($id, Request $request)
    {
        $risk_detail = RiskDetail::where('id_riskd', '=', $id)->first();
        $risk_detail->update([
            'status_mitigasi' => 1
        ]);
        $id_risk = $request->id_risk;
        return Redirect::back()->with(['success-swal' => 'Data '.$id_risk.' berhasil diubah menjadi Perlu Mitigasi.']);
    }

    public function notMitigation($id, Request $request)
    {
        $risk_detail = RiskDetail::where('id_riskd', '=', $id)->first();
        $risk_detail->update([
            'status_mitigasi' => 0
        ]);
        $id_risk = $request->id_risk;
        return Redirect::back()->with(['success-swal' => 'Data '.$id_risk.' berhasil diubah menjadi Tidak Mitigasi.']);
    }

    public function deleteRiskDetail($id, Request $request)
    {
        RiskDetail::destroy($id);
        $id_risk = $request->id_risk;
        return Redirect::back()->with(['success-swal' => 'Data '.$id_risk.' berhasil dihapus.']);
    }
}
