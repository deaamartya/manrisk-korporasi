<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\SRisiko;
use App\Models\Pengukuran;
use App\Models\PengukuranKorporasi;
use App\Models\RiskHeader;
use App\Models\RiskHeaderKorporasi;
use App\Models\RiskDetail;
use App\Models\PengajuanMitigasi;
use App\Models\Mitigasi;
use App\Models\ProsesManrisk;
use App\Models\StatusProses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Session;

class GlobalController extends Controller
{
    public function get_divisi()
    {
        if(Auth::check()){
            $wr = "1=1";
            if(auth()->user()->is_risk_officer == 1){
                $wr .= " AND divisi_id = ".auth()->user()->divisi_id;
            }

            $divisi = Divisi::whereRaw($wr)->get();

            return response()->json($divisi, 200);
        }
        else{
            return response()->json(["message" => "Unauthorized"], 401);
        }
    }

    public function forum()
    {
        if(Auth::check()){
            $wr = '1=1';
            if(!Auth::user()->is_admin){
                $wr.=' AND forum.display = 1';
            }
            $data = DB::table('forum')
            ->leftJoin('defendid_user as du', 'du.id_user', 'forum.id_user')
            ->whereRaw($wr)
            ->orWhere('forum.id_user', Auth::user()->id_user)
            ->orderBy('forum.updated_at', 'desc')
            ->select('forum.id', 'forum.id_user', 'forum.subject', 'forum.body', 'forum.display', 'forum.updated_at', 'du.username')
            ->simplePaginate(25);

            return view('forum', compact('data'));
        }
        else{
            return route('login');
        }

    }

    public function get_forum($id)
    {
        $data = DB::table('forum')->where('id', $id)->first();

        return response()->json(['message' => 'success', 'data' => $data], 200);
    }

    public function forum_delete($id)
    {
        DB::beginTransaction();

        DB::table('forum_detail')->where('id_forum', $id)->delete();
        DB::table('forum')->where('id', $id)->delete();

        DB::commit();
        return back()->with(['success-swal' => 'Forum berhasil dihapus!']);
    }

    public function forum_detail($id)
    {
        if(Auth::check()){
            $forum = DB::table('forum')->where('id', $id)
            ->leftJoin('defendid_user as du', 'du.id_user', 'forum.id_user')
            ->select('forum.id', 'forum.id_user', 'forum.subject', 'forum.body', 'forum.display', 'forum.updated_at', 'du.username')
            ->first();
            $forum_detail = DB::table('forum_detail')->where('id_forum', $id)
            ->leftJoin('defendid_user as du', 'du.id_user', 'forum_detail.id_user')
            ->select('forum_detail.id_forum', 'forum_detail.id_user', 'forum_detail.body', 'forum_detail.created_at', 'du.username')
            ->get();

            return view('forum-detail', compact('forum', 'forum_detail'));
        }
        else{
            return route('login');
        }
    }

    public function forum_detail_store(Request $request, $id)
    {
        DB::table('forum_detail')->insert([
            'id_forum' => $id,
            'id_user' => Auth::user()->id_user,
            'body' => $request->message,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        return back()->with(['success-swal' => 'Tanggapan berhasil disimpan!']);
    }

    public function forum_store(Request $request, $id = null)
    {
        $req = [
            'subject' => 'required',
            'body' => 'required',
        ];

        $msg = [
            'subject.required' => 'Subject tidak boleh kosong',
            'body.required' => 'Isi/body tidak boleh kosong'
        ];

        $request->validate($req, $msg);

        $params = [
            'subject' => $request->subject,
            'body' => $request->body,
            'display' => $request->display,
            'updated_at' => Carbon::now()
        ];
        if($id){
            DB::table('forum')->where('id', $id)->update($params);
        }
        else{
            $params['id'] = Uuid::uuid4()->getHex();
            $params['id_user'] = Auth::user()->id_user;
            $params['created_at'] = Carbon::now();
            DB::table('forum')->insert($params);
        }

        return back()->with(['success-swal' => 'Forum berhasil disimpan!']);
    }

    public function get_notification()
    {
        // init
        $data_risk_officer = [];
        $data_penilai = [];
        $data_penilai_korporasi = [];
        $data_risk_owner = [];
        $data_admin = [];

        // check role
        if(Auth::user()->is_risk_officer){
            $data_risk_officer = $this->notif_risk_officer();
        }
        if(Auth::user()->is_penilai){
            $data_penilai = $this->notif_penilai();
        }
        if(Auth::user()->is_penilai_korporasi){
            $data_penilai_korporasi = $this->notif_penilai_korporasi();
        }
        if(Auth::user()->is_risk_owner){
            $data_risk_owner = $this->notif_risk_owner();
        }
        if(Auth::user()->is_admin){
            $data_admin = $this->notif_admin();
        }

        // merge notif
        $data = [];
        if(count($data_risk_officer) > 0){
            foreach($data_risk_officer as $dro){
                $data[] = $dro;
            }
        }
        if(count($data_penilai) > 0){
            foreach($data_penilai as $dp){
                $data[] = $dp;
            }
        }
        if(count($data_penilai_korporasi) > 0){
            foreach($data_penilai_korporasi as $dpi){
                $data[] = $dpi;
            }
        }
        if(count($data_risk_owner) > 0){
            foreach($data_risk_owner as $drow){
                $data[] = $drow;
            }
        }
        if(count($data_admin) > 0){
            foreach($data_admin as $da){
                $data[] = $da;
            }
        }

        // Notif/Alert kondisi risiko yang melewati jatuh tempo
        if(Auth::user()->is_admin || Auth::user()->is_risk_officer){
            $segment = "/deadline-mitigasi";
            // if(Auth::user()->is_risk_officer){
            //     $segment = '/risk-officer/deadline-mitigasi';
            // }
            // else{
            //     if(Auth::user()->is_penilai){
            //         $segment = '/penilai/pengukuran-risiko';
            //     }
            //     else if(Auth::user()->is_penilai_korporasi){
            //         $segment = '/penilai-korporasi/pengukuran-risiko-korporasi';
            //     }
            //     else if(Auth::user()->is_risk_owner){
            //         $segment = '/risk-owner/pengukuran-risiko';
            //     }
            // }

            // $temp = DB::raw("(
            //     SELECT MAX(realisasi) as final_realisasi, id_riskd FROM mitigasi_logs WHERE is_approved = 1 ORDER BY updated_at DESC
            // ) as mitigasi_logs");
            // $mitigasi_jatuh_tempo = Mitigasi::leftJoin('risk_detail', 'risk_detail.id_riskd', 'mitigasi.id_riskd')
            // ->leftJoin($temp, 'mitigasi_logs.id_riskd', 'risk_detail.id_riskd')
            // ->where('risk_detail.jadwal_mitigasi', '<', Carbon::now()->format('Y-m-d'))
            // ->where('mitigasi_logs.final_realisasi', '<', 100)
            // ->select('risk_detail.id_riskd', 'risk_detail.mitigasi')
            // ->get();

            $risk = RiskHeader::getAllMitigasiDetail();
            $mitigasi_jatuh_tempo = [];
            foreach($risk as $index => $value){
                if(date('Y-m-d', strtotime($value->jadwal)) < Carbon::now()->format('Y-m-d') && $value->final_realisasi < 100){
                    $mitigasi_jatuh_tempo[] = $value;
                }
            }

            Session::forget('deadline-mitigasi');
            $total_jatuh_tempo = count($mitigasi_jatuh_tempo);
            if($total_jatuh_tempo > 0){
                Session::put('deadline-mitigasi', $total_jatuh_tempo);
                $data[] = [
                    'title' => 'Terdapat risiko telah melewati tanggal jatuh tempo sebanyak ',
                    'jumlah' => $total_jatuh_tempo,
                    'link' => url('/').$segment
                ];
            }
        }

        return response()->json(['message' => 'ok', 'data' => $data], 200);
    }

    public function notif_risk_officer()
    {
        $s_risk_dinilai = SRisiko::join('pengukuran as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
        ->join('defendid_pengukur as dp', 'p.id_pengukur', 'dp.id_pengukur')
        ->where('dp.id_user', Auth::user()->id_user)
        ->where('status_s_risiko', 1)
        ->whereNull('s_risiko.deleted_at')
        ->pluck('s_risiko.id_s_risiko');
        // dd($s_risk_dinilai);
        $jml_risk = SRisiko::where('status_s_risiko', 1)
            ->where('s_risiko.divisi_id', Auth::user()->divisi_id)
            ->whereNotIn('s_risiko.id_s_risiko', $s_risk_dinilai)
            ->whereNull('s_risiko.deleted_at')
            ->count('s_risiko.id_s_risiko');
        // dd($jml_risk);

        $mitigasi_logs = DB::table('mitigasi_logs')->leftJoin('risk_detail as rd', 'rd.id_riskd', 'mitigasi_logs.id_riskd')
        ->where('rd.divisi_id', Auth::user()->divisi_id)
        ->pluck('mitigasi_logs.id_riskd')->toArray();
        // dd($mitigasi_logs);
        $jml_mitigasi = RiskDetail::join('s_risiko', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')
                        ->select('risk_detail.id_riskd')
                        ->where('risk_detail.divisi_id', Auth::user()->divisi_id)
                        ->whereNull('risk_detail.deleted_at')
                        ->whereNotIn('risk_detail.id_riskd', $mitigasi_logs)
                        ->count();

        // $rd = [];
        // foreach ($risk_detail as $key => $value) {
        //     $rd[] = $value->id_riskd;
        // }


        $data = [];
        if($jml_risk > 0){
            $data[] = [
                'title' => 'Terdapat pengukuran risiko divisi sebanyak ',
                'jumlah' => $jml_risk,
                'link' => url('/')."/risk-officer/pengukuran-risiko"
            ];
        }
        if($jml_mitigasi > 0){
            $data[] = [
                'title' => 'Terdapat detail risiko yang belum dimitigasi sebanyak ',
                'jumlah' => $jml_mitigasi,
                'link' => url('/')."/risk-officer/mitigasi-plan"
            ];
        }

        // $data = [[
        //     'title' => 'Terdapat pengukuran risiko divisi sebanyak ',
        //     'jumlah' => $jml_risk
        // ],
        // [
        //     'title' => 'Terdapat detail risiko yang belum dimitigasi sebanyak ',
        //     'jumlah' => $jml_risk
        // ]];

        return $data;
    }

    public function notif_risk_owner()
    {
        $s_risk_dinilai = SRisiko::join('pengukuran as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
                        ->join('defendid_pengukur as dp', 'p.id_pengukur', 'dp.id_pengukur')
                        ->where('dp.id_user', Auth::user()->id_user)
                        ->where('status_s_risiko', 1)
                        ->whereNull('s_risiko.deleted_at')
                        ->pluck('s_risiko.id_s_risiko');
        // dd($s_risk_dinilai);
        $jml_risk = SRisiko::where('status_s_risiko', 1)
                    ->where('s_risiko.divisi_id', Auth::user()->divisi_id)
                    ->whereNotIn('s_risiko.id_s_risiko', $s_risk_dinilai)
                    ->whereNull('s_risiko.deleted_at')
                    ->count('s_risiko.id_s_risiko');
        // dd($jml_risk);

        $jml_approval_risk_register = RiskHeader::where(['divisi_id' => Auth::user()->divisi_id, 'status_h' => 0])->count();

        $data = [];
        if($jml_risk > 0){
            $data[] = [
                'title' => 'Terdapat pengukuran risiko divisi sebanyak ',
                'jumlah' => $jml_risk,
                'link' => url('/')."/risk-owner/pengukuran-risiko"
            ];
        }
        if($jml_approval_risk_register > 0){
            $data[] = [
                'title' => 'Terdapat risk register divisi yang belum disetujui sebanyak ',
                'jumlah' => $jml_approval_risk_register,
                'link' => url('/')."/risk-owner/risiko"
            ];
        }

        // $data = [[
        //     'title' => 'Terdapat pengukuran risiko divisi sebanyak ',
        //     'jumlah' => $jml_risk
        // ],
        // [
        //     'title' => 'Terdapat risk register divisi yang belum disetujui sebanyak ',
        //     'jumlah' => $jml_approval_risk_register
        // ]];

        return $data;
    }

    public function notif_penilai()
    {
        $s_risk_dinilai = SRisiko::join('pengukuran as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
        ->join('defendid_pengukur as dp', 'p.id_pengukur', 'dp.id_pengukur')
        ->where('dp.id_user', Auth::user()->id_user)
        ->where('status_s_risiko', 1)
        ->whereNull('s_risiko.deleted_at')
        ->pluck('s_risiko.id_s_risiko');
        // dd($s_risk_dinilai);
        $jml_risk = SRisiko::where('status_s_risiko', 1)
            ->where('s_risiko.divisi_id', Auth::user()->divisi_id)
            ->whereNotIn('s_risiko.id_s_risiko', $s_risk_dinilai)
            ->whereNull('s_risiko.deleted_at')
            ->count('s_risiko.id_s_risiko');
        // dd($jml_risk);

        $data = [[
            'title' => 'Terdapat pengukuran risiko divisi sebanyak ',
            'jumlah' => $jml_risk,
            'link' => url('/')."/penilai/pengukuran-risiko"
        ]];
        if($jml_risk == 0){
            $data = [];
        }

        return $data;
    }

    public function notif_penilai_korporasi()
    {
        $s_risk_divisi = SRisiko::join('risk_detail', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')
            ->where('risk_detail.status_korporasi', 1)
            ->where('risk_detail.divisi_id', '!=', 6)
            ->whereNull('s_risiko.deleted_at')
            ->whereNull('risk_detail.deleted_at')
            ->groupBy('s_risiko.id_s_risiko')
            ->pluck('s_risiko.id_s_risiko')->toArray();

        // get all id_s_risiko korporasi
        $s_risk_korporasi = SRisiko::where('divisi_id', 6)
            ->whereNull('deleted_at')
            ->pluck('id_s_risiko')->toArray();

        $s_risk_all = array_merge($s_risk_divisi, $s_risk_korporasi);

        // get all id_s_risiko divisi yang sudah dinilai
        $s_risk_dinilai_divisi = SRisiko::join('pengukuran_korporasi as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
            ->join('defendid_pengukur as dp', 'p.id_pengukur', 'dp.id_pengukur')
            ->where('dp.id_user', Auth::user()->id_user)
            ->whereIn('s_risiko.id_s_risiko', $s_risk_divisi)
            ->selectRaw('s_risiko.*, p.*')
            ->whereNull('p.deleted_at')
            ->groupBy('s_risiko.id_s_risiko')
            ->pluck('s_risiko.id_s_risiko')->toArray();

        // get all id_s_risiko korporasi yang sudah dinilai
        $s_risk_dinilai_korporasi = SRisiko::join('pengukuran_korporasi as p', 'p.id_s_risiko', 's_risiko.id_s_risiko')
            ->join('defendid_pengukur as dp', 'p.id_pengukur', 'dp.id_pengukur')
            ->where('dp.id_user', Auth::user()->id_user)
            ->whereIn('s_risiko.id_s_risiko', $s_risk_korporasi)
            ->selectRaw('s_risiko.*, p.*')
            ->whereNull('p.deleted_at')
            ->groupBy('s_risiko.id_s_risiko')
            ->pluck('s_risiko.id_s_risiko')->toArray();

        $s_risk_dinilai = array_merge($s_risk_dinilai_divisi, $s_risk_dinilai_korporasi);

        $jml_risk = SRisiko::whereIn('id_s_risiko', $s_risk_all)
            ->whereNotIn('id_s_risiko', $s_risk_dinilai)
            ->whereNull('deleted_at')
            ->count('id_s_risiko');

        $data = [[
            'title' => 'Terdapat pengukuran risiko korporasi sebanyak ',
            'jumlah' => $jml_risk,
            'link' => url('/')."/penilai-korporasi/pengukuran-risiko-korporasi"
        ]];
        if($jml_risk == 0){
            $data = [];
        }

        return $data;
    }

    public function notif_admin()
    {
        $approval_srisiko_korporasi = SRisiko::
        //join('risk_detail', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')
                                    // where('s_risiko.tahun', date('Y'))
                                    where('s_risiko.status_s_risiko', 0)
                                    // ->where('risk_detail.status_korporasi', 1)
                                    // ->whereNull('risk_detail.deleted_at')
                                    ->count('s_risiko.id_s_risiko');
        $approval_pengajuan_mitigasi_korporasi = PengajuanMitigasi::where('is_approved', 0)->count();
        $approval_risk_register_divisi = RiskHeader::where('status_h_korporasi', 0)
                                    ->where('status_h', 1)->count();
        $risk_detail = DB::raw("(
            SELECT id_riskd, id_riskh FROM risk_detail WHERE deleted_at IS NULL AND status_mitigasi = 1
        ) as risk_detail");
        $need_approve = DB::raw("(
            SELECT id as need_approve, id_riskd FROM mitigasi_logs WHERE is_approved = 0
        ) as need_approve");
        $approval_hasil_mitigasi = RiskHeader::leftJoin($risk_detail, 'risk_detail.id_riskh', 'risk_header.id_riskh')
            ->leftJoin($need_approve, 'need_approve.id_riskd', 'risk_detail.id_riskd')
            ->whereNull('risk_header.deleted_at')
            ->groupBy('risk_header.id_riskh')
            ->count('need_approve.need_approve');
        // $mitigasi_logs = DB::raw("(
        //     SELECT id_riskd FROM mitigasi_logs WHERE is_approved != 2 AND realisasi < 100
        // ) as mitigasi_logs");
        // $hasil_mitigasi_korporasi = DB::table('risk_detail')
        //                                 ->leftJoin($mitigasi_logs, 'mitigasi_logs.id_riskd', 'risk_detail.id_riskd')
        //                                 ->where(['status_korporasi' => 1, 'divisi_id' => 6])
        //                                 ->groupBy('risk_detail.id_riskd')
        //                                 ->count();

        $data = [];
        if($approval_srisiko_korporasi > 0){
            $data[] = [
                'title' => 'Terdapat sumber risiko yang belum disetujui sebanyak ',
                'jumlah' => $approval_srisiko_korporasi,
                'link' => url('/')."/admin/sumber-risiko-korporasi"
            ];
        }
        if($approval_pengajuan_mitigasi_korporasi > 0){
            $data[] = [
                'title' => 'Terdapat pengajuan mitigasi yang belum disetujui sebanyak ',
                'jumlah' => $approval_pengajuan_mitigasi_korporasi,
                'link' => url('/')."/admin/mitigasi-plan"
            ];
        }
        if($approval_risk_register_divisi > 0){
            $data[] = [
                'title' => 'Terdapat risk register divisi yang belum disetujui sebanyak ',
                'jumlah' => $approval_risk_register_divisi,
                'link' => url('/')."/admin/risk-register-divisi"
            ];
        }
        if($approval_hasil_mitigasi > 0){
            $data[] = [
                'title' => 'Terdapat hasil mitigasi yang belum disetujui sebanyak ',
                'jumlah' => $approval_hasil_mitigasi,
                'link' => url('/')."/admin/approval-hasil-mitigasi"
            ];
        }
        // if($hasil_mitigasi_korporasi > 0){
        //     $data[] = [
        //         'title' => 'Terdapat mitigasi korporasi yang kurang dari 100% sebanyak ',
        //         'jumlah' => $hasil_mitigasi_korporasi,
        //         'link' => url('/')."admin/mitigasi-plan-korporasi/index"
        //     ];
        // }

        return $data;
    }

    public function deadlineMitigasi()
    {
        // $temp = DB::raw("(
        //     SELECT MAX(realisasi) as final_realisasi, id_riskd FROM mitigasi_logs WHERE is_approved = 1
        // ) as mitigasi_logs");

        // $data = Mitigasi::leftJoin('risk_detail', 'risk_detail.id_riskd', 'mitigasi.id_riskd')
        // ->leftJoin($temp, 'mitigasi_logs.id_riskd', 'risk_detail.id_riskd')
        // ->leftJoin('s_risiko', 's_risiko.id_s_risiko', 'risk_detail.id_s_risiko')
        // ->leftJoin('konteks', 'konteks.id_konteks', 's_risiko.id_konteks')
        // ->leftJoin('divisi', 'divisi.divisi_id', 'risk_detail.divisi_id')
        // ->where(DB::raw("DATE_FORMAT(risk_detail.jadwal_mitigasi, '%Y-%m-%d')"), '<', Carbon::now()->format('Y-m-d'))
        // ->where('mitigasi_logs.final_realisasi', '<', 100)
        // ->selectRaw('
        //     konteks.id_risk,
        //     konteks.no_k,
        //     s_risiko.s_risiko,
        //     risk_detail.mitigasi,
        //     risk_detail.jadwal_mitigasi,
        //     mitigasi_logs.final_realisasi,
        //     divisi.divisi,
        //     risk_detail.tahun
        // ')
        // ->get();

        $risk = RiskHeader::getAllMitigasiDetail();
        $data = [];
        foreach($risk as $index => $value){
            if(date('Y-m-d', strtotime($value->jadwal)) < Carbon::now()->format('Y-m-d') && $value->final_realisasi < 100){
                $data[] = $value;
            }
        }

        return view('deadline_mitigasi', compact('data'));
    }

    public function statusProses()
    {
        if(Auth::user()->is_admin){
            $status_proses = StatusProses::all();
        }else{
            $status_proses = StatusProses::where('divisi_id', '=', Auth::user()->divisi_id)->get();
        }

        $proses = ProsesManrisk::all();
        // dd($status_proses);
        return view('status_proses', compact('status_proses', 'proses'));
    }

    public function storeStatusProses(Request $request)
    {
      $request->validate([
        'tahun' => 'required',
        'id_proses' => 'required',
      ]);

      StatusProses::insert([
        'tahun' => $request->tahun,
        'divisi_id' => Auth::user()->divisi_id,
        'id_proses' => $request->id_proses,
        'created_at' => now(),
      ]);

      return redirect()->route('status-proses.index')->with(['success-swal' => 'Status proses terkini berhasil disimpan!']);
    }

    public function updateStatusProses(Request $request, $id)
    {
      $request->validate([
        'tahun' => 'required',
        'id_proses' => 'required',
      ]);

      StatusProses::find($id)->update([
        'tahun' => $request->tahun,
        'id_proses' => $request->id_proses,
        'updated_at' => now(),
      ]);

      return redirect()->route('status-proses.index')->with(['success-swal' => 'Status proses terkini berhasil diubah!']);
    }

    public function destroyStatusProses($id)
    {
      StatusProses::find($id)->delete();
      return redirect()->route('status-proses.index')->with(['success-swal' => 'Status proses terkini telah dihapus!']);
    }
}
