<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RansomwareVictim extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'infostealer_data' => 'array',
        'discovered_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
