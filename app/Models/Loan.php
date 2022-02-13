<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model
{
    use HasFactory;

    const APPROVAL_PENDING = 'APPROVAL_PENDING';              // Approval pending from admin
    const REJECTED         = 'REJECTED';                      // Loan rejected by admin
    const APPROVED       = 'APPROVED';                        // Loan approved
    const COMPLETED        = 'COMPLETED';                     // Loan completed

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'requested_amount',
        'rate_of_interest',
        'duration',
        'status',
        'loan_applied_date',
        'loan_accepted_date',
        'loan_rejected_date',
        'loan_completed_date',
        'loan_agreement',
    ];


     /**
     * one to many relationship of loan to installments
     *
     * @return HasMany
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'loan_id', 'id');
    }

    /**
     * loan is belonging to user
     * one to many
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
