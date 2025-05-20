<?php

namespace Modules\SuratIjin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Pengaturan\Entities\Pegawai;

class TerlambatLogs extends Model
{
    use HasFactory;

    protected $table = 'terlambat_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['status', 'terlambat_id', 'updated_by', 'catatan'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class,'updated_by');
    }
}
