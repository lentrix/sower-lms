<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Loan extends Model
{
    protected $guarded = [];

    protected $appends = ['plan','formattedAmount','statusText','amortization','balance','totalLoanPayable'];

    protected $casts = [
        'released_at' => 'datetime'
    ];

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

        if($this->loanPlan->payment_schedules==0) dd($this);

        //arawan
        if($this->loanPlan->plan_type==1) {
            $int = $this->amount * (($this->loanPlan->interest/100) * 2);
            $totalPayable = $int + $this->amount;

            return $totalPayable/$this->loanPlan->payment_schedules;
        }

        //Weekly
        if($this->loanPlan->plan_type==2) {
            $int = $this->amount * (($this->loanPlan->interest/100)*3);
            $totalPayable = $int + $this->amount;
            return $totalPayable/$this->loanPlan->payment_schedules;
        }

        //Bi-Monthly
        $int = $this->amount * (($this->loanPlan->interest/100) * $this->loanPlan->month);
        $totalPayable = $int + $this->amount;
        return $totalPayable / $this->loanPlan->payment_schedules;
    }

    public function generatePaymentSchedules() {
        try {
            if($pcount = $this->paymentSchedules()->count()>0) {
                throw new Exception("$pcount Payment Schedules exists for this Loan.");
            }

            //initialized dueDate for the next day
            $dueDate = Carbon::now()->addDay();

            $amortization = number_format($this->amortization,2,".","");

            if($dueDate->dayOfWeek == 0) $dueDate->addDay;

            if($this->loanPlan->month=="2") { //Arawan
                for($i=0; $i<$this->loanPlan->payment_schedules; $i++) {
                    PaymentSchedule::create([
                        'loan_id' => $this->id,
                        'due_date' => $dueDate,
                        'amount_due' => $amortization,
                    ]);
                    $dueDate->addDay();
                    if($dueDate->dayOfWeek==0) $dueDate->addDay();
                }
                return;
            }

            if($this->loanPlan->month=="3") { //Weekly

                for($i=0; $i<$this->loanPlan->payment_schedules; $i++) {
                    PaymentSchedule::create([
                        'loan_id' => $this->id,
                        'due_date' => $dueDate,
                        'amount_due' => $amortization,
                    ]);
                    $dueDate->addWeek();
                }
                return;
            }

            //Otherwise for Bi-Monthly
            if($dueDate->dayOfMonth <= 7) $dueDate->setDay(20);
            else $dueDate->setDay(5)->addMonth();

            for($i=0; $i<$this->loanPlan->payment_schedules; $i++) {
                PaymentSchedule::create([
                    'loan_id' => $this->id,
                    'due_date' => $dueDate,
                    'amount_due' => $amortization
                ]);

                if($dueDate->dayOfMonth==5) {
                    $dueDate->setDay(20);
                }else {
                    $dueDate->setDay(5)->addMonth();
                }

                if($dueDate->dayOfWeek==0) $dueDate->addDay();
            }

        }catch(Exception $ex) {
            dd($ex->getMessage());
        }
    }

    public function generatePenalty() {
        $unPaidSchedules = $this->getUnpaidSchedules();

        $totalPenalty = 0;
        foreach($unPaidSchedules as $up) {
            $penaltyAmount = $up->amount_due * ($this->loanPlan->penalty/100);

            if($penaltyAmount==0) continue;

            Penalty::create([
                'payment_schedule_id' => $up->id,
                'amount' => $penaltyAmount
            ]);

            $totalPenalty += $penaltyAmount;
        }

        return $totalPenalty;
    }

    public function getPayablePenaltiesAttribute() {
        return Penalty::whereHas('paymentSchedule', function($q) {
            $q->where('loan_id', $this->id);
        })->whereDoesntHave('penaltyPayments')
        ->get();
    }

    //All unpaide dues regardless of due date
    public function getUnPaidSchedules() {
        return PaymentSchedule::whereDoesntHave('loanPayments')
            ->where('loan_id', $this->id)
            ->get();
    }

    //All unpaid past dues
    public function getUnPaidPastDueSchedules() {
        return PaymentSchedule::whereDoesntHave('loanPayments')
            ->where('due_date','<',Carbon::now()->addDay())
            ->where('loan_id', $this->id)
            ->get();
    }


    //Payment schedules that are past due without or lacking payment
    public function getUnsettledPaymentSchedules() {
        $pscheds = PaymentSchedule::where('loan_id', $this->id)
            ->where('due_date','<=', Carbon::now()->addDay())
            ->orderBy('due_date','ASC')
            ->get();
        $data = [];
        foreach($pscheds as $sched) {
            $due = $sched->amount_due;
            $paid = $sched->loanPayments->sum('amount');
            if($due > $paid) {
                $data[] = [
                    'paymentSchedule' => $sched,
                    'balance' => $due-$paid
                ];
            }
        }
        return $data;
    }

    public function getUnsettledPenalties() {
        $penalties = Penalty::whereHas('paymentSchedule', function($q1){
            $q1->where('loan_id', $this->id);
        })->get();

        $data = [];

        foreach($penalties as $penalty) {
            $amount = $penalty->amount;
            $paid = $penalty->penaltyPayments->sum('amount');
            if($amount > $paid) {
                $data[] = [
                    'penalty' => $penalty,
                    'balance' => $amount-$paid
                ];
            }
        }

        return $data;
    }

    public function getTotalLoanPayableAttribute() {
        $interest = $this->amount * ($this->loanPlan->interest/100);
        return $this->amount + ($interest*$this->loanPlan->month);
    }

    public function getBalanceAttribute() {
        $totalLoanPayments = LoanPayment::whereHas('paymentSchedule', function($q) {
            $q->where('loan_id', $this->id);
        })->sum('amount');

        return $this->totalLoanPayable-$totalLoanPayments;
    }

    public static function countType($type) {
        return Loan::where('status',2)->whereHas('loanPlan', function($q) use ($type) {
            $q->where('plan_type', $type);
        })->count();
    }

    public function getDueAttribute() {
        $now = Date::now();
        $dueSchedules = PaymentSchedule::where('loan_id',$this->id)
            ->where('due_date','<',$now);

        $loanDue = $dueSchedules->sum('amount_due');

        $loanPaid = LoanPayment::whereIn('payment_schedule_id', $dueSchedules->pluck('id'))
            ->sum('amount');

        $duePenalties = Penalty::whereIn('payment_schedule_id', $dueSchedules->pluck('id'));

        $penaltyDue = $duePenalties->sum('amount');

        $penaltyPaid = PenaltyPayment::whereIn('penalty_id', $duePenalties->pluck('id'))->sum('amount');

        return ($loanDue + $penaltyDue) - ($loanPaid + $penaltyPaid);

    }

    public function computations() {
        $totalInterest = $this->amount * (($this->loanPlan->interest/100) * $this->loanPlan->month);
        $totalPayable = $totalInterest + $this->amount;
        $amortization = $totalPayable / $this->loanPlan->payment_schedules;

        $interestPortionPerPayment = $totalInterest / $this->loanPlan->payment_schedules;
        $principalPortionPerPayment = $this->amount / $this->loanPlan->payment_schedules;

        $interestPortionPerPaymentPercentage = ($interestPortionPerPayment / $amortization);

        return [
            'totalInterest' => $totalInterest,
            'totalPayable' => $totalPayable,
            'amortization' => $amortization,
            'interestPortionPerPayment' => $interestPortionPerPayment,
            'principalPortionPerPayment' => $principalPortionPerPayment,
            'interestPortionPerPaymentPercentage' => $interestPortionPerPaymentPercentage
        ];
    }


}
