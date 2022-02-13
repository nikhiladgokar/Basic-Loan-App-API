<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoanInstallment extends Model
{
    use HasFactory;

    const PAID       = 'PAID';                 // EMI paid/completed status
    const PENDING    = 'PENDING';              // EMI pending status

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'due_date',
        'paid_date',
        'status',
    ];

    /**
     * installment belongs to loan
     * one to many
     *
     * @return BelongsTo
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }
}
