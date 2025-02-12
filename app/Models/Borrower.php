<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowerFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['activeLoan'];

    public function loans() {
        return $this->hasMany(Loan::class);
    }

    public function getActiveLoanAttribute() {
        return Loan::where('status','=',2)
            ->where('borrower_id', $this->id)
            ->first();
    }

    public function getPendingLoan() {
        return Loan::whereIn('status',[0,1])
            ->where('borrower_id', $this->id)
            ->first();
    }

    public function getAddressAttribute() {
        return "$this->barangay, $this->town, $this->province";
    }
}
