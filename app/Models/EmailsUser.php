<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailsUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'display_name',
        'email_address',
        'recipient_type',
        'department',
    ];
}
