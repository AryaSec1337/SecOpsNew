<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TyposquatLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'scan_date' => 'datetime',
        'is_registered' => 'boolean',
    ];
}
