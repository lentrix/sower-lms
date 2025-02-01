<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $fillable = ['name','description'];

    public function loanPlans() {
        return $this->hasMany(LoanPlan::class);
    }
}
