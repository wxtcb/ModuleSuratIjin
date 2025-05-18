<?php

namespace Modules\SuratIjin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pengaturan\Entities\Pegawai;

class Terlambat extends Model
{
    use HasFactory;

    protected $table = 'terlambat';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pegawai_id',
        'pejabat_id',
        'tim_kerja_id',
        'jenis_ijin',
        'jam',
        'hari',
        'tanggal',
        'alasan',
        'access_token',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
