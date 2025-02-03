<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use Illuminate\Console\Command;

class SyncPaymentScheduleAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-payment-schedule-amount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the amount of each payment schedules to the computed amortization value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ps = PaymentSchedule::whereHas('loan', function($q) {
            $q->where('status', 2);
        })->get();

        foreach($ps as $p) {
            $p->amount_due = $p->loan->amortization;
            $p->save();
        }
        echo "done.\n";
    }
}
