<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetLicenseActivityLogs extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'purchase_code',
        'reset_license_time',
        'type',
        'reset_by'
    ];

    protected $casts = [
        'reset_license_time' => 'datetime',
    ];
}
