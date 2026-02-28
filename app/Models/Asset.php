<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeDomains($query)
    {
        return $query->where('type', 'domain');
    }

    public function latestScan()
    {
        return $this->hasOne(DomainScanLog::class)->latestOfMany('scan_date');
    }

    public function scanHistory()
    {
        return $this->hasMany(DomainScanLog::class)->orderBy('scan_date', 'desc');
    }

    public function sslStatus()
    {
        return $this->hasOne(DomainSslStatus::class)->latest();
    }

    public function dnsRecords()
    {
        return $this->hasMany(DomainDnsRecord::class);
    }

    public function ransomwareVictims()
    {
        return $this->hasMany(RansomwareVictim::class);
    }
}
