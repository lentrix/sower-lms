<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPlan extends Model
{
    protected $guarded = [];

    protected $appends = ['planText','categoryName'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function loan() {
        return $this->hasOne(Loan::class);
    }

    public function getCategoryNameAttribute() {
        return $this->category->name;
    }

    public function getPlanTextAttribute() {
        return config('sower.plan_types.' . $this->plan_type);
    }

}
