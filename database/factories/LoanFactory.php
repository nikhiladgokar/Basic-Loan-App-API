<?php

namespace Database\Factories;

use Carbon\Carbon;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
         $principalAmount=$this->faker->numberBetween(100,100000);
         $interestRate=$this->faker->numberBetween(1,100);
         $tenure=$this->faker->numberBetween(1,10);

         $loanDetails=Loan::calculateLoan($principalAmount,$interestRate,$tenure);

        return [
            'principal_amount' => $principalAmount,
            'interest_rate'=>$interestRate,
            'tenure'=>$tenure,
            'status'=>Loan::APPROVAL_PENDING,
            'total_repay_amount'=>$loanDetails['total_repay_amount'],
            'total_intrest'=>$loanDetails['total_intrest'],
            'loan_applied_date'=> Carbon::now(),
            'loan_agreement'=>Loan::ACCEPTED,
        ];
    }
}
