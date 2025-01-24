<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use Illuminate\Http\Request;

class BorrowerController extends Controller
{
    public function index() {
        $borrowers = Borrower::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return inertia('Borrowers',[
            'borrowers' => $borrowers
        ]);
    }
}
