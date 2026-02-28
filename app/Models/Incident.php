<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the parent source model (ActivityLog or FileIntegrityLog).
     */
    public function source()
    {
        return $this->morphTo();
    }
}
