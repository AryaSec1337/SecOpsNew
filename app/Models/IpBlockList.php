<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpBlockList extends Model
{
    /** @use HasFactory<\Database\Factories\IpBlockListFactory> */
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'source',
        'description',
        'dest_ip',
        'dest_port',
        'proto',
        'signature_severity',
        'reason',
        'week_number',
        'year',
        'status',
    ];
}
