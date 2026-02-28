<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookFileScan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'yara_result' => 'array',
        'clamav_result' => 'array',
        'vt_result' => 'array',
        'timestamps_stages' => 'array',
    ];

    public function mitigationLog()
    {
        return $this->belongsTo(MitigationLog::class);
    }
}
