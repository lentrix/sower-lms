<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    protected $guarded = [];

    public function payment() {
        return $this->belongsTo(Payment::class);
    }

    public function paymentSchedule() {
        return $this->belongsTo(PaymentSchedule::class);
    }
}
