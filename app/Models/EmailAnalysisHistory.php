<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAnalysisHistory extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'sender',
        'recipient',
        'score',
        'risk_level',
        'results'
    ];

    protected $casts = [
        'results' => 'array',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
