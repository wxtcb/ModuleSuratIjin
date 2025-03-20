<?php

namespace Modules\SuratIjin\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LupaAbsen extends Model
{
    use HasFactory;

    protected $fillable = [];
    
    protected static function newFactory()
    {
        return \Modules\SuratIjin\Database\factories\LupaAbsenFactory::new();
    }
}
