<?php

namespace Modules\SuratIjin\Entities;

use Facade\FlareClient\Time\Time;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pengaturan\Entities\Pegawai;
use Modules\Pengaturan\Entities\TimKerja;
use Modules\Pengaturan\Entities\Unit;

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
        return $this->hasMany(TerlambatLogs::class, 'terlambat_id');
    }
}
