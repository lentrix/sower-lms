<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class GeneratePenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate penalties for all overdue payment schedules.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $activeLoans = Loan::where('status','2')->get();
        foreach($activeLoans as $al) {
            echo "Assessing Loan: " . $al->borrower->last_name . "|" . $al->loanPlan->loanType->name . "... ";
            $penalty = $al->generatePenalty();
            echo "$penalty.\n";
        }
    }
}
