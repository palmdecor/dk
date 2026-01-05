<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'total_files',
        'processed_files',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(JobFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
