<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixAmortization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-amortization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the Amortization Discrepancy in Arawan data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $loans = Loan::whereHas('loanPlan', function($q) {
            $q->where('plan_type',1);
        })->get();

        foreach($loans as $loan) {
            echo "{$loan->borrower->last_name}.. ";
            DB::beginTransaction();
            foreach($loan->paymentSchedules as $ps) {
                $ps->amount_due = $ps->loan->amortization;
                $ps->save();
            }
            DB::commit();
            echo "done.\n";
        }
    }
}
