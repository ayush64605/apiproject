<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class License extends Model
{

    protected $fillable = [
        "item_id",
        "item_name",
        "purchase_code",
        "installed_version",
        "purchase_time",
        "buyer",
        "buyer_email",
        "activated_domain",
        "license",
        "user_agent",
        "ip",
        "os",
        "purchase_count",
        "status",
        "hash",
        "decryption_key",
        "last_validate_request"
    ];
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id', 'item_id');
    }

    public function availableVersions(): HasManyThrough
    {
        return $this->hasManyThrough(ProductVersions::class, Product::class, 'item_id', 'pid', 'item_id', 'item_id');
    }

    public function resetLogs(): HasMany
    {
        return $this->hasMany(ResetLicenseActivityLogs::class, 'purchase_code', 'purchase_code');
    }
}
