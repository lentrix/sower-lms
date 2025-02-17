<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanPlan;
use App\Models\LoanType;
use App\Models\PaymentSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'categories' => Category::orderBy('name')->get(),
            'loan_plans' => config('sower.loan_plans'),
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'ref_no' => 'string|required',
            'category_id' => 'numeric|required',
            'plan' => 'required',
            'amount' => 'numeric|required',
            'transaction_fee'=>'numeric|required',
        ]);

        // dd($request->all());

        $category = Category::findOrFail($request->category_id);

        $amount = str_replace(",","", $request->amount);
        $month = $request->plan['month'];
        $paymentScheds = $request->plan['payment_schedules'];

        // $intRate = $category->interest_rate/100;
        // $interest = $amount * ($intRate*$month);

        // $payable = $amount + $interest;

        $plan = LoanPlan::create([
            'month' => $request->plan['month'],
            'category_id' => $category->id,
            'interest' => $category->interest_rate,
            'penalty' => $request->plan['penalty'],
            'payment_schedules' => $paymentScheds,
            'plan_type' => $request->plan['plan_type']
        ]);

        Loan::create([
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
            'categories' => Category::orderBy('name')->get(),
            'loan_plans' => config('sower.loan_plans')
        ]);
    }

    public function update(Loan $loan, Request $request) {
        $request->validate([
            'ref_no' => 'string|required',
            'category_id' => 'numeric|required',
            'plan' => 'required',
            'amount' => 'numeric|required',
            'transaction_fee'=>'numeric|required',
        ]);

        $loan->update($request->only('ref_no','purpose','amount','transaction_fee'));

        $category = Category::findOrFail($request->category_id);

        $loan->loanPlan->update([
            'month' => $request->plan['month'],
            'category_id' => $request->category_id,
            'interest' => $category->interest_rate,
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

    public function syncWithBalance(Loan $loan) {
        $paidPaymentSchedules = PaymentSchedule::where('loan_id', $loan->id)
                ->whereHas('loanPayments')->get();

        foreach($paidPaymentSchedules as $pps) {
            $pps->update(['amount_due'=>$pps->loanPayments->sum('amount')]);
        }

        $syncablePaymentSchedules = PaymentSchedule::whereDoesntHave('loanPayments')
            ->where('loan_id', $loan->id);

        $newAmortization = $loan->balance / $syncablePaymentSchedules->count();

        $syncablePaymentSchedules->update([
            'amount_due' => $newAmortization
        ]);

        return redirect('/borrowers/' . $loan->borrower->id)->with('success','The remaining payment schedules have been synced with the balance.');

    }

    public function export(Loan $loan) {
        return Pdf::loadView('pdf.loan-details', compact('loan'))
                ->setPaper('Legal')
                ->stream();
    }
}
