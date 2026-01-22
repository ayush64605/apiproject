<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedIps extends Model
{
    protected $fillable = [
        "block_type",
        "ip_address",
        "domain",
        "purchase_code",
        "reason",
        "enrichment_data",
        "enrichment_updated_at",
        "attempts",
        "blocked_until",
        "is_manually_blocked",
    ];
}
