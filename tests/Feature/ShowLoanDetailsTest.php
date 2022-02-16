<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Loan;
use App\Models\User;
use Laravel\Passport\Passport;
use App\Models\LoanInstallment;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShowLoanDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Show Loan details without login.
     *
     * @return void
     */
    public function test_show_loan_without_login()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get('/api/v1/loan/1/details');
        $response->assertStatus(401);
    }


     /**
     * Show Loan details not found.
     *
     * @return void
     */
    public function test_show_loan_notfund()
    {
        // Run a specific seeder...
        $this->seed(UserSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('client');
        $user->save();


        Passport::actingAs(
            $user,
            ['*']
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get('/api/v1/loan/1/details');
        $response->assertStatus(404);
    }

      /**
     * Show Loan details success.
     *
     * @return void
     */
    public function test_show_loan_success()
    {

         // Run a specific seeder...
         $this->seed(UserSeeder::class);

         $user = User::factory()->create();
         $user->assignRole('client');
         $user->save();


         Passport::actingAs(
             $user,
             ['*']
         );

         //:: create loan
         $principalAmount=1000;
         $interestRate=10;
         $tenure=1;

         $loanDetails=Loan::calculateLoan($principalAmount,$interestRate,$tenure);

         $loan=Loan::factory()->create([
            'user_id'=>$user->id,
            'status'=> Loan::COMPLETED,
            'principal_amount' => $principalAmount,
            'interest_rate'=>$interestRate,
            'tenure'=>$tenure,
            'total_repay_amount'=>$loanDetails['total_repay_amount'],
            'total_intrest'=>$loanDetails['total_intrest'],
            'loan_applied_date'=> Carbon::now()->subDays(30),
            'loan_accepted_date'=> Carbon::now()->subDays(29),
            'loan_agreement'=>Loan::ACCEPTED,
         ]);

         //::create loan emi
         $loanDueDate    = $loan->loan_accepted_date;

        for($i=0; $i<$loan->tenure;$i++){
            $loanDueDate = $loanDueDate->addMonth();
            $installmentDetails=[
                'loan_id'=>$loan->id,
                'amount'=>$loanDetails['installment_amount'],
                'due_date'=>$loanDueDate,
                'status' => LoanInstallment::PAID,
                'paid_date' => Carbon::now(),
            ];
            LoanInstallment::storeLoanInstallment($installmentDetails);
        }

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get("/api/v1/loan/$loan->id/details");
        $response->assertStatus(200);
    }
}
