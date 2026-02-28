<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WazuhAlert extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'rule_groups' => 'array',
        'rule_mitre' => 'array',
        'syscheck' => 'array',
        'raw_json' => 'array',
    ];

    /**
     * Get severity label based on Wazuh rule level.
     */
    public function getSeverityAttribute(): string
    {
        return match(true) {
            $this->rule_level >= 12 => 'Critical',
            $this->rule_level >= 10 => 'High',
            $this->rule_level >= 7  => 'Medium',
            $this->rule_level >= 4  => 'Low',
            default                 => 'Info',
        };
    }

    /**
     * Get severity color for UI badges.
     */
    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'Critical' => 'rose',
            'High'     => 'orange',
            'Medium'   => 'amber',
            'Low'      => 'blue',
            default    => 'slate',
        };
    }
}
