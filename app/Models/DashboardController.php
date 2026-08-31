<?php

namespace App\Models;

use App\Services\PenaltyAssessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class DashboardController extends Model
{
    /**
     * Whether penalty assessment is actually running, for the dashboard banner.
     *
     * The original failure in this system was not a broken rule - it was that
     * nothing ever said the assessment had never run. This makes that state
     * impossible to miss.
     */
    private function penaltyAssessmentStatus(PenaltyAssessor $assessor): array {

        if(!$assessor->isAutomaticAssessmentEnabled()) {
            return [
                'state' => 'disabled',
                'message' => 'Automatic penalty assessment is not enabled. Set PENALTY_ASSESSMENT_START_DATE to begin.',
            ];
        }

        $last = SystemLog::where('action','penalty.assessed.auto')->latest('id')->first();

        if(!$last) {
            return [
                'state' => 'stale',
                'message' => 'Automatic penalty assessment is enabled but has not run yet.',
            ];
        }

        $hours = $last->created_at->diffInHours(Date::now());

        return [
            'state' => $hours > 48 ? 'stale' : 'ok',
            'message' => $hours > 48
                ? 'Penalties last assessed ' . $last->created_at->diffForHumans() . '.'
                : 'Penalties last assessed ' . $last->created_at->format('M d, Y') . '.',
        ];
    }

    public function index(PenaltyAssessor $assessor) {

        $summary = [];

        $summary['arawan'] = Loan::countType(1);
        $summary['weekly'] = Loan::countType(2);
        $summary['biMonthly'] = Loan::countType(3);

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

        return inertia('Dashboard',[
            'summary' => $summary,
            'dueToday' => $dueToday,
            'filter' => [
                'type' => $type,
                'town' => $town,
                'barangay' => $barangay
            ],
            'planTypes' => config('sower.plan_types'),
            'penaltyAssessment' => $this->penaltyAssessmentStatus($assessor)
        ]);
    }
}
