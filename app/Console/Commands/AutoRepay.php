<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\User;
use App\Models\LoanInstallment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Payment\PaymentGatewayContract;

class AutoRepay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:repay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto repayment user EMI';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PaymentGatewayContract $paymentGateway)
    {
        \Log::info("Autopay payment cron started");

        //::current date
        $currentDate=Carbon::now();

        //::Get loan details who have due date is qual to current date
        $loans = Loan::where('status',Loan::APPROVED)
        ->whereHas('installments',function($query) use($currentDate){
            $query->where('status',LoanInstallment::PENDING)
            ->whereDate('due_date',$currentDate);
        })->get();


        foreach($loans as $loan){
            //::repay loan amount to admin  using paymentgetway
            $sender=User::where('id',$loan->user_id)->first();
            $reciver=User::whereHas("associatedRole", function ($query) {
                $query->where('name', 'super-admin');
            })->first();
            $paymentDetails=$paymentGateway->charge($loan->principal_amount, $sender,$reciver);

            //::if payment fails
           //Ignoring this condition in test case as returing static result for now
           // @codeCoverageIgnoreStart
            if(!$paymentDetails['response']){
                \Log::info("Autopay payment for loan $loan->id at $currentDate faild");
            }
            // @codeCoverageIgnoreEnd

            $result=DB::transaction(function () use ($loan,$paymentDetails) {

                $installment=LoanInstallment::getLoanInstallment($loan->id);
                $installment=LoanInstallment::updateLoanInstallment($installment,$paymentDetails);
                $nextInstallment=LoanInstallment::getLoanInstallment($loan->id);
                //::If no future installment pending marked loan completed
                if(!$nextInstallment){
                    $loan->status = self::COMPLETED;
                    $loan->loan_completed_date = Carbon::now();
                    $loan->save();
                }
                return true;
            });
        }

        \Log::info("Autopay payment cron ended");

        return 0;
    }
}
