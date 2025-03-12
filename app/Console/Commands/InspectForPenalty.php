<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InspectForPenalty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspect-for-penalty {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspects payments schedules and impose penalties when applicable Code: 1 - Bi-monthly, Code: 2 - Weekly and Arawan, Code: 3 - All';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $code = $this->argument('code');

        $today = Carbon::now();

        /**
         * Bi-Monthly
         * Check all payment schedules with bi-monthly loan types
         * which are unpaid and whose due date is past today
         * but only if today is 5th or 20th of the month
         * or 6th or 21th if 5th or 20th falls on a Sunday
         */
        if($code==1 || $code==3) {
            if( (($today->day == 5 || $today->day == 20) && $today->dayOfWeek!=0) || (($today->day==6 || $today->day==21) && $today->dayOfWeek==1) || true ){

                $scheds = PaymentSchedule::whereHas('loan', function($q1) {
                    $q1->whereHas('loanPlan', function($q2) {
                        $q2->where('plan_type', 3);
                    });
                })->where('due_date','<', $today)
                ->whereDoesntHave('penalty')
                ->whereDoesntHave('loanPayments')->get();

                $n = 0;
                foreach($scheds as $sched) {
                    $sched->imposePenalty();
                }

            }

        }

        if($code==2 || $code==3) {

            /**
             * For Weekly Loan types. Check payment schedules
             * which are not yet paid whose due date is
             * two weeks ago.
             */
            $twoWeeksAgo = $today->subDays(14);
            if($twoWeeksAgo->dayOfWeek==0) $twoWeeksAgo->addDay();

            $wscheds = PaymentSchedule::whereHas('loan', function($q1) {
                $q1->whereHas('loanPlan', function($q2) {
                    $q2->where('plan_type', 2);
                });
            })->where('due_date','<', $twoWeeksAgo)
            ->whereDoesntHave('penalty')
            ->whereDoesntHave('loanPayments')->get();

            foreach($wscheds as $sched) {
                $sched->imposePenalty();
            }

            /**
             * For Daily Loan Types. Check payment schedules whose
             * loan account has a release date of 56 days ago
             */

            $fiftySixDaysAgo = Carbon::now()->subDays(56);
            if($fiftySixDaysAgo->dayOfWeek==0) $fiftySixDaysAgo->addDay();

            $dscheds = PaymentSchedule::whereHas('loan', function($q1) use ($fiftySixDaysAgo) {
                $q1->whereHas('loanPlan', function($q2) {
                    $q2->where('plan_type', 1);
                })->where('released_at','<',$fiftySixDaysAgo);
            })
            ->whereDoesntHave('penalty')
            ->whereDoesntHave('loanPayments')->get();

            foreach($dscheds as $sched) {
                $sched->imposePenalty();
            }
        }
    }
}
