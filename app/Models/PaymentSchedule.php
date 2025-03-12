<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $guarded = [];

    protected $appends = ['totalPayments','penaltyAmount', 'penaltyPayment'];

    protected $casts = [
        'due_date' => 'datetime'
    ];

    public function loan() {
        return $this->belongsTo(Loan::class);
    }

    public function loanPayments() {
        return $this->hasMany(LoanPayment::class);
    }

    public function penalty() {
        return $this->hasOne(Penalty::class);
    }

    public function getTotalPaymentsAttribute() {
        return $this->loanPayments->sum('amount');
    }

    public function getPenaltyAmountAttribute() {
        return $this->penalty ? $this->penalty->amount : 0;
    }

    public function getPenaltyPaymentAttribute() {
        return $this->penalty ? $this->penalty->penaltyPayments->sum('amount') : 0;
    }

    public function imposePenalty() {

        $penaltyAmount = $this->amount_due * ($this->loan->loanPlan->penalty/100.0);

        Penalty::create([
            'payment_schedule_id' => $this->id,
            'amount' => $penaltyAmount
        ]);

        // echo ($this->loan->borrower->last_name
        //     . ', '
        //     . $this->loan->borrower->first_name
        //     . " Type: " . $this->loan->loanPlan->planText
        //     . " Due Date: " . $this->due_date->format('M-d-Y')
        //     . " Amount: " . $this->amount_due
        //     . " Penalty: " . $penaltyAmount . "\n");
    }
}
