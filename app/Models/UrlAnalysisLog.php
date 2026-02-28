<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlAnalysisLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'url_hash',
        'analysis_id',
        'status',
        'result',
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
