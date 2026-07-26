<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public function loanPayments() {
        return $this->hasMany(LoanPayment::class);
    }

    public function penaltyPayments() {
        return $this->hasMany(PenaltyPayment::class);
    }

    public static function pay(Loan $loan, $amountToPay, $orNo, $date) {

        try {

            $pmt = Payment::create([
                'loan_id' => $loan->id,
                'or_number' => $orNo,
                'amount' => $amountToPay,
                'date' => $date
            ]);

            $pmt->allocate();

        }catch(Exception $ex) {
            dd($ex);
        }
    }

    /**
     * Spread this payment's amount over the loan's unsettled penalties first,
     * then over its payment schedules in due date order.
     */
    public function allocate() {

        $loan = $this->loan;

        $amountToPay = $this->amount;

        foreach($loan->getUnsettledPenalties() as $unP) {
            if($amountToPay <= 0.001) break;

            $payAmount = $amountToPay >= $unP['balance'] ? $unP['balance'] : $amountToPay;

            PenaltyPayment::create([
                'payment_id' => $this->id,
                'penalty_id' => $unP['penalty']->id,
                'amount' => $payAmount
            ]);

            $amountToPay-=$payAmount;
        }

        $computations = $loan->computations();

        $intPct = $computations['interestPortionPerPaymentPercentage'];

        foreach($loan->paymentSchedules()->get() as $psched) {
            if($amountToPay <= 0.001) break;

            $balance = $psched->amount_due - $psched->loanPayments->sum('amount');

            if($balance <= 0.001) continue;

            $payAmount = (float)($amountToPay>$balance ? $balance : $amountToPay);

            $interest = round($payAmount * $intPct, 2);
            $principal = round($payAmount - $interest, 2);

            LoanPayment::create([
                'payment_id' => $this->id,
                'payment_schedule_id' => $psched->id,
                'amount' => $payAmount,
                'interest' => $interest,
                'principal' => $principal
            ]);

            $psched->refresh();

            $amountToPay -= $payAmount;
        }
    }

    /**
     * Re-apply every payment of this loan from scratch. Used after a payment is
     * edited or deleted so the schedules stay filled earliest-first. Payment
     * schedules and penalties themselves are left untouched.
     */
    public static function reapplyForLoan(Loan $loan) {

        DB::transaction(function() use ($loan) {

            $paymentIds = Payment::where('loan_id', $loan->id)->pluck('id');

            PenaltyPayment::whereIn('payment_id', $paymentIds)->delete();
            LoanPayment::whereIn('payment_id', $paymentIds)->delete();

            $payments = Payment::where('loan_id', $loan->id)
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            foreach($payments as $pmt) {
                $pmt->allocate();
            }
        });
    }
}
