<?php

namespace App\Http\Controllers\RiskOfficer;

use App\Abstracts\AbsMitigasiPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskHeader;
use Auth;
use Illuminate\Support\Arr;
use App\Models\RiskDetail;
use App\Models\MitigasiLogs;
use Redirect;
use Illuminate\Support\Facades\Crypt;
use DNS2D;
use Session;
use App\Models\DefendidUser;
use Barryvdh\DomPDF\Facade\Pdf as DomPDF;
use PDF;
use App\Models\SRisiko;
use App\Models\Pengukuran;
use Illuminate\Support\Facades\Route;
use App\Models\ShortUrl;
use Illuminate\Support\Str;

class MitigasiPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $headers = AbsMitigasiPlan::getAllData();
        return view('risk-officer.mitigasi-plan', compact("headers"));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $headers = AbsMitigasiPlan::getDataByIdRiskh($id);
        // dd($headers);
        return view('risk-officer.mitigasi-detail', compact("headers"));
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
        $query = AbsMitigasiPlan::updateRiskDetail($request, $id);
        $id_header = $query['id_header'];

        return redirect()->route('risk-officer.mitigasi-plan.show', $id_header)->with(['success-swal' => 'Data Mitigasi berhasil diubah.']);
    }

    public function getProgressData(Request $request) {
        $data = null;
        $logs = MitigasiLogs::where('id_riskd', '=', $request->id)->orderBy('created_at', 'DESC')->get();
        if($logs != null){
            $data = new \stdClass();
            $data->data = [];
            $count = 0;
            foreach($logs as $c){
                $status = '';
                if ($c->is_approved == 1) {
                    $status = '<div class="badge badge-success">Disetujui</div>';
                } else {
                    $status = '<div class="badge badge-danger">Belum Disetujui</div>';
                }
                if ($c->dokumen == null) {
                    $isi = [
                        $count + 1,
                        $c->realisasi,
                        date('d M Y', strtotime($c->created_at)),
                        '',
                        $c->description ? $c->description : '-',
                        $status
                    ];
                } else {
                    $path = asset('document/mitigasi-progress/'. $c->dokumen);
                    $isi = [
                        $count + 1,
                        $c->realisasi,
                        date('d M Y', strtotime($c->created_at)),
                        '<a href="'. $path. '"  target="_blank" class="btn btn-xs btn-info p-1">Lihat Dokumen</a>',
                        $c->description ? $c->description : '-',
                        $status
                    ];
                }
                array_push($data->data, $isi);
                $count++;
            }
        }
        return response()->json($data);
    }

    public function insertProgress(Request $request) {
        $extension_file = $request->file('dokumen')->extension();
        if($extension_file == "pdf" || $extension_file == "png" || $extension_file == "jpeg" ){
            $filename = null;
            if($request->dokumen) {
                $filename = $request->file('dokumen')->getClientOriginalName();
                $folder = '/document/mitigasi-progress/';
                $request->file('dokumen')->storeAs($folder, $filename, 'public');
            }
            MitigasiLogs::insert([
                'id_riskd' => $request->id_riskd,
                'id_user' => $request->id_user,
                'realisasi' => $request->prosentase,
                'dokumen' => $filename,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return Redirect::back()->with(['success-swal' => 'Progress Mitigasi berhasil ditambahkan.']);
        }else{
            return Redirect::back()->with(['error-swal' => 'Progress Mitigasi gagal ditambahkan. File dokumen harus dalam format pdf/png/jpeg. Silahkan upload ulang dokumen dengan format sesuai ketentuan.']);
        }
    }

    public function print($id) {
        $header = RiskHeader::where('id_riskh', '=', $id)->first();
        $document_type = 'mitigasi_plan';
        $url = "url='risk-officer/mitigasi-plan/print/".$header->id_riskh."';".
            "signed_by=".($header->pemeriksa ? $header->pemeriksa->name : '-').";".
            "instansi=".$header->perusahaan->instansi.";".
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
        $user = DefendidUser::where('id_user', '=', $header->id_user)->first();
        $encrypted = url('document/verify/').'/'.$short_url->short_code;
        $qrcode = DNS2D::getBarcodePNG($encrypted, 'QRCODE');
        $pdf = PDF::loadView('risk-officer.mitigasi-plan-pdf', compact('header', 'user', 'qrcode'))->setPaper('a4', 'landscape');
        Session::forget('is_bypass');
        return $pdf->stream('Hasil Mitigasi '.$user->instansi.' Tahun '.$header->tahun.'.pdf');
    }
}
