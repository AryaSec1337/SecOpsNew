<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitigationLogFile extends Model
{
    protected $fillable = [
        'mitigation_log_id',
        'file_path',
        'original_name',
        'file_type',
        'file_size',
    ];

    public function mitigationLog()
    {
        return $this->belongsTo(MitigationLog::class);
    }
}
