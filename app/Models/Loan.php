<?php

namespace App\Models;

use Carbon\Carbon;
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
        'principal_amount',
        'interest_rate',
        'tenure',
        'status',
        'total_repay_amount',
        'total_intrest',
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
    public static function calculateLoan($principal,$intrestRate,$tenure){

        $rate = $intrestRate/(12*100); //Monthly interest rate

        $installmentAmount = $principal * $rate * (pow(1 + $rate, $tenure) / (pow(1 + $rate, $tenure) - 1));
        $totalRePayAmount = round(($installmentAmount * $tenure), 2);
        $totalInterest = round(($installmentAmount * $tenure) - $principal, 2);


        $loanDetails=[
            'principal_amount'=>$principal,
            'tenure'=>$tenure,
            'interest_rate'=>$intrestRate,
            'installment_amount'=>round($installmentAmount,2),
            'total_repay_amount'=>$totalRePayAmount,
            'total_intrest'=>$totalInterest,
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

        $requestData=request()->all();
        $loanDetails=self::calculateLoan($requestData['amount'],$requestData['interest_rate'],$requestData['tenure']);

        $loan= self::create([
            'principal_amount'=>$loanDetails['principal_amount'],
            'interest_rate'=>$loanDetails['interest_rate'],
            'tenure'=>$loanDetails['tenure'],
            'total_repay_amount'=>$loanDetails['total_repay_amount'],
            'total_intrest'=>$loanDetails['total_intrest'],
            'status'=>self::APPROVAL_PENDING,
            'loan_applied_date'=>Carbon::now(),
            'loan_agreement'=>request()->loan_agreement,
        ]);

        $loan->user_id=auth()->user()->id;
        $loan->save();
        return $loan;
    }

     /**
     * Update loand status and store loan EMI details
     *
     * @param $loan object
     * @return $loan object
     */
    public static function approveLoan($loan){

        $result=DB::transaction(function () use ($loan) {

            $loanDetails=self::calculateLoan($loan->principal_amount,$loan->interest_rate,$loan->tenure);

            $loan->status = self::APPROVED;
            $loan->loan_accepted_date = Carbon::now();
            $loan->save();

            $loanDueDate    = $loan->loan_accepted_date;

            for($i=0; $i<$loan->tenure;$i++){
                $loanDueDate = $loanDueDate->addMonth();
                $installmentDetails=[
                    'loan_id'=>$loan->id,
                    'amount'=>$loanDetails['installment_amount'],
                    'due_date'=>$loanDueDate
                ];
                LoanInstallment::storeLoanInstallment($installmentDetails);
            }

            return $loan;
        });
        return $result;
    }

     /**
     * Update loan repayment details
     *
     * @param $loan object
     * @return $result boolean
     */
    public static function repayLoanAmount($loan){

        $result=DB::transaction(function () use ($loan) {

            $installment=LoanInstallment::getLoanInstallment($loan->id);
            $installment=LoanInstallment::updateLoanInstallment($installment);
            $nextInstallment=LoanInstallment::getLoanInstallment($loan->id);

            //::If no future installment pending marked loan completed
            if(!$nextInstallment){
                $loan->status = self::COMPLETED;
                $loan->loan_completed_date = Carbon::now();
                $loan->save();
            }

            return true;
        });
        return $result;
    }
}
