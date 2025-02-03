<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanPlan;
use App\Models\LoanType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index() {
        return inertia('Loans/Index');
    }

    public function create(Borrower $borrower=null) {
        return inertia('Loans/Create', [
            'borrower' => $borrower,
            'loan_types' => LoanType::orderBy('name')->get(),
            'loan_plans' => config('sower.loan_plans'),
            'interest_rates' => config('sower.interest_rates')
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'ref_no' => 'string|required',
            'interest_rate' => 'numeric|required',
            'plan' => 'required',
            'amount' => 'numeric|required',
            'transaction_fee'=>'numeric|required',
        ]);

        // dd($request->all());

        $loanTypeId = null;
        switch($request->interest_rate) {
            case 3 : $loanTypeId = 1; break;
            case 6 : $loanTypeId = 2; break;
            case 4 : $loanTypeId = 3; break;
        }

        $amount = str_replace(",","", $request->amount);
        $month = $request->plan['month'];
        $paymentScheds = $request->plan['payment_schedules'];

        $intRate = $request->interest_rate/100;
        $interest = $amount * ($intRate*$month);

        $payable = $amount + $interest;

        $plan = LoanPlan::create([
            'month' => $request->plan['month'],
            'loan_type_id' => $loanTypeId,
            'interest' => $request->interest_rate,
            'penalty' => $request->plan['penalty'],
            'payment_schedules' => $paymentScheds
        ]);

        $loan = Loan::create([
            'ref_no'            => $request->ref_no,
            'loan_plan_id'      => $plan->id,
            'borrower_id'       => $request->borrower_id,
            'purpose'           => $request->purpose,
            'amount'            => $request->amount,
            'transaction_fee'   => $request->transaction_fee,
            'status'            => 0
        ]);

        return redirect('/borrowers/' . $request->borrower_id)->with('success','A new loan has been created for this borrower.');
    }

    public function setStatus($status, Loan $loan) {
        $loan->update(['status'=>$status]);

        if($status==2) {
            $loan->generatePaymentSchedules();
        }

        return redirect('/borrowers/' . $loan->borrower->id)->with('success',"This loan's status has been set to " . config('sower.status_names')[$status]);
    }

    public function edit(Loan $loan) {

        return inertia('Loans/Edit', [
            'loan' => $loan,
            'borrower' => $loan->borrower,
            'loan_types' => LoanType::orderBy('name')->get(),
            'loan_plans' => config('sower.loan_plans'),
            'interest_rates' => config('sower.interest_rates')
        ]);
    }

    public function update(Loan $loan, Request $request) {
        $request->validate([
            'ref_no' => 'string|required',
            'interest_rate' => 'numeric|required',
            'plan' => 'required',
            'amount' => 'numeric|required',
            'transaction_fee'=>'numeric|required',
        ]);

        $loanTypeId = null;
        switch($request->interest_rate) {
            case 3 : $loanTypeId = 1; break;
            case 6 : $loanTypeId = 2; break;
            case 4 : $loanTypeId = 3; break;
        }

        $loan->update($request->only('ref_no','purpose','amount','transaction_fee'));

        $loan->loanPlan->update([
            'month' => $request->plan['month'],
            'loan_type_id' => $loanTypeId,
            'interest' => $request->interest_rate,
            'penalty' => $request->plan['penalty'],
            'payment_schedules' => $request->plan['payment_schedules']
        ]);

        return redirect('/borrowers/' . $loan->borrower->id)->with('success','The loan has been updated.');
    }

    public function resyncAmortization(Loan $loan) {
        DB::beginTransaction();
        foreach($loan->paymentSchedules as $ps) {
            $ps->amount_due = $ps->loan->amortization;
            $ps->save();
        }
        DB::commit();

        return redirect('/borrowers/' . $loan->borrower_id)->with('success','The amortizations has been resynced');
    }
}
