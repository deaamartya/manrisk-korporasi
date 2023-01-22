<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class Divisi
 * 
 * @property int $divisi_id
 * @property string $divisi_code
 * @property string $divisi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|DefendidUser[] $defendid_users
 *
 * @package App\Models
 */
class Divisi extends Model
{
	protected $table = 'divisi';
	protected $primaryKey = 'divisi_id';

	protected $fillable = [
		'divisi_code',
		'divisi'
	];

	public function getCountMitigasi() {
		return RiskDetail::where('divisi_id', $this->divisi_id)
			->where('status_mitigasi', '=', 1)
			->whereNull('deleted_at')
			->count('id_riskd');
	}

	public function getCountMitigasiDone() {
		return RiskDetail::join('mitigasi_logs as m', 'm.id_riskd', 'risk_detail.id_riskd')
			->where('risk_detail.divisi_id', $this->divisi_id)
			->where('m.realisasi', '=', 100)
			->where('m.is_approved', '=', 1)
			->whereNull('risk_detail.deleted_at')
			->count('risk_detail.id_riskd');
	}

	public function countLow() {
		if ($this->divisi_id == 6) {
			return RiskDetail::where('divisi_id', $this->divisi_id)
				->where('r_awal', '<', 6)
				->whereNull('deleted_at')
				->count('id_riskd');
		}
		return RiskHeader::join('risk_detail as d','d.id_riskh','=','risk_header.id_riskh')
			->where('d.divisi_id', $this->divisi_id)
			->where('r_awal', '<', 6)
			->whereNull('risk_header.deleted_at')
			->whereNull('d.deleted_at')
			->count('d.id_riskd');
	}

	public function countMed() {
		if ($this->divisi_id == 6) {
			return RiskDetail::where('divisi_id', $this->divisi_id)
				->where('r_awal', '>=', 6)
				->where('r_awal', '<', 12)
				->whereNull('deleted_at')
				->count('id_riskd');
		}
		return RiskHeader::join('risk_detail as d','d.id_riskh','=','risk_header.id_riskh')
			->where('d.divisi_id', $this->divisi_id)
			->where('r_awal', '>=', 6)
			->where('r_awal', '<', 12)
			->whereNull('risk_header.deleted_at')
			->whereNull('d.deleted_at')
			->count('d.id_riskd');
	}

	public function countHigh() {
		if ($this->divisi_id == 6) {
			return RiskDetail::where('divisi_id', $this->divisi_id)
				->where('r_awal', '>=', 12)
				->where('r_awal', '<', 16)
				->whereNull('deleted_at')
				->count('id_riskd');
		}
		return RiskHeader::join('risk_detail as d','d.id_riskh','=','risk_header.id_riskh')
			->where('d.divisi_id', $this->divisi_id)
			->where('r_awal', '>=', 12)
			->where('r_awal', '<', 16)
			->whereNull('risk_header.deleted_at')
			->whereNull('d.deleted_at')
			->count('d.id_riskd');
	}

	public function countExtreme() {
		if ($this->divisi_id == 6) {
			return RiskDetail::where('divisi_id', $this->divisi_id)
				->where('r_awal', '>=', 16)
				->whereNull('deleted_at')
				->count('id_riskd');
		}
		return RiskHeader::join('risk_detail as d','d.id_riskh','=','risk_header.id_riskh')
			->where('d.divisi_id', $this->divisi_id)
			->where('r_awal', '>=', 16)
			->whereNull('risk_header.deleted_at')
			->whereNull('d.deleted_at')
			->count('d.id_riskd');
	}

	public function mitigasiPercentage() {
		$count_mitigasi = $this->getCountMitigasi();
		$count_done_mitigasi = $this->getCountMitigasiDone();
		if ($count_mitigasi < 1) {
			return 100;
		}
		return intval($count_done_mitigasi / $count_mitigasi * 100);
	}

	public function sumber_risiko()
	{
		return $this->hasMany(SRisiko::class, 'id_s_risiko');
	}

	public function status_proses()
	{
		return $this->hasMany(StatusProses::class, 'id_status_proses');
	}
}
