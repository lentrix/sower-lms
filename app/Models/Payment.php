<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

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


                $interest = round($payAmount * $intPct, 2);
                $principal = round($payAmount - $interest, 2);

                LoanPayment::create([
                    'payment_id' => $pmt->id,
                    'payment_schedule_id' => $psched->id,
                    'amount' => $payAmount,
                    'interest' => $interest,
                    'principal' => $principal
                ]);

                $amountToPay -= $payAmount;
            }

        }catch(Exception $ex) {
            dd($ex);
        }
    }
}
