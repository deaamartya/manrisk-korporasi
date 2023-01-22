<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\DefendidUser;
use App\Models\DefendidPengukur;
use App\Models\RiskHeader;
use App\Models\SRisiko;
use App\Models\PengajuanMitigasi;
use App\Models\RiskDetail;

class DivisiController extends Controller
{
    public function index()
    {
        $divisi = Divisi::whereNull('deleted_at')->get();

        return view('admin.divisi', compact('divisi'));
    }

    public function store(Request $request, $id = null)
    {
        $req = [
            'divisi_code' => 'required',
            'instansi' => 'required',
        ];

        $msg = [
            'divisi_code.required' => 'Kode divisi tidak boleh kosong',
            'instansi.required' => 'Nama divisi tidak boleh kosong'
        ];

        $request->validate($req, $msg);

        $params = [
            'divisi_code' => $request->divisi_code,
            'instansi' => $request->instansi,
            'updated_at' => Carbon::now()
        ];

        if($id == null){
            $params['created_at'] = Carbon::now();
            Divisi::insert($params);
            $messages = ['success-swal' => 'Divisi berhasil disimpan!'];
        }
        else{
            Divisi::where('divisi_id', $id)->update($params);
            $messages = ['success-swal' => 'Divisi berhasil diubah!'];
        }

        return back()->with($messages);
    }

    public function delete(Request $request)
    {
        try {
            $checkUser = DefendidUser::where('divisi_id', $request->divisi_id)->exists();
            $checkPengukur = DefendidPengukur::where('divisi_id', $request->divisi_id)->exists();
            $checkHeader = RiskHeader::where('divisi_id', $request->divisi_id)->exists();
            $checkSRisiko = SRisiko::where('divisi_id', $request->divisi_id)->exists();
            $checkPengajuan = PengajuanMitigasi::where('divisi_id', $request->divisi_id)->exists();
            $checkDetail = RiskDetail::where('divisi_id', $request->divisi_id)->exists();
            if (!$checkUser && !$checkPengukur && !$checkHeader && !$checkSRisiko && !$checkPengajuan && !$checkDetail) {
                Divisi::where('divisi_id', $request->divisi_id)->update(['deleted_at' => Carbon::now()]);
            } else {
                return back()->with(["error-swal" => 'Data masih digunakan pada user, pengukur, risk header, sumber risiko, pengajuan mitigasi atau risk detail. Mohon hapus data yang menggunakan divisi ini terlebih dahulu.']);
            }
        } catch (\ErrorException $e) {
            return back()->with(["error-swal" => $e->getMessage()]);
        }

        return back()->with(['success-swal' => 'Divisi berhasil dihapus!']);
    }

    public function get_divisi($id)
    {
        $divisi = Divisi::where('divisi_id', $id)->first();

        return response()->json($divisi, 200);
    }
}
