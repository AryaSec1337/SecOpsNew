<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModSecurityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'rule_matches' => 'array',
        'raw_log' => 'array',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
