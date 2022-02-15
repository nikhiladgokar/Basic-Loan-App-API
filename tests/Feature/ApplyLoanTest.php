<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyLoanTest extends TestCase
{
    use RefreshDatabase;

     /**
     * Apply Loan without login.
     *
     * @return void
     */
    public function test_apply_loan_without_login()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/v1/loan/apply');
        $response->assertStatus(401);
    }

     /**
     * Apply Loan with no input field.
     *
     * @return void
     */
    public function test_apply_loan_without_input()
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
        ])->post('/api/v1/loan/apply');

        $response->assertStatus(422);
    }

     /**
     * Apply Loan with empty input field.
     *
     * @return void
     */
    public function test_apply_loan_with_wrong_input()
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
        ])->json('POST', '/api/v1/loan/apply', [
            'amount'=>'amount',
            'tenure'=>'months',
            'interest_rate'=>'2.3',
            'loan_agreement'=>true
        ]);

        $response->assertStatus(422);
    }


    /**
     * Apply Loan with unotherize accesss.
     *
     * @return void
     */
    public function test_apply_loan_with_unotherize_access()
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
        ])->json('POST', '/api/v1/loan/apply', [
            'amount'=>10000,
            'tenure'=>10,
            'interest_rate'=>10,
            'loan_agreement'=>'ACCEPTED'
        ]);

        $response->assertStatus(403);
    }


     /**
     * Apply Loan with success.
     *
     * @return void
     */
    public function test_apply_loan_with_success()
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
        ])->json('POST', '/api/v1/loan/apply', [
            'amount'=>10000,
            'tenure'=>10,
            'interest_rate'=>10,
            'loan_agreement'=>'ACCEPTED'
        ]);

        $response->assertStatus(201);
    }
}
