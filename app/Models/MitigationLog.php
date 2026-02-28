<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MitigationLog extends Model
{
    protected $fillable = [
        'title',
        'incident_time',
        'description',
        'analyst_decision',
        'event_log',
        'evidence_before',
        'evidence_after',
        'system_affected',
        'status',
        'mitigated_at',
        'user_id',
        'reporter_email',
        'reporter_department',
        'type', // General, Email Phishing, File Check, Domain Check
        'attack_classification', // True Attack, False Attack
        'email_subject',
        'email_sender',
        'email_recipient',
        'email_headers',
        'file_analysis_log_id',
        'url_analysis_log_id',
        'ip_analysis_id',

        'analysis_summary',
        'priority',
        'severity',
        'hostname',
        'internal_ip',
        'os',
        'network_zone',
    ];

    protected $casts = [
        'mitigated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(MitigationLogDetail::class)->latest('log_date');
    }

    public function files()
    {
        return $this->hasMany(MitigationLogFile::class);
    }

    public function fileAnalysis()
    {
        return $this->belongsTo(FileAnalysisLog::class, 'file_analysis_log_id');
    }

    public function urlAnalysis()
    {
        return $this->belongsTo(UrlAnalysisLog::class, 'url_analysis_log_id');
    }

    public function ipAnalysis()
    {
        return $this->belongsTo(IpAnalysis::class, 'ip_analysis_id');
    }

    public function webhookFileScan()
    {
        return $this->hasOne(WebhookFileScan::class);
    }
}
