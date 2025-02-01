<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPlan extends Model
{
    protected $guarded = [];

    protected $appends = ['planText'];

    public function loanType() {
        return $this->belongsTo(LoanType::class);
    }

    public function loan() {
        return $this->hasOne(Loan::class);
    }

    public function getPlanTextAttribute() {
        switch($this->month) {
            case 2 : return "Arawan";
            case 3 : return "Weekly";
            default: return "Bi-Monthly (" . $this->month . " months)";
        }
    }
}
