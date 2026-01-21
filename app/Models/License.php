<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class License extends Model
{
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
