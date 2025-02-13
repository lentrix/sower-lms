<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class DashboardController extends Model
{
    public function index() {

        $summary = [];

        $summary['arawan'] = Loan::countType(1);
        $summary['weekly'] = Loan::countType(2);
        $summary['biMonthly'] = Loan::countType(3);

        $loans = Loan::where('status', 2)
            ->whereHas('paymentSchedules', function($q1) {
            $q1->where('due_date','<', Date::now());
        })->get();

        $dueToday = [];
        foreach($loans as $loan) {
            if( ($due = $loan->due) > 0) {
                $dueToday[] = [
                    'id' => $loan->borrower_id,
                    'borrower' => $loan->borrower->last_name . ", " . $loan->borrower->first_name,
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

        return inertia('Dashboard',[
            'summary' => $summary,
            'dueToday' => $dueToday
        ]);
    }
}
