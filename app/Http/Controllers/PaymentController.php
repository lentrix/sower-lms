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

        return inertia('Payments/Index',[
            'payees' => $payees,
        ]);
    }

    public function pay(Borrower $borrower) {
        $payees = DB::table('borrowers')->select('id', 'last_name','first_name','middle_name')->get();

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
            'balance' => $borrower->activeLoan->getBalance()
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
                'date' => now()
            ]);

            $unsettledLoan = $loan->getUnsettledPaymentSchedules();
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

                $payAmount = $amountToPay>$balance ? $balance : $amountToPay;

                LoanPayment::create([
                    'payment_id' => $pmt->id,
                    'payment_schedule_id' => $psched->id,
                    'amount' => $payAmount,
                    'interest' => 0,
                    'principal' => 0
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
