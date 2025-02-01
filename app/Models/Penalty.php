<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $guarded = [];

    public function paymentSchedule() {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function penaltyPayments() {
        return $this->hasMany(PenaltyPayment::class);
    }
}
