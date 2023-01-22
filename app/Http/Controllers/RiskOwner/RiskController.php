<?php

namespace App\Http\Controllers\RiskOwner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskHeader;
use App\Models\DefendidUser;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use Auth;
use PDF;
use App\Models\SRisiko;
use App\Models\RiskDetail;
use Illuminate\Support\Facades\Crypt;
use DNS2D;
use Session;
use Illuminate\Support\Facades\Route;
use App\Models\ShortUrl;
use Illuminate\Support\Str;

class RiskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $headers = RiskHeader::where('divisi_id', '=', Auth::user()->divisi_id)->whereNull('deleted_at')->get();
        return view('risk-owner.risk', compact("headers"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        RiskHeader::insert([
            'id_user' => Auth::user()->id_user,
            'tahun' => $request->tahun,
            'target' => $request->target,
            'penyusun' => Auth::user()->nama,
        ]);
        return redirect()->route('risk-owner.risiko.index')->with(['success-swal' => 'Risk Header berhasil disimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $headers = RiskHeader::where('id_riskh', '=', $id)->first();
        // $pilihan_s_risiko = SRisiko::where([
        //     ['id_user', '=', Auth::user()->id_user],
        //     ['tahun', '=', date('Y')],
        //     ['status_s_risiko', '=', 1],
        //     ['divisi_id', '=', Auth::user()->divisi_id],
        // ])->orderBy('id_s_risiko')->get();

        $s_risk_diinput = RiskDetail::where([
            ['divisi_id', '=', Auth::user()->divisi_id],
        ])->pluck('id_s_risiko');
        // dd($s_risk_diinput);
        $pilihan_s_risiko = SRisiko::where([
            ['status_s_risiko', '=', 1],
            ['divisi_id', '=', Auth::user()->divisi_id],
        ]
        )
        ->whereNotIn('id_s_risiko', $s_risk_diinput)
        ->orderBy('id_s_risiko')->get();

        return view('risk-owner.risk-detail', compact("headers", 'pilihan_s_risiko'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $riskheader = RiskHeader::where('id_riskh', '=', $id)->first();
        $riskheader->update([
            'tahun' => $request->tahun,
            'target' => $request->target
        ]);
        return redirect()->route('risk-owner.risiko.index')->with(['success-swal' => 'Risk Header berhasil diubah!']);
    }

    public function print($id) {
        $header = RiskHeader::where('id_riskh', '=', $id)->first();
        $user = DefendidUser::where('id_user', '=', $header->id_user)->first();
        $document_type = 'risk_register_ro';
        $url = "url='risk-owner/risiko/print/".$header->id_riskh."';".
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
        $pdf = PDF::loadView('risk-owner.risk-header-pdf', compact('header', 'user', 'qrcode'))->setPaper('a4', 'landscape');
        Session::forget('is_bypass');
        // return view('risk-officer.risk-header-pdf', compact('header', 'user'));
        return $pdf->stream('Laporan Manajemen Risiko '.$header->divisi->instansi.' Tahun '.$header->tahun.'.pdf');
    }

    public function toggleKorporasi($id) {
        $detail = RiskDetail::where('id_riskd', '=', $id)->first();
        $riskh = $detail->id_riskh;
        if ($detail->status_korporasi === 0) {
            $detail->update([
                'status_korporasi' => 1,
            ]);
        } else {
            $detail->update([
                'status_korporasi' => 0,
            ]);
        }
        return redirect()->route('risk-owner.risiko.show', $riskh)->with(['success-swal' => 'Detail risiko berhasil diupdate!']);
    }

    public function approve($id) {
        $header = RiskHeader::where('id_riskh', '=', $id)->first();
        if ($header->status_h === 0) {
            $header->update([
                'pemeriksa' => Auth::user()->name,
                'status_h' => 1,
            ]);
        } else {
            $header->update([
                'pemeriksa' => Auth::user()->name,
                'status_h' => 0,
            ]);
        }
        return redirect()->route('risk-owner.risiko.index')->with(['success-swal' => 'Risiko berhasil disetujui!']);
    }
}
