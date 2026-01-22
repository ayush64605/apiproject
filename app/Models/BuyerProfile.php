<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerProfile extends Model
{
    protected $fillable = [
        "envato_username",
        "email",
        "first_name",
        "last_name",
        "display_name",
        "envato_user_id",
        "country",
        "city",
        "profile_image",
        "profile_url",
        "total_purchases",
        "total_spent",
        "first_purchase_date",
        "last_purchase_date",
        "email_marketing_consent",
        "is_active_buyer",
        "preferred_language",
        "email_verified_at",
        "last_updated_from_api",
        "api_fetch_attempts",
        "last_api_fetch_attempt",
    ];
}
