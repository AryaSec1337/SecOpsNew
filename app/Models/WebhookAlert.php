<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookAlert extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'scan_results' => 'array',
    ];

    /**
     * Get the associated webhook file scan record.
     */
    public function webhookFileScan()
    {
        return $this->belongsTo(WebhookFileScan::class);
    }

    /**
     * Get the badge color class for the verdict.
     */
    public function getVerdictColorAttribute(): string
    {
        return match($this->verdict) {
            'MALICIOUS' => 'rose',
            'SUSPICIOUS' => 'amber',
            'CLEAN' => 'emerald',
            default => 'slate',
        };
    }
}
