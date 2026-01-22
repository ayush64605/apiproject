<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVersions extends Model
{
    protected $fillable = [
        "vid",
        "pid",
        "version",
        "release_date",
        "summary",
        "changelog",
        "bug",
        "improvement",
        "feature",
        "main_file",
        "regular_main_file",
        "extended_main_file",
        "sql_file",
        "regular_sql_file",
        "extended_sql_file",
        "status",
    ];
}
