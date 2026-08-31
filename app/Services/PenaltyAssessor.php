<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\PaymentSchedule;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

/**
 * The single source of truth for when a payment schedule earns a penalty.
 *
 * Four callers share these rules: the inspect-for-penalty command, the
 * request-triggered sweep middleware, point-of-use assessment when a borrower
 * or payment page is opened, and the staff-run backfill on the borrower page.
 * Keep the rules here so those four can never drift apart.
 */
class PenaltyAssessor
{
    /** Plan types, in the order the command's {code} argument groups them. */
    public const BI_MONTHLY = 3;
    public const WEEKLY = 2;
    public const ARAWAN = 1;

    /**
     * Grace period, in days, before a plan type's unsettled payment is penalized.
     * See the note in config/sower.php - the anchor differs per plan type.
     */
    public function graceDays(int $planType): int {
        return collect(config('sower.loan_plans'))->firstWhere('plan_type', $planType)['penalty_after_days'];
    }

    /**
     * The date a payment must fall strictly before to be penalized.
     *
     * Anchored to the start of the day so that the result does not depend on
     * what time of day assessment happens to run - due dates are stored at
     * midnight, so a cutoff carrying the current clock time would penalize a
     * payment that is exactly the grace period old.
     */
    public function cutoffFor(int $planType): Carbon {
        $cutoff = Carbon::now()->subDays($this->graceDays($planType))->startOfDay();

        // Weekly and Arawan have always shifted off a Sunday cutoff.
        if($planType !== self::BI_MONTHLY && $cutoff->dayOfWeek == 0) $cutoff->addDay();

        return $cutoff;
    }

    /**
     * The date before which loans are never assessed automatically.
     *
     * Null means automatic assessment is switched off entirely - see
     * config/sower.php. Legacy accounts are handled by the staff-run backfill,
     * which passes $ignoreCutover.
     */
    public function cutoverDate(): ?Carbon {
        $configured = config('sower.penalty_assessment_start_date');

        return $configured ? Carbon::parse($configured)->startOfDay() : null;
    }

    public function isAutomaticAssessmentEnabled(): bool {
        return $this->cutoverDate() !== null;
    }

    /**
     * Payment schedules that should carry a penalty but do not yet.
     *
     * Pure - writes nothing. Pass a loan to scope it to one account, or null
     * for the whole portfolio. $planTypes limits which plan rules are applied
     * (the command's {code} argument).
     */
    public function candidates(?Loan $loan = null, bool $ignoreCutover = false, ?array $planTypes = null): Collection {

        $cutover = $this->cutoverDate();

        // With no cutover date configured, automatic assessment finds nothing.
        if(!$ignoreCutover && !$cutover) return collect();

        $planTypes = $planTypes ?: [self::BI_MONTHLY, self::WEEKLY, self::ARAWAN];

        $found = collect();

        foreach($planTypes as $planType) {

            $cutoff = $this->cutoffFor($planType);

            $query = PaymentSchedule::whereHas('loan', function($q1) use ($planType, $cutoff, $loan, $ignoreCutover, $cutover) {
                $q1->whereHas('loanPlan', function($q2) use ($planType) {
                    $q2->where('plan_type', $planType);
                });

                // Arawan is anchored on the loan's release date - the whole term
                // lapsing - rather than on each schedule's due date.
                if($planType === self::ARAWAN) $q1->where('released_at', '<', $cutoff);

                if($loan) $q1->where('id', $loan->id);

                if(!$ignoreCutover) $q1->where('released_at', '>=', $cutover);
            })
            ->whereDoesntHave('penalty')
            ->whereDoesntHave('loanPayments');

            if($planType !== self::ARAWAN) $query->where('due_date', '<', $cutoff);

            $found = $found->merge($query->with('loan.loanPlan')->orderBy('due_date')->get());
        }

        return $found;
    }

    /**
     * What a candidate schedule would be charged, without imposing it.
     */
    public function penaltyAmountFor(PaymentSchedule $schedule): float {
        return $schedule->amount_due * ($schedule->loan->loanPlan->penalty / 100.0);
    }

    /**
     * Impose penalties. Returns ['count' => int, 'total' => float].
     *
     * $scheduleIds limits the work to specific schedules (the staff backfill,
     * where each one is confirmed by a human); null assesses every candidate.
     */
    public function assess(?Loan $loan = null, ?array $scheduleIds = null, bool $ignoreCutover = false, ?array $planTypes = null): array {

        $candidates = $this->candidates($loan, $ignoreCutover, $planTypes);

        if($scheduleIds !== null) {
            $candidates = $candidates->whereIn('id', $scheduleIds);
        }

        $count = 0;
        $total = 0.0;

        foreach($candidates as $schedule) {
            $amount = $this->penaltyAmountFor($schedule);

            try {
                $schedule->imposePenalty();
            } catch (QueryException $ex) {
                // penalties.payment_schedule_id is unique. Losing a race against
                // another request that just assessed the same schedule is the
                // constraint doing its job, not an error.
                if($this->isUniqueViolation($ex)) continue;
                throw $ex;
            }

            $count++;
            $total += $amount;
        }

        return ['count' => $count, 'total' => $total];
    }

    /**
     * Assess the whole portfolio. Used by the daily request-triggered sweep.
     */
    public function sweep(): array {
        return $this->assess();
    }

    private function isUniqueViolation(QueryException $ex): bool {
        return $ex->getCode() === '23000';
    }
}
