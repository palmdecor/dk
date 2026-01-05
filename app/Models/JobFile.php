<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobFile extends Model
{
    protected $fillable = [
        'job_id',
        'original_path',
        'processed_path',
        'status',
        'error_message',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
