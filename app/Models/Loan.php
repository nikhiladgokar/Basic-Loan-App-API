<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
    const ACCEPTED        = 'ACCEPTED';                       // Aggriment Accepted

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'principal_ampunt',
        'interest_rate',
        'tenure',
        'status',
        'total_repay_amount',
        'final_amount',
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

     /**
     * Calculate Loan Details
     * @param $request object
     * @return $loanDeatils array
     */
    public static function calculateLoan(){
        $requestData=request()->all();
        $principal = $requestData['amount'];
        $rate = $requestData['interest_rate']/(12*100); //Monthly interest rate
        $tenure = $requestData['tenure']; // Term in months

        $installmentAmount = $principal * $rate * (pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1));
        $totalRePayAmount = round(($installmentAmount * $tenure), 2);
        $totalInterest = round(($installmentAmount * $tenure) - $principal, 2);


        $loanDetails=[
            'principal_ampunt'=>$principal,
            'tenure'=>$tenure,
            'interest_rate'=>$requestData['interest_rate'],
            'installment_amount'=>round($installmentAmount,2),
            'total_repay_amount'=>$totalRePayAmount,
            'final_amount'=>$totalInterest,
        ];

        return $loanDetails;
    }
     /**
     * Store Loan Details in to Database
     * Client Request loan to admin
     *
     * @return $loan boject
     */
    public static function storeLoanDetails(){

        $loanDetails=self::calculateLoan();
        $loan= self::create([
            'principal_ampunt'=>$loanDetails['principal_ampunt'],
            'interest_rate'=>$loanDetails['interest_rate'],
            'tenure'=>$loanDetails['tenure'],
            'total_repay_amount'=>$loanDetails['total_repay_amount'],
            'final_amount'=>$loanDetails['final_amount'],
            'status'=>self::APPROVAL_PENDING,
            'loan_applied_date'=>now(),
            'loan_agreement'=>request()->loan_agreement,
        ]);

        $loan->user_id=auth()->user()->id;
        $loan->save();
        return $loan;
    }
}
