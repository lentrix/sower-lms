<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    protected $guarded = [];

    protected $appends = ['totalPayments','penaltyAmount', 'penaltyPayment'];

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
}
