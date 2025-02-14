<?php

namespace App\Http\Controllers;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowerController extends Controller
{
    public function index() {

        // $borrowers = Borrower::whereHas('loans', function($q) {
        //     $q->where('status',2);
        // })->with(['loans' => function($q) {
        //     $q->where('status',2)
        //         ->orderBy('released_at','DESC');
        // }])->paginate(20);

        $borrowers = Loan::where('status',2)
            ->orderBy('released_at', 'DESC')
            ->limit(50)->get()->map(function($q) {
                $q->loanPlan;
                return [
                    'id' => $q->borrower_id,
                    'last_name' => $q->borrower->last_name,
                    'first_name' => $q->borrower->first_name,
                    'address' => $q->borrower->address,
                    'contact_no' => $q->borrower->contact_no,
                    'activeLoan' => $q,
                ];
            });

        return inertia('Borrowers/Index',[
            'borrowers' => $borrowers
            // 'borrowers' => [],
        ]);
    }

    public function search(Request $request) {
        $request->validate([
            'search' => 'required|min:3'
        ]);

        $borrowers = Borrower::where('last_name','like',"%$request->search%")
                ->orWhere('first_name','like',"%$request->search%")
                ->orWhere('middle_name','like',"%$request->search%")
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()->map(function($q) {
                    return [
                        'id' => $q->id,
                        'last_name' => $q->last_name,
                        'first_name' => $q->first_name,
                        'address' => $q->address,
                        'contact_no' => $q->contact_no,
                        'activeLoan' => $q->activeLoan,
                    ];
                });

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
            'barangay' => 'required|string',
            'town' => 'required|string',
            'province' => 'required|string',
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
            'barangay' => 'required|string',
            'town' => 'required|string',
            'province' => 'required|string',
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

    public function showCompleted(Borrower $borrower, Loan $loan) {

        $loan->paymentSchedules;

        return inertia('Borrowers/ShowCompleted',[
            'borrower' => $borrower,
            'completed' => $loan,
            'loanHistory' => $borrower->loans->load('loanPlan'),
            'totalAmountDue'        => $loan->totalLoanPayable,
            'totalPenalty'          => Penalty::whereHas('paymentSchedule', function($q1) use ($loan) {
                                            $q1->where('loan_id', $loan->id);
                                        })->sum('amount'),
            'totalLoanPayment'      => LoanPayment::whereHas('paymentSchedule', function($q1) use ($loan) {
                                            $q1->where('loan_id', $loan->id);
                                        })->sum('amount'),
            'totalPenaltyPayment'   => PenaltyPayment::whereHas('payment', function($q1) use ($loan) {
                                            $q1->where('loan_id', $loan->id);
                                        })->sum('amount'),
        ]);
    }
}
