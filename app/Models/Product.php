<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        "item_id",
        "name",
        "bypass_url",
        "bypass_script",
        "thumbnail_link",
        "provider",
        "meta",
        "license_update",
        "serve_latest_updates",
        "download_multiple_version",
    ];
    public function versions(): HasMany
    {
        return $this->hasMany(ProductVersions::class, 'pid', 'item_id');
    }

}
