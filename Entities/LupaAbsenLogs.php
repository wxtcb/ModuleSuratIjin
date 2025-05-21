<?php

namespace Modules\SuratIjin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pengaturan\Entities\Pegawai;

class LupaAbsenLogs extends Model
{
    use HasFactory;

    protected $table = 'lupa_absen_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['status', 'lupa_absen_id', 'updated_by', 'catatan'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class,'updated_by');
    }
}
