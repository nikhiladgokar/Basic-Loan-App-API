<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $loanDetails=Loan::calculateLoan();
        return response()->json(['message'=>'loan calculated successfully!','data'=>$loanDetails], 200);
    }

     /**
     * Apply Loan Action
     * Client Request loan to admin
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

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $loan=Loan::storeLoanDetails();
        return response()->json(['message'=>'your loan request submited successfully!'], 201);
    }
}
