<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyPayment extends Model
{
    protected $guarded = [];

    public function penalty() {
        return $this->belongsTo(Penalty::class);
    }

    public function payment() {
        return $this->belongsTo(Payment::class);
    }
}
