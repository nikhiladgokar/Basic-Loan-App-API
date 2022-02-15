<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\CheckRepaymentAmount;
use App\Payment\PaymentGatewayContract;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
     /**
     * Calculate Loan
     * Calculate and return loan details
     *
     * @param $amount integer
     * @param $tenure integer
     * @param $interest_rate integer in percentage
     * @return @json
     */
    public function calculate(Request $request)
    {
        $rules = [
            'amount' => 'required|integer|min:100',
            'tenure' => 'required|integer|min:1',
            'interest_rate' => 'required|integer|max:100',
        ];

        //::check validations
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //::calculate loan
        $requestData=$request->all();
        $loanDetails=Loan::calculateLoan($requestData['amount'],$requestData['interest_rate'],$requestData['tenure']);
        return response()->json(['message'=>'loan calculated successfully!','data'=>$loanDetails], 200);
    }

     /**
     * Apply Loan Action
     * Only client can request loan to admin
     *
     * @param $amount integer
     * @param $tenure integer
     * @param $interest_rate integer in percentage
     * @return @json
     */
    public function apply(Request $request)
    {
        //::Only client user can apply loan
        $this->authorize('canApply', Loan::class);

        $rules = [
            'amount' => 'required|integer|min:100',
            'tenure' => 'required|integer|min:1',
            'interest_rate' => 'required|integer|max:100',
            'loan_agreement' => ['required',Rule::in([Loan::ACCEPTED])],
        ];

        //::check validations
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //::store loan details
        $loan=Loan::storeLoanDetails();
        return response()->json(['message'=>'your loan request submited successfully!'], 201);
    }

    /**
     * Approve loan action and transfer loan amount
     * Only admin can approve loan
     *
     * @param $loanId integer
     */
    public function approve($loanId,PaymentGatewayContract $paymentGateway)
    {
        //::Only admin can apply loan
        $this->authorize('canApprove', Loan::class);

        $loan=Loan::findOrFail($loanId);

        //::If loan request is not pending for approval
        if($loan->status !== Loan::APPROVAL_PENDING){
            return response()->json(['message'=>"Loan request is already $loan->status."], 422);
        }

        //::Pay approved amount to client  using paymentgetway
        $sender=auth()->user();
        $reciver=User::where('id',$loan->user_id)->first();
        $paymentDetails=$paymentGateway->charge($loan->principal_amount, $sender,$reciver);

        //::if payment fails
        if(!$paymentDetails['response']){
            return response()->json(['message'=>"There is problem to process payment, please try after some time"], 500);
        }

        //::update loan details and payment details
        $loan=Loan::approveLoan($loan,$paymentDetails);
        return response()->json(['message'=>'Loan request approved successfully!'], 200);

    }


     /**
     * Repay Loan Action
     * Only client can repay repective loan to admin
     *
     * @param $amount integer
     * @return @json
     */
    public function repay($loanId,Request $request,PaymentGatewayContract $paymentGateway)
    {
        //::Only client user can apply loan
        $loan=Loan::findOrFail($loanId);
        $this->authorize('canRepay', [Loan::class,$loan]);

        $rules = [
            'amount' => ['required','numeric',new CheckRepaymentAmount($loanId)],
        ];

        //::check validations
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //::If loan request is not approved
        if($loan->status !== Loan::APPROVED){
            return response()->json(['message'=>"Loan request is already $loan->status."], 422);
        }

        //::repay loan amount to admin  using paymentgetway
         $sender=auth()->user();
         $reciver=User::whereHas("associatedRole", function ($query) {
            $query->where('name', 'super-admin');
        })->first();
         $paymentDetails=$paymentGateway->charge($loan->principal_amount, $sender,$reciver);

         //::if payment fails
         if(!$paymentDetails['response']){
             return response()->json(['message'=>"There is problem to process payment, please try after some time"], 500);
         }

        //::uopdate loan repayment details
        $loan=Loan::repayLoanAmount($loan,$paymentDetails);
        return response()->json(['message'=>'your loan repayment submited successfully!'], 200);
    }


    /**
     * Get Loan details with installments details
     * Only respective client or admin can see
     *
     * @param $loanId integer
     */
    public function show($loanId)
    {
        //::Only admin can apply loan
        $loan=Loan::with('installments')->findOrFail($loanId);
        $this->authorize('canSeeLoan', [Loan::class,$loan]);

        return response()->json(['message'=>'Loan details fetch successfully!','data'=>$loan], 200);
    }
}
