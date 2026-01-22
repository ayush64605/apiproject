<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    protected $fillable = [
        "purchase_code",
        "domain",
        "endpoint",
        "method",
        "ip_address",
        "user_agent",
        "request_data",
        "response_code",
        "response_time",
    ];
}
