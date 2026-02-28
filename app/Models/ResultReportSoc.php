<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultReportSoc extends Model
{
    protected $table = 'result_report_soc';
    protected $guarded = [];

    protected $casts = [
        'ip_info' => 'array',
        'greynoise' => 'array',
        'virustotal' => 'array',
        'abuseipdb' => 'array',
        'alienvault' => 'array',
    ];
}
