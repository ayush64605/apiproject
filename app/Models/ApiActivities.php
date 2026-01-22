<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiActivities extends Model
{
    protected $fillable = [
        "purchase_code",
        "item_id",
        "event_type",
        "domain",
        "user_id",
        "payload",
    ];
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'item_id', 'item_id');
    }
}
