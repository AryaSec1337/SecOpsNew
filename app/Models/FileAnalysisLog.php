<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileAnalysisLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'result' => 'array',
        'yara_matches' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
