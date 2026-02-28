<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitigationLogDetail extends Model
{
    protected $fillable = [
        'mitigation_log_id',
        'action',
        'description',
        'log_date',
        'user_id',
    ];

    protected $casts = [
        'log_date' => 'datetime',
    ];

    public function mitigationLog()
    {
        return $this->belongsTo(MitigationLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
