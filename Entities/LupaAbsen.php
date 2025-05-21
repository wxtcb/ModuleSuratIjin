<?php

namespace Modules\SuratIjin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pengaturan\Entities\Pegawai;
use Modules\Pengaturan\Entities\TimKerja;
use Modules\Pengaturan\Entities\Unit;

class LupaAbsen extends Model
{
    use HasFactory;
    protected $table = 'lupa_absen';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pegawai_id',
        'pejabat_id',
        'tim_kerja_id',
        'jenis_ijin',
        'hari',
        'tanggal',
        'alasan',
        'access_token',
        'status',
        'tanggal_disetujui_pejabat',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function timKerja() 
    {
        return $this->belongsTo(TimKerja::class);    
    }

        public function logs()
    {
        return $this->hasMany(LupaAbsenLogs::class, 'lupa_absen_id');
    }
}
