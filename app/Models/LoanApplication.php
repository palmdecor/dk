<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'national_id',
        'monthly_income',
        'employment_status',
        'loan_amount',
        'loan_term',
        'phone',
        'email',
        'notes',
        'status',
        'kvkk_approved',
    ];

    protected $casts = [
        'monthly_income' => 'float',
        'loan_amount' => 'float',
        'kvkk_approved' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
