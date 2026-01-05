<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'watermark_path',
        'position_mode',
        'custom_x',
        'custom_y',
        'scale_mode',
        'scale_value',
        'opacity',
        'padding',
        'max_upload_mb',
        'output_quality',
    ];
}
