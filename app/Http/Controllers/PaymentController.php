<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Payment;
use App\Models\PenaltyPayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index() {
        $payees = DB::table('borrowers')
                ->join('loans','loans.borrower_id','=','borrowers.id')
                ->where('loans.status','=',2)
                ->select('borrowers.id', 'last_name','first_name','middle_name')->get();

        $payments = Payment::orderBy('created_at','DESC')->limit(50)->get()->map(function($pmt) {
            return [
                'date' => $pmt->date->format('M d, Y'),
                'orno' => $pmt->or_number,
                'payee' => $pmt->loan->borrower->last_name . ", " . $pmt->loan->borrower->first_name,
                'amount' => $pmt->amount
            ];
        });

        return inertia('Payments/Index',[
            'payees' => $payees,
            'payments' => $payments
        ]);
    }

    public function pay(Borrower $borrower) {
        $payees = DB::table('borrowers')->select('id', 'last_name','first_name','middle_name')->get();
        $payments = Payment::orderBy('created_at','DESC')->limit(50)->get()->map(function($pmt) {
            return [
                'date' => $pmt->date->format('M d, Y'),
                'orno' => $pmt->or_number,
                'payee' => $pmt->loan->borrower->last_name . ", " . $pmt->loan->borrower->first_name,
                'amount' => $pmt->amount
            ];
        });

        return inertia('Payments/Index',[
            'payees' => $payees,
            'selectedPayee' => $borrower,
            'payablePenalties' => [
                'total' => $borrower->activeLoan->payablePenalties->sum('amount'),
                'count' => $borrower->activeLoan->payablePenalties->count()
            ],
            'unPaidSchedules' => [
                'total' => $borrower->activeLoan->getUnPaidPastDueSchedules()->sum('amount_due'),
                'count' => $borrower->activeLoan->getUnPaidPastDueSchedules()->count()
            ],
            'balance' => $borrower->activeLoan->balance,
            'payments' => $payments
        ]);
    }

    public function store(Request $request) {

        // dd($request->all());

        $loan = Loan::findOrFail($request->loan_id);

        $amountToPay = $request->amount_paid;
        $orNo = $request->or_number;

        DB::beginTransaction();

        try {

            $pmt = Payment::create([
                'loan_id' => $loan->id,
                'or_number' => $orNo,
                'amount' => $amountToPay,
                'date' => $request->date
            ]);

            // $unsettledLoan = $loan->getUnsettledPaymentSchedules();
            $unsettledPenalty = $loan->getUnsettledPenalties();

            foreach($unsettledPenalty as $unP) {
                if($amountToPay==0) break;

                $payAmount = $amountToPay >= $unP['balance'] ? $unP['balance'] : $amountToPay;

                PenaltyPayment::create([
                    'payment_id' => $pmt->id,
                    'penalty_id' => $unP['penalty']->id,
                    'amount' => $payAmount
                ]);

                $amountToPay-=$payAmount;
            }

            foreach($loan->paymentSchedules as $psched) {
                if($amountToPay==0) break;

                $balance = $psched->amount_due - $psched->loanPayments->sum('amount');
                if($balance == 0) continue;

                $payAmount = (float)($amountToPay>$balance ? $balance : $amountToPay);

                $computations = $loan->computations();
                $intPct = $computations['interestPortionPerPaymentPercentage'];

                $interest = bcdiv($payAmount * $intPct, 1, 2);
                $principal = bcdiv($payAmount - $interest, 1, 2);

                LoanPayment::create([
                    'payment_id' => $pmt->id,
                    'payment_schedule_id' => $psched->id,
                    'amount' => $payAmount,
                    'interest' => $interest,
                    'principal' => $principal
                ]);

                $amountToPay -= $payAmount;
            }

            DB::commit();

        }catch(Exception $ex) {
            DB::rollBack();
            dd($ex);
        }

        return redirect('/borrowers/' . $loan->borrower_id)->with('success','Payment has been recorded successfully!');
    }
}
