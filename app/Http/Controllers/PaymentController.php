<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Payment;
use App\Models\PenaltyPayment;
use App\Models\SystemLog;
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

    public function update(Request $request, Payment $payment) {

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'or_number' => 'nullable|string|max:25',
            'date' => 'required|date',
        ]);

        DB::transaction(function() use ($payment, $validated) {

            $old = [
                'amount' => (float) $payment->amount,
                'or_number' => $payment->or_number,
                'date' => $payment->date->format('Y-m-d'),
            ];

            $payment->update($validated);

            Payment::reapplyForLoan($payment->loan);

            $new = [
                'amount' => (float) $payment->amount,
                'or_number' => $payment->or_number,
                'date' => $payment->date->format('Y-m-d'),
            ];

            SystemLog::record(
                'payment.updated',
                sprintf(
                    'Updated payment #%d (OR#%s) of %s - amount %s to %s, date %s to %s.',
                    $payment->id,
                    $new['or_number'] ?: 'n/a',
                    $this->payeeName($payment),
                    number_format($old['amount'],2),
                    number_format($new['amount'],2),
                    $old['date'],
                    $new['date']
                ),
                $payment,
                ['old' => $old, 'new' => $new]
            );
        });

        return back()->with('success','The payment has been updated successfully.');
    }

    public function destroy(Payment $payment) {

        DB::transaction(function() use ($payment) {

            $loan = $payment->loan;

            $deleted = [
                'id' => $payment->id,
                'loan_id' => $payment->loan_id,
                'amount' => (float) $payment->amount,
                'or_number' => $payment->or_number,
                'date' => $payment->date->format('Y-m-d'),
            ];

            $payeeName = $this->payeeName($payment);

            PenaltyPayment::where('payment_id', $payment->id)->delete();
            LoanPayment::where('payment_id', $payment->id)->delete();

            $payment->delete();

            Payment::reapplyForLoan($loan);

            SystemLog::record(
                'payment.deleted',
                sprintf(
                    'Deleted payment #%d (OR#%s) of %s dated %s in the amount of %s.',
                    $deleted['id'],
                    $deleted['or_number'] ?: 'n/a',
                    $payeeName,
                    $deleted['date'],
                    number_format($deleted['amount'],2)
                ),
                $payment,
                ['deleted' => $deleted]
            );
        });

        return back()->with('success','The payment has been deleted successfully.');
    }

    private function payeeName(Payment $payment) {
        $borrower = $payment->loan?->borrower;

        if(!$borrower) return 'Unknown Borrower';

        return $borrower->last_name . ', ' . $borrower->first_name;
    }
}
