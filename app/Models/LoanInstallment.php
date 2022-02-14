<?php

namespace App\Models;

use Carbon\Carbon;
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


     /**
     * Store Loan Installment to Database
     *
     * @param $installmentDetails array
     * @return $loanInstallment object
     */
    public static function storeLoanInstallment($installmentDetails){

        $loanInstallment= self::create([
            'amount'=>$installmentDetails['amount'],
            'due_date'=>$installmentDetails['due_date'],
            'status'=>self::PENDING
        ]);

        $loanInstallment->loan_id=$installmentDetails['loan_id'];
        $loanInstallment->save();
        return $loanInstallment;
    }

     /**
     * Update Loan Installment to Database
     *
     * @param $installment object
     * @return $loanInstallment object
     */
    public static function updateLoanInstallment($installment){

        $installment->status = self::PAID;
        $installment->paid_date = Carbon::now();
        return $installment->save();
    }

    /**
     * Get Loan Installment to details
     *
     * @param $loanId int
     * @return $loanInstallment object
     */
    public static function getLoanInstallment($loanId){
        return self::where('loan_id',$loanId)->where('status',self::PENDING)->first();
    }
}
