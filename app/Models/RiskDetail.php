<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * Class RiskDetail
 *
 * @property int $id_riskd
 * @property int $id_riskh
 * @property int $id_s_risiko
 * @property string|null $ppkh
 * @property string|null $indikator
 * @property string|null $sebab
 * @property string|null $dampak
 * @property string|null $uc
 * @property string|null $pengendalian
 * @property float|null $l_awal
 * @property float|null $c_awal
 * @property float|null $r_awal
 * @property string|null $peluang
 * @property string|null $tindak_lanjut
 * @property string|null $jadwal
 * @property string|null $pic
 * @property string|null $dokumen
 * @property string|null $mitigasi
 * @property Carbon|null $jadwal_mitigasi
 * @property int|null $realisasi
 * @property string|null $keterangan
 * @property float|null $l_akhir
 * @property float|null $c_akhir
 * @property float|null $r_akhir
 * @property int|null $status
 * @property string|null $u_file
 * @property int|null $status_mitigasi
 * @property int|null $status_korporasi
 *
 * @package App\Models
 */
class RiskDetail extends Model
{
	use SoftDeletes;

	protected $table = 'risk_detail';
	protected $primaryKey = 'id_riskd';

	protected $casts = [
		'id_riskh' => 'int',
		'id_s_risiko' => 'int',
		'l_awal' => 'float',
		'c_awal' => 'float',
		'r_awal' => 'float',
		'realisasi' => 'int',
		'l_akhir' => 'float',
		'c_akhir' => 'float',
		'r_akhir' => 'float',
		'status' => 'int',
		'status_mitigasi' => 'int',
		'status_korporasi' => 'int',
		'dampak_kuantitatif' => 'int',
		'dampak_kuantitatif_residu' => 'int',
		'biaya_penanganan' => 'int',
		'no_urut' => 'int'
	];

	protected $dates = [
		'jadwal_mitigasi',
		'deleted_at'
	];

	protected $fillable = [
		'id_riskh',
		'id_s_risiko',
		'ppkh',
		'indikator',
		'sasaran_kinerja',
		'sebab',
		'dampak_kuantitatif',
		'dampak',
		'uc',
		'pengendalian',
		'penilaian',
		'l_awal',
		'c_awal',
		'r_awal',
		'peluang',
		'tindak_lanjut',
		'jadwal',
		'pic',
		'dokumen',
		'mitigasi',
		'jadwal_mitigasi',
		'realisasi',
		'keterangan',
		'l_akhir',
		'c_akhir',
		'r_akhir',
		'dampak_kuantitatif_residu',
		'dampak_residu',
		'biaya_penanganan',
		'status',
		'u_file',
		'status_mitigasi',
		'status_korporasi',
		'no_urut'
	];

	public function risk_header()
	{
		return $this->belongsTo(RiskHeader::class, 'id_riskh');
	}

	public function sumber_risiko()
	{
		return $this->belongsTo(SRisiko::class, 'id_s_risiko');
	}

	public function pengajuan_mitigasi()
	{
		return $this->hasMany(PengajuanMitigasi::class, 'id_riskd');
	}

	public function mitigasi_logs()
	{
		return $this->hasMany(MitigasiLogs::class, 'id_riskd');
	}

    // Untuk set nomor urut
    public static function set_no_urut($id_riskh, $status_korporasi)
    {
        $result = [];
        $wr = '1=1';
        // if(!Auth::user()->is_admin){
        //     $wr .= ' AND risk_detail.divisi_id = '.Auth::user()->divisi_id;
        // }

        try {
            $risk_detail = self::leftJoin('s_risiko', 'risk_detail.id_s_risiko', 's_risiko.id_s_risiko')
            ->leftJoin('konteks', 's_risiko.id_konteks', 'konteks.id_konteks')
            ->whereRaw($wr)
            ->where(['risk_detail.id_riskh' => $id_riskh, 'risk_detail.tahun' => date('Y'), 'risk_detail.status_korporasi' => $status_korporasi])
            // ->orderBy('risk_detail.divisi_id', 'ASC')
            ->orderBy('konteks.id_risk', 'ASC')
            ->orderBy('risk_detail.created_at', 'ASC')
            ->select('risk_detail.*', 'konteks.id_risk')
            ->get();

            $no = 1;
            foreach ($risk_detail as $key => $value) {
                // if ($key > 0) {
                //     if ($risk_detail[$key]->divisi_id != $risk_detail[$key - 1]->divisi_id) {
                //         $no = 1;
                //     }
                // }
                if ($key > 0) {
                    if ($risk_detail[$key]->id_risk != $risk_detail[$key - 1]->id_risk) {
                        $no = 1;
                    }
                }
                RiskDetail::where('id_riskd', $value->id_riskd)->update(['no_urut' => $no]);
                $no ++;
            }
        } catch (\Throwable $th) {
            $result = [
                'success' => false,
                'message' => $th->getMessage()
            ];
            return $result;
        }

        $result = [
            'success' => true,
            'message' => 'Data berhasil diurutkan.'
        ];

        return $result;
    }
}
