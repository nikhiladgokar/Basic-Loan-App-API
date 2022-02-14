<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Rules\CheckRepaymentAmount;
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
    public function approve($loanId)
    {
        //::Only admin can apply loan
        $this->authorize('canApprove', Loan::class);

        $loan=Loan::findOrFail($loanId);

        //::If loan request is not pending for approval
        if($loan->status !== Loan::APPROVAL_PENDING){
            return response()->json(['message'=>"Loan request is already $loan->status."], 422);
        }

        //::update loan details
        $loan=Loan::approveLoan($loan);

        return response()->json(['message'=>'Loan request approved successfully!'], 200);
    }


     /**
     * Repay Loan Action
     * Only client can repay repective loan to admin
     *
     * @param $amount integer
     * @return @json
     */
    public function repay(Request $request,$loanId)
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

        //::uopdate loan repayment details
        $loan=Loan::repayLoanAmount($loan);
        return response()->json(['message'=>'your loan repayment submited successfully!'], 200);
    }
}
