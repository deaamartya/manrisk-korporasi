<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusProses extends Model
{
    use HasFactory;
    protected $table = 'status_proses';
	protected $primaryKey = 'id_status_proses';
	protected $dates = ['created_at', 'updated_at'];

	protected $fillable = [
		'tahun',
		'id_proses',
		'divisi_id'
	];

	public function proses_manrisk()
	{
		return $this->belongsTo(ProsesManrisk::class, 'id_proses');
	}
	
	public function divisi()
	{
		return $this->belongsTo(Divisi::class, 'divisi_id');
	}
}
