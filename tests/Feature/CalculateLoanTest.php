<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculateLoanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Calculate Loan without login.
     *
     * @return void
     */
    public function test_calculate_loan_without_login()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->get('/api/v1/loan/calculate');
        $response->assertStatus(401);
    }

     /**
     * Calculate Loan with no input field.
     *
     * @return void
     */
    public function test_calculate_loan_without_input()
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
        ])->get('/api/v1/loan/calculate');

        $response->assertStatus(422);
    }

    /**
     * Calculate Loan with empty input field.
     *
     * @return void
     */
    public function test_calculate_loan_with_wrong_input()
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
        ])->get('/api/v1/loan/calculate',[
            'amount'=>'amount',
            'tenure'=>'months',
            'interest_rate'=>'2.3'
        ]);

        $response->assertStatus(422);
    }

     /**
     * Calculate Loan with success.
     *
     * @return void
     */
    public function test_calculate_loan_with_success()
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
        ])->get("api/v1/loan/calculate?amount=100000&tenure=36&interest_rate=12");

        $response->assertStatus(200);
    }
}
