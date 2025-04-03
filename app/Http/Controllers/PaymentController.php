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

        $loan = Loan::findOrFail($request->loan_id);
        $amountToPay = $request->amount_paid;
        $orNo = $request->or_number;

        Payment::pay($loan, $amountToPay, $orNo, $request->date);

        return redirect('/borrowers/' . $loan->borrower_id)->with('success','Payment has been recorded successfully!');
    }
}
