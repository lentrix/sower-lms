<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class ReportsController extends Controller
{
    public function index() {
        return inertia('Reports/Index');
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
