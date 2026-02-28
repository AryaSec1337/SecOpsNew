<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'timestamp',
        'status_code',
        'method',
        'path',
        'ip_address',
        'agent_name',
        'agent_ip',
        'os',
        'user_agent',
        'log_file',
        'details',
        'size'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'details' => 'array',
    ];

    protected $appends = ['is_threat'];

    public function getIsThreatAttribute()
    {
        return isset($this->details['threat_match']);
    }
}
