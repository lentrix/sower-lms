<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
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
                'total' => $borrower->activeLoan->getUnPaidSchedules()->sum('amount_due'),
                'count' => $borrower->activeLoan->getUnPaidSchedules()->count()
            ]
        ]);
    }

    public function store(Request $request) {
        $loan = Loan::findOrFail($request->loan_id);

        $paidAmount = $request->amount_paid;
        $orNo = $request->or_number;

        $unsettled = $loan->getUnsettledPaymentSchedules();


    }
}
