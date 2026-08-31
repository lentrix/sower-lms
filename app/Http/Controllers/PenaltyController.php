<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\PaymentSchedule;
use App\Models\SystemLog;
use App\Services\PenaltyAssessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenaltyController extends Controller
{
    /**
     * The payment schedules on this loan that could be penalized right now,
     * ignoring the cutover date.
     *
     * Staff review this list before anything is charged. The imported payment
     * data is incomplete, so a schedule showing no payment is not proof the
     * borrower was late - a human decides, one row at a time.
     */
    public function candidates(Loan $loan, PenaltyAssessor $assessor) {

        $candidates = $assessor->candidates($loan, true)->map(function($schedule) use ($assessor) {
            return [
                'schedule_id' => $schedule->id,
                'due_date' => $schedule->due_date->format('M d, Y'),
                'amount_due' => (float) $schedule->amount_due,
                'penalty_amount' => round($assessor->penaltyAmountFor($schedule), 2),
            ];
        })->values();

        return response()->json([
            'candidates' => $candidates,
            'total' => round($candidates->sum('penalty_amount'), 2),
        ]);
    }

    /**
     * Impose penalties on the schedules a staff member selected.
     *
     * Only the ids are trusted from the client - amounts are always recomputed
     * from the loan plan, and anything that is not currently a genuine
     * candidate is ignored.
     */
    public function assess(Request $request, Loan $loan, PenaltyAssessor $assessor) {

        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'integer',
        ]);

        $result = DB::transaction(function() use ($validated, $loan, $assessor) {

            $result = $assessor->assess($loan, $validated['schedule_ids'], true);

            if($result['count'] > 0) {
                SystemLog::record(
                    'penalty.assessed.manual',
                    sprintf(
                        'Assessed %d penalt%s totalling %s on %s\'s loan #%d.',
                        $result['count'],
                        $result['count'] == 1 ? 'y' : 'ies',
                        number_format($result['total'], 2),
                        $loan->borrower?->full_name ?? 'Unknown Borrower',
                        $loan->id
                    ),
                    $loan,
                    ['assessed' => $result, 'schedule_ids' => $validated['schedule_ids']]
                );
            }

            return $result;
        });

        if($result['count'] === 0) {
            return back()->with('error','No penalties were imposed - those payment schedules are no longer eligible.');
        }

        return back()->with('success', sprintf(
            'Imposed %d penalt%s totalling %s.',
            $result['count'],
            $result['count'] == 1 ? 'y' : 'ies',
            number_format($result['total'], 2)
        ));
    }

    /**
     * Waive the penalty imposed on a payment schedule.
     *
     * This forgives money owed, so it is logged like a payment deletion. A
     * penalty that has already been paid against cannot be removed - deleting
     * it would orphan its penalty_payments rows.
     */
    public function destroy(PaymentSchedule $paymentSchedule) {

        $penalty = $paymentSchedule->penalty;

        if(!$penalty) {
            return back()->with('error','This payment schedule has no penalty to remove.');
        }

        if($penalty->penaltyPayments()->exists()) {
            return back()->with('error','This penalty has already been paid against and can no longer be removed.');
        }

        DB::transaction(function() use ($paymentSchedule, $penalty) {

            $borrowerName = $paymentSchedule->loan?->borrower?->full_name ?? 'Unknown Borrower';
            $dueDate = $paymentSchedule->due_date->format('M d, Y');

            $deleted = [
                'id' => $penalty->id,
                'payment_schedule_id' => $penalty->payment_schedule_id,
                'amount' => (float) $penalty->amount,
                'due_date' => $paymentSchedule->due_date->format('Y-m-d'),
            ];

            $penalty->delete();

            SystemLog::record(
                'penalty.removed',
                sprintf(
                    'Removed penalty of %s on %s\'s payment schedule dated %s.',
                    number_format($deleted['amount'],2),
                    $borrowerName,
                    $dueDate
                ),
                $paymentSchedule,
                ['deleted' => $deleted]
            );
        });

        return back()->with('success','The penalty has been removed');
    }
}
