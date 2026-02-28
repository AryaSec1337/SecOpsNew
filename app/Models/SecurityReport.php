<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityReport extends Model
{
    protected $table = 'security_reports';

    protected $fillable = [
        'period',       // Example: AI-SCAN-1770601431 or 2026-02
        'summary_json', // column JSON
    ];

    protected $casts = [
        'summary_json' => 'array', // JSON -> array
    ];
}
