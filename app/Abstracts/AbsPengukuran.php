<?php

namespace App\Abstracts;

// use App\Abstracts\AbsPengukuran;
use App\Models\DefendidPengukur;
use App\Models\Pengukuran;
use App\Models\SRisiko;
use DB;
use Auth;

class AbsPengukuran
{
    public static function index($filter)
    {
        $wr = '1=1';
        $wr_sr = 's_risiko.status_s_risiko = 1';
        if($filter != 'risk_officer'){
            if($filter == 'penilai' || $filter == 'penilai_indhan'){
                $wr_sr .= " AND defendid_pengukur.id_user = ".Auth::user()->id_user;
                $wr .= " AND defendid_pengukur.id_user = ".Auth::user()->id_user;
            }
        }
        else{
            $wr .= " AND defendid_pengukur.divisi_id = ".Auth::user()->divisi_id;
        }
        $data_pengukur = DefendidPengukur::whereRaw($wr)->get();
        if($filter == 'penilai_indhan'){
            // dd($data_sr);
            // foreach($data_sr as $d){
            //     // dd($d->id_s_risiko);
            //     $pengukuran = PengukuranIndhan::join('s_risiko', 'pengukuran_indhan.id_s_risiko', 's_risiko.id_s_risiko')
            //         ->where('pengukuran_indhan.id_s_risiko', $d->id_s_risiko)->get();
            //     // dd($pengukuran);
            //     // $jabatan = DefendidPengukur::join('defendid_user', 'defendid_pengukur.id_user', 'defendid_user.id_user')->where('defendid_user.is_penilai_indhan', 1)->get();
            //     $pengukur = DefendidPengukur::where('id_user', Auth::user()->id_user)->get();
            //     // dd($pengukur);
            //     $arr_pengukur = [];
            //     foreach($pengukur as $i=>$p){
            //         $pengukur_risk = PengukuranIndhan::join('s_risiko', 'pengukuran_indhan.id_s_risiko', 's_risiko.id_s_risiko')
            //         ->join('defendid_pengukur', 'pengukuran_indhan.id_pengukur', 'defendid_pengukur.id_pengukur')
            //         ->where('pengukuran_indhan.id_s_risiko', $d->id_s_risiko)
            //         ->where('pengukuran_indhan.id_pengukur', $p->id_pengukur)
            //         ->get();

            //         if(count($pengukur_risk) == 0){
            //                 $arr_pengukur[] = $p;
            //         }
            //     }
            // // dd(count($arr_pengukur));
            // }

            $data_sr = SRisiko::join('risk_detail', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')->where('status_indhan', 1)->get();
            // dd($data_pengukur);
            if(count($data_sr) > 0){
                $sr_exists = true;
                $pengukuran_1 = [];
                $pengukuran_2 = [];

                foreach($data_pengukur as $dp) {
                    // get all id_s_risiko divisi dengan status_indhan 1
                    $s_risk_divisi = SRisiko::join('risk_detail', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')
                        ->where('risk_detail.status_indhan', 1)
                        ->where('risk_detail.divisi_id', '!=', 6)
                        ->whereNull('s_risiko.deleted_at')
                        ->whereNull('risk_detail.deleted_at')
                        ->groupBy('s_risiko.id_s_risiko')
                        ->pluck('s_risiko.id_s_risiko')->toArray();
                    
                    // get all id_s_risiko indhan
                    $s_risk_indhan = SRisiko::where('s_risiko.divisi_id', 6)
                        ->whereNull('s_risiko.deleted_at')
                        ->pluck('id_s_risiko')->toArray();

                    $s_risk_all = array_merge($s_risk_divisi, $s_risk_indhan);

                    // get all id_s_risiko divisi yang sudah dinilai
                    $s_risk_dinilai_divisi = SRisiko::join('pengukuran_indhan as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->where('p.id_pengukur', '=', $dp->id_pengukur)
                        ->whereIn('s_risiko.id_s_risiko', $s_risk_divisi)
                        ->selectRaw('s_risiko.*, p.*')
                        ->whereNull('p.deleted_at')
                        ->groupBy('s_risiko.id_s_risiko')
                        ->pluck('s_risiko.id_s_risiko')->toArray();
                    
                    // get all id_s_risiko indhan yang sudah dinilai
                    $s_risk_dinilai_indhan = SRisiko::join('pengukuran_indhan as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->where('p.id_pengukur', '=', $dp->id_pengukur)
                        ->whereIn('s_risiko.id_s_risiko', $s_risk_indhan)
                        ->selectRaw('s_risiko.*, p.*')
                        ->whereNull('p.deleted_at')
                        ->groupBy('s_risiko.id_s_risiko')
                        ->pluck('s_risiko.id_s_risiko')->toArray();

                    $s_risk_dinilai = array_merge($s_risk_dinilai_divisi, $s_risk_dinilai_indhan);

                    // count id_s_risiko yang sudah dinilai group by tahun
                    $s_risk_dinilai_yearly = SRisiko::join('pengukuran_indhan as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->where('p.id_pengukur', '=', $dp->id_pengukur)
                        ->whereIn('s_risiko.id_s_risiko', $s_risk_dinilai)
                        ->groupBy('tahun')
                        ->selectRaw('s_risiko.*, COUNT(s_risiko.id_s_risiko) as jml_risk, p.*')
                        ->whereNull('s_risiko.deleted_at')
                        ->whereNull('p.deleted_at')
                        ->get();

                    foreach($s_risk_dinilai_yearly as $s) {
                        $pengukuran_1[] = $s;
                    }

                    $s_risk_perlu_dinilai = SRisiko::whereIn('id_s_risiko', $s_risk_all)
                        ->whereNotIn('s_risiko.id_s_risiko', $s_risk_dinilai)
                        ->groupBy('tahun')
                        ->selectRaw('*, COUNT(id_s_risiko) as jml_risk')
                        ->whereNull('deleted_at')
                        ->get();

                    foreach($s_risk_perlu_dinilai as $s) {
                        $s->nama_responden = $dp->nama;
                        $s->jabatan = $dp->jabatan;
                        $s->id_pengukur = $dp->id_pengukur;
                        $pengukuran_2[] = $s;
                    }
                }

                $results['data_sr'] = $data_sr;
                $results['pengukuran_1'] = $pengukuran_1;
                $results['pengukuran_2'] = $pengukuran_2;
            } else {
                $sr_exists = false;
            }
            $results['sr_exists'] = $sr_exists;
        } else {
            $data_sr = SRisiko::where('divisi_id', Auth::user()->divisi_id)->where('status_s_risiko', 1)->get();
            // dd(Auth::user()->divisi_id);
            // dd($data_sr);
            if(count($data_sr) > 0){
                $sr_exists = true;
                $pengukuran_1 = [];
                $pengukuran_2 = [];

                foreach($data_pengukur as $dp) {
                    // get all id_s_risiko yang sudah dinilai
                    $s_risk_dinilai = SRisiko::join('pengukuran as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->where('p.id_pengukur', '=', $dp->id_pengukur)
                        ->where('status_s_risiko', 1)
                        ->selectRaw('s_risiko.*, p.*')
                        ->whereNull('s_risiko.deleted_at')
                        ->whereNull('p.deleted_at')
                        ->pluck('id_s_risiko');
                    // count id_s_risiko yang sudah dinilai group by tahun
                    $s_risk_dinilai_yearly = SRisiko::join('pengukuran as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->where('p.id_pengukur', '=', $dp->id_pengukur)
                        ->where('status_s_risiko', 1)
                        ->groupBy('s_risiko.tahun')
                        ->selectRaw('s_risiko.*, COUNT(s_risiko.id_s_risiko) as jml_risk, p.*')
                        ->whereNull('s_risiko.deleted_at')
                        ->whereNull('p.deleted_at')
                        ->get();
                    foreach($s_risk_dinilai_yearly as $s) {
                        $pengukuran_1[] = $s;
                    }
                    $s_risk = SRisiko::where('status_s_risiko', 1)
                        ->where('s_risiko.divisi_id', $dp->divisi_id)
                        ->whereNotIn('s_risiko.id_s_risiko', $s_risk_dinilai)
                        ->groupBy('s_risiko.tahun')
                        ->selectRaw('s_risiko.*, COUNT(s_risiko.id_s_risiko) as jml_risk')
                        ->whereNull('s_risiko.deleted_at')
                        ->get();
                    foreach($s_risk as $s) {
                        $s->nama_responden = $dp->nama;
                        $s->jabatan = $dp->jabatan;
                        $s->id_pengukur = $dp->id_pengukur;
                        $pengukuran_2[] = $s;
                    }
                }

                $sumber_risiko = Pengukuran::join('s_risiko', 'pengukuran.id_s_risiko', 's_risiko.id_s_risiko')
                                ->join('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
                                ->join('defendid_pengukur', 'pengukuran.id_pengukur', 'defendid_pengukur.id_pengukur')
                                ->where('s_risiko.divisi_id', '=', Auth::user()->divisi_id)
                                ->whereRaw($wr_sr)
                                ->whereNull('s_risiko.deleted_at')
                                ->whereNull('defendid_pengukur.deleted_at')
                                ->whereNull('pengukuran.deleted_at')
                                ->get();

                // dd($sumber_risiko);
                $results['data_sr'] = $data_sr;
                $results['pengukuran_1'] = $pengukuran_1;
                $results['pengukuran_2'] = $pengukuran_2;
                // dd($results['pengukuran_2']);
                $results['sumber_risiko'] = $sumber_risiko;
            }
            else{
                $sr_exists = false;
            }

            $results['sr_exists'] = $sr_exists;

        }
        return $results;
    }
}

?>
