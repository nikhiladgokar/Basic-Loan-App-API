<?php

namespace App\Rules;

use App\Models\LoanInstallment;
use Illuminate\Contracts\Validation\Rule;

class CheckRepaymentAmount implements Rule
{
    private $loanId;
    private $isInstallmentPending;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($loanId)
    {
        $this->loanId = $loanId;
        $this->isInstallmentPending = true;

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $installment=LoanInstallment::getLoanInstallment($this->loanId);
        //::check installment pending or not
        if(!$installment){
            $this->isInstallmentPending= false;
        }if($installment->amount == $value){
            //::check provide amount is equal or not
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->isInstallmentPending){
            return 'The installment amount did not match with provided amount.';
        }else{
            return 'There is no installment pending.';
        }
    }
}
