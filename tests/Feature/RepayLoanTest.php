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

class RepayLoanTest extends TestCase
{
    use RefreshDatabase;

     /**
     * Repay Loan without login.
     *
     * @return void
     */
    public function test_repay_loan_without_login()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/v1/loan/1/repay');
        $response->assertStatus(401);
    }

     /**
     * Repay Loan with loan notfound .
     *
     * @return void
     */
    public function test_repay_loan_with_notofund()
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
        ])->post('/api/v1/loan/1/repay');
        $response->assertStatus(404);
    }

     /**
     * Repay Loan with loan empty input .
     *
     * @return void
     */
    public function test_repay_loan_with_empty_input()
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

          $loan=Loan::factory()->create([
             'user_id'=>$user->id,
             'status'=> Loan::ACCEPTED
          ]);

         $response = $this->withHeaders([
             'Accept' => 'application/json',
             'Content-Type' => 'application/json',
         ])->post("/api/v1/loan/$loan->id/repay");
         $response->assertStatus(422);
    }

     /**
     * Repay Loan with loan wrong input .
     *
     * @return void
     */
    public function test_repay_loan_with_wrong_input()
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

         $loan=Loan::factory()->create([
            'user_id'=>$user->id,
            'status'=> Loan::ACCEPTED
         ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>'amount'
        ]);
        $response->assertStatus(422);
    }



     /**
     * Repay Loan with loan unthorize access input .
     *
     * @return void
     */
    public function test_repay_loan_with_unauthorize_action()
    {
        // Run a specific seeder...
        $this->seed(UserSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('client');
        $user->save();


       $admin = User::whereHas("associatedRole", function ($query) {
           $query->where('name', 'super-admin');
       })->first();


       Passport::actingAs(
           $admin,
           ['*']
       );

        $loan=Loan::factory()->create([
           'user_id'=>$user->id,
           'status'=>  Loan::APPROVED
        ]);

       $response = $this->withHeaders([
           'Accept' => 'application/json',
           'Content-Type' => 'application/json',
       ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>'amount'
         ]);

       $response->assertStatus(403);
    }


     /**
     * Repay Loan with loan wrong emi amount .
     *
     * @return void
     */
    public function test_repay_loan_with_wrong_emi_amount()
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
         $principalAmount=10000;
         $interestRate=10;
         $tenure=5;

         $loanDetails=Loan::calculateLoan($principalAmount,$interestRate,$tenure);

         $loan=Loan::factory()->create([
            'user_id'=>$user->id,
            'status'=>  Loan::APPROVED,
            'principal_amount' => $principalAmount,
            'interest_rate'=>$interestRate,
            'tenure'=>$tenure,
            'total_repay_amount'=>$loanDetails['total_repay_amount'],
            'total_intrest'=>$loanDetails['total_intrest'],
            'loan_applied_date'=> Carbon::now()->subDays(1),
            'loan_accepted_date'=> Carbon::now(),
            'loan_agreement'=>Loan::ACCEPTED,
         ]);

         //::create loan emi
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


        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>200
        ]);
        $response->assertStatus(422);
    }


     /**
     * Repay completed Loan  .
     *
     * @return void
     */
    public function test_repay_compelted_loan()
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
        ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>$loanDetails['installment_amount']
        ]);
        $response->assertStatus(422);
    }

    /**
     * Repay Loan  Success.
     *
     * @return void
     */
    public function test_repay_loan_success()
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
            'status'=> Loan::APPROVED,
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
            ];
            LoanInstallment::storeLoanInstallment($installmentDetails);
        }


        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>$loanDetails['installment_amount']
        ]);
        $response->assertStatus(200);
    }


    /**
     * Repay Loan  Success With Diffirent Payment Getway.
     *
     * @return void
     */
    public function test_repay_loan_success_diff_paymentgetway()
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
            'status'=> Loan::APPROVED,
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
            ];
            LoanInstallment::storeLoanInstallment($installmentDetails);
        }


        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->json('POST', "/api/v1/loan/$loan->id/repay", [
            'amount'=>$loanDetails['installment_amount'],
            'payment_getway'=>'braintree',
        ]);
        $response->assertStatus(200);
    }
}
