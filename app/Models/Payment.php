<?php

namespace App\Models;

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
        return $this->belongsTo(LoanPayment::class);
    }

    public function penaltyPayments() {
        return $this->hasMany(PenaltyPayment::class);
    }
}
