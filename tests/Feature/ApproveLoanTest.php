<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Loan;
use App\Models\User;
use Laravel\Passport\Passport;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApproveLoanTest extends TestCase
{
    use RefreshDatabase;

    /**
    * Approve Loan without login.
    *
    * @return void
    */
   public function test_approve_loan_without_login()
   {
       $response = $this->withHeaders([
           'Accept' => 'application/json',
           'Content-Type' => 'application/json',
       ])->post('/api/v1/loan/1/approve');
       $response->assertStatus(401);
   }

    /**
     * Approve Loan with lon notfound .
     *
     * @return void
     */
    public function test_approve_loan_with_notofund()
    {
        // Run a specific seeder...
        $this->seed(UserSeeder::class);

        $admin = User::whereHas("associatedRole", function ($query) {
            $query->where('name', 'super-admin');
        })->first();


        Passport::actingAs(
            $admin,
            ['*']
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/v1/loan/1/approve');
        $response->assertStatus(404);
    }

    /**
     * Loan approval not pending for approval.
     *
     * @return void
     */
    public function test_approve_loan_not_approval_pending()
    {
        // Run a specific seeder...
        $this->seed(UserSeeder::class);

        $admin = User::whereHas("associatedRole", function ($query) {
            $query->where('name', 'super-admin');
        })->first();


        Passport::actingAs(
            $admin,
            ['*']
        );

        $loan=Loan::factory()->create([
           'status'=> Loan::ACCEPTED
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("/api/v1/loan/$loan->id/approve");

        $response->assertStatus(422);
    }


    /**
     * Approve Loan with success.
     *
     * @return void
     */
    public function test_approve_loan_with_success()
    {
        // Run a specific seeder...
        $this->seed(UserSeeder::class);

        $admin = User::whereHas("associatedRole", function ($query) {
            $query->where('name', 'super-admin');
        })->first();


        Passport::actingAs(
            $admin,
            ['*']
        );

        $loan=Loan::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("/api/v1/loan/$loan->id/approve");

        $response->assertStatus(200);
    }

}
