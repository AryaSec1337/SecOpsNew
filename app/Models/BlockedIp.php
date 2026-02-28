<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Asset;


class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'port',
        'protocol',
        'agent_id',
        'rule_id',
        'status',
        'reason',
        'blocked_at',
        'user_id',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(Asset::class, 'agent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
