<?php

namespace App\Http\Controllers\RiskOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiskDetail;
use Redirect;
use App\Imports\RiskDetailImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use App\Models\PengajuanMitigasi;
use App\Models\MitigasiLogs;
use App\Models\SRisiko;

class RiskDetailController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except('_token');
        $data['status_mitigasi'] = ($request->r_awal >= 12) ? 1 : 0;
        $inputan_idr = preg_replace("/[^0-9]/", "", $request->dampak_kuantitatif);
        $data['dampak_kuantitatif'] = (int) $inputan_idr;
        $inputan_idr_residu = preg_replace("/[^0-9]/", "", $request->dampak_kuantitatif_residu);
        $data['dampak_kuantitatif_residu'] = (int) $inputan_idr_residu;

        $get_konteks = SRisiko::join('konteks as k', 's_risiko.id_konteks', 'k.id_konteks')
            ->where('id_s_risiko', '=', $data['id_s_risiko'])
            ->pluck('k.id_risk');
        // dd($get_konteks);
        $cek_konteks = RiskDetail::join('s_risiko as s', 'risk_detail.id_s_risiko', 's.id_s_risiko')
        ->join('konteks as k', 's.id_konteks', 'k.id_konteks')
        ->where('k.id_risk', '=', $get_konteks)
        ->exists();
        // dd($cek_konteks);
        if($cek_konteks){
            $no_urut = RiskDetail::join('s_risiko as s', 'risk_detail.id_s_risiko', 's.id_s_risiko')
            ->join('konteks as k', 's.id_konteks', 'k.id_konteks')
            ->where('k.id_risk', '=', $get_konteks)
            ->max('no_urut');
            // dd($no_urut);
            $data['no_urut'] = (int) $no_urut + 1;
        }else{
            $data['no_urut'] = 1;
        }

        // dd($data['no_urut']);

        RiskDetail::insert($data);
        return Redirect::back()->with(['success-swal' => 'Risk Detail berhasil dibuat!']);
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
        $risk_detail = RiskDetail::where('id_riskd', '=', $id)->first();

        $data = $request->except('_token');
        $inputan_idr = preg_replace("/[^0-9]/", "", $request->dampak_kuantitatif);
        $data['dampak_kuantitatif'] = (int) $inputan_idr;
        $inputan_idr_residu = preg_replace("/[^0-9]/", "", $request->dampak_kuantitatif_residu);
        $data['dampak_kuantitatif_residu'] = (int) $inputan_idr_residu;

        $risk_detail->update($data);
        return Redirect::back()->with(['success-swal' => 'Risk Detail berhasil diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $count = PengajuanMitigasi::where('id_riskd', '=', $id)->count('id_riskd');
        $count += MitigasiLogs::where('id_riskd', '=', $id)->count('id_riskd');
        if ($count > 0) {
            return back()->with(["error-swal" => 'Data ini masih digunakan pada pengajuan mitigasi atau log mitigasi. Mohon hapus data yang menggunakan  risiko ini terlebih dahulu.']);
        }
        RiskDetail::destroy($id);
        return Redirect::back()->with(['success-swal' => 'Risk Detail berhasil dihapus!']);
    }
}
