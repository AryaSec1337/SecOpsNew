<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainScanLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'scan_date' => 'datetime',
        'vt_stats' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
