<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileIntegrityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'change_type',
        'process_name',
        'user',
        'hash_before',
        'hash_after',
        'severity',
        'detected_at',
        'details'
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'details' => 'array',
    ];
}
