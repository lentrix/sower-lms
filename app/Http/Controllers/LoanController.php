<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Category;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\LoanPlan;
use App\Models\PaymentSchedule;
use App\Models\Penalty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

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
            $loan->released_at = now();
            $loan->save();
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
            'payment_schedules' => $request->plan['payment_schedules'],
            'plan_type' => $request->plan['plan_type']
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

    public function updateReleaseDate(Request $request) {
        $loan = Loan::findOrFail($request->loan_id);

        $loan->released_at = $request->released_at;
        $loan->save();

        return back()->with('success','The release date of this loan has been updated successfully.');
    }

    public function rebuildPaymentSchedule(Loan $loan) {

        //check penalties
        $penalties = Penalty::whereHas('paymentSchedule', function($q1) use ($loan) {
            $q1->where('loan_id', $loan->id);
        })->count();

        if($penalties>0) return back()->with('error','This loan contains penalties. Payment Schedule rebuild cannot proceed safely.');

        LoanPayment::whereHas('payment', function($q1) use($loan) { $q1->where('loan_id', $loan->id);})->delete();

        PaymentSchedule::where('loan_id', $loan->id)->delete();

        $loan->generatePaymentSchedules();


        //rebuild payments
        foreach($loan->payments as $pmt) {

            $amountToPay = $pmt->amount;

            foreach($loan->paymentSchedules as $psched) {
                if($amountToPay==0) break;

                $balance = $psched->amount_due - $psched->loanPayments->sum('amount');

                if($balance == 0) continue;

        $payAmount = (float)($amountToPay>$balance ? $balance : $amountToPay);

                $computations = $loan->computations();

                $intPct = $computations['interestPortionPerPaymentPercentage'];


                $interest = round($payAmount * $intPct, 2);
                $principal = round($payAmount - $interest, 2);

                $lp = LoanPayment::create([
                    'payment_id' => $pmt->id,
                    'payment_schedule_id' => $psched->id,
                    'amount' => $payAmount,
                    'interest' => $interest,
                    'principal' => $principal
                ]);

                $psched->refresh();

                $amountToPay -= $payAmount;
            }


        }

        return back();
    }

    public function paymentHistory(Loan $loan) {

        $loan->load('payments');

        return inertia('Borrowers/LoanPaymentHistory',[
            'loan' => $loan,
            'loanHistory' => $loan->borrower->loans->load('loanPlan')
        ]);
    }
}
