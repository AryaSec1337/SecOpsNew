<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'risk_score',
        'geo_data',
        'virustotal_data',
        'abuseipdb_data',
        'greynoise_data',
        'alienvault_data',
    ];

    protected $casts = [
        'geo_data' => 'array',
        'virustotal_data' => 'array',
        'abuseipdb_data' => 'array',
        'greynoise_data' => 'array',
        'alienvault_data' => 'array',
    ];
}
