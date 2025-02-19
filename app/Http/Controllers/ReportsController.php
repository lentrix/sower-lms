<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class ReportsController extends Controller
{
    public function index(Request $request) {

        $now = Carbon::now();
        $year = $request->year ? $request->year : $now->year;
        $month = $request->month ? $request->month : $now->month;

        $payments = Payment::whereYear('date', $year)->whereMonth('date', $month)
            ->orderBy('or_number')
            ->get();

        $paymentReport = [];
        $totalLoanPayments = 0;
        $totalInterestPaid = 0;
        $totalPrincipalPaid = 0;
        $totalPenaltyPaid = 0;

        $cashFlowReport = [
            'loanPayment' => 0,
            'interestPayment' => 0,
            'principalPayment' => 0,
            'penaltyPayment' => 0,
        ];

        foreach($payments as $pmt) {
            $paymentReport[] = [
                'id' => $pmt->id,
                'date' => $pmt->date->format('M-d-Y'),
                'orno' => $pmt->or_number,
                'payor' => $pmt->loan->borrower->full_name,
                'amount' => $pmt->amount,
                'principal' => $prin = $pmt->loanPayments?->sum('principal'),
                'interest' => $intr = $pmt->loanPayments?->sum('interest'),
                'penalty' => $pnlty = $pmt->penaltyPayments?->sum('amount')
            ];
            $cashFlowReport['loanPayment'] += $pmt->amount;
            $cashFlowReport['interestPayment'] += $intr;
            $cashFlowReport['principalPayment'] += $prin;
            $cashFlowReport['penaltyPayment'] += $pnlty;
        }

        $loansCount = Loan::whereYear('released_at', $year)->whereMonth('released_at', $month)->count();

        $cashFlowReport['processing'] = $loansCount * 100;
        $cashFlowReport['insurance'] = $loansCount * 50;

        $monthNum = $now->month;


        return inertia('Reports/Index', compact('paymentReport','cashFlowReport','month','year'));
    }

    public function dueToday() {

        $loans = Loan::where('status', 2)
            ->whereHas('paymentSchedules', function($q1) {
            $q1->where('due_date','<', Date::now());
        });

        $type = request()->query('type');
        $town = request()->query('town');
        $barangay = request()->query('barangay');

        if($type) {
            $loans->whereHas('loanPlan', function($q) use ($type) {
                $q->where('plan_type', $type);
            });
        }

        if($barangay) {
            $loans->whereHas('borrower', function($q) use ($barangay) {
                $q->where('barangay', $barangay);
            });
        }

        if($town) {
            $loans->whereHas('borrower', function($q) use ($town) {
                $q->where('town', $town);
            });
        }


        $dueToday = [];
        foreach($loans->get() as $loan) {
            if( ($due = $loan->due) > 0) {
                $dueToday[] = [
                    'id' => $loan->borrower_id,
                    'loan_id' => $loan->id,
                    'borrower' => $loan->borrower->last_name . ", " . $loan->borrower->first_name,
                    'contact_no' => $loan->borrower->contact_no,
                    'address' => $loan->borrower->barangay . ", " . $loan->borrower->town,
                    'due' => number_format($due,2),
                    'type' => $loan->loanPlan->plan_type
                ];
            }
        }

        usort($dueToday, function($a, $b) {
            if($a['borrower'] == $b['borrower']) return 0;
            return ($a['borrower'] < $b['borrower']) ? -1 : 1;
        });

        return Pdf::loadView('pdf.due-today', compact('type','barangay','town','dueToday'))
            ->setPaper('letter', 'portrait')
            ->stream('due-today.pdf');
    }

}
