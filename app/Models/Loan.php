<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $guarded = [];

    protected $appends = ['plan','formattedAmount','statusText','amortization'];

    public function borrower() {
        return $this->belongsTo(Borrower::class);
    }

    public function loanPlan() {
        return $this->belongsTo(LoanPlan::class);
    }

    public function getStatusTextAttribute() {
        $status = [
            "Request",
            "Confirmed",
            "Released",
            "Completed",
            "Denied",
            "Incomplete"
        ];

        return $status[$this->status];
    }

    public function paymentSchedules() {
        return $this->hasMany(PaymentSchedule::class)->orderBy('due_date');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function getPlanAttribute() {
        return $this->loanPlan->loanType;
    }

    public function getFormattedAmountAttribute() {
        return number_format($this->amount,2,".",",");
    }

    public function getAmortizationAttribute() {

        //arawan
        if($this->loanPlan->month==2) {
            $int = $this->amount * (($this->loanPlan->interest/100) * 2);
            $totalPayable = $int + $this->amount;

            return $totalPayable/56;
        }

        //Weekly
        if($this->loanPlan->month==3) {
            $int = $this->amount * (($this->loanPlan->interest/100)*3);
            $totalPayable = $int + $this->amount;
            return $totalPayable/12;
        }

        //Bi-Monthly
        $int = $this->amount * (($this->loanPlan->interest/100) * ($this->loanPlan->month/2));
        $totalPayable = $int + $this->amount;
        return $totalPayable / ($this->loanPlan->month);
    }

}
