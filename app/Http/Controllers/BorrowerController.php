<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\LoanPayment;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use Illuminate\Http\Request;

class BorrowerController extends Controller
{
    public function index() {
        // $borrowers = Borrower::orderBy('last_name')
        //     ->orderBy('first_name')
        //     ->get();

        return inertia('Borrowers/Index',[
            // 'borrowers' => $borrowers
            'borrowers' => []
        ]);
    }

    public function search(Request $request) {
        $request->validate([
            'search' => 'required|min:3'
        ]);

        $borrowers = Borrower::where('last_name','like',"%$request->search%")
                ->orWhere('first_name','like',"%$request->search%")
                ->orWhere('middle_name','like',"%$request->search%")
                ->orWhere('address','like',"%$request->search%")
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

        return inertia('Borrowers/Index',[
            'borrowers' => $borrowers
        ]);

    }

    public function create() {
        return inertia('Borrowers/Create');
    }

    public function store(Request $request) {
        $fields = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'address' => 'required|string',
            'contact_no' => 'required|string',
            'tax_id' => 'string',
            'email' => 'required|email',
        ]);

        $borrower = Borrower::create($fields);

        return redirect('/loans/create/' . $borrower->id)->with('info', 'New borrower created.');
    }

    public function show(Borrower $borrower) {

        return inertia('Borrowers/Show', [
            'borrower' => $borrower,
            'payment_schedules'     => $borrower->activeLoan ? $borrower->activeLoan->paymentSchedules : [],
            'pending_loan'          => $borrower->getPendingLoan(),
            'totalAmountDue'        => $borrower->activeLoan ? $borrower->activeLoan->totalLoanPayable : 0,
            'totalPenalty'          => $borrower->activeLoan ? Penalty::whereHas('paymentSchedule', function($q1) use ($borrower) {
                                            $q1->where('loan_id', $borrower->activeLoan->id);
                                        })->sum('amount') : 0,
            'totalLoanPayment'      => $borrower->activeLoan ? LoanPayment::whereHas('paymentSchedule', function($q1) use ($borrower) {
                                            $q1->where('loan_id', $borrower->activeLoan->id);
                                        })->sum('amount') : 0,
            'totalPenaltyPayment'   => $borrower->activeLoan ? PenaltyPayment::whereHas('payment', function($q1) use ($borrower) {
                                            $q1->where('loan_id', $borrower->activeLoan->id);
                                        })->sum('amount') : 0,
            'loanHistory'           => $borrower->loans->load('loanPlan')
        ]);
    }

    public function edit(Borrower $borrower) {
        return inertia('Borrowers/Edit', [
            'borrower' => $borrower
        ]);
    }

    public function update(Borrower $borrower) {
        $fields = request()->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'middle_name' => 'required|string',
            'address' => 'required|string',
            'contact_no' => 'required|string',
            'tax_id' => 'string',
            'email' => 'required|email',
        ]);

        $borrower->update($fields);

        return redirect('/borrowers/' . $borrower->id)->with('success', 'Borrower updated.');
    }

    public function destroy(Borrower $borrower) {
        $borrower->delete();

        return redirect('/borrowers')->with('info', 'Borrower deleted.');
    }
}
