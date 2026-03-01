<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BadIpAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_description',
        'src_ip',
        'dest_ip',
        'dest_port',
        'proto',
        'signature_severity',
        'raw_data',
        'status',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];

    // Helper to get tailwind colors based on Suricata Severity
    public function getSeverityColorAttribute()
    {
        return match (strtolower($this->signature_severity ?? '')) {
            'major', 'critical', 'high' => 'rose',
            'warning', 'medium' => 'amber',
            'minor', 'low', 'info' => 'blue',
            default => 'slate',
        };
    }
}
