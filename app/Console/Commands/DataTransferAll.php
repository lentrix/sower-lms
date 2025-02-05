<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DataTransferAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute all the data transfer commands in a series. Make sure all the source csv files are present.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //borrowers
        $this->call('data-transfer:borrower',['file'=>'borrower.csv']);

        //loan plans
        $this->call('data-transfer:loan-plan',['file'=>'loan_plan.csv']);

        //loan
        $this->call('data-transfer:loan',['file'=>'loan.csv']);

        //payment_schedule
        $this->call('data-transfer:payment-schedule',['file'=>'loan_schedule.csv']);

        //payments
        $this->call('data-transfer:payment',['file'=>'payment_and_penalty.csv']);

        //sync payment schedule amounts
        $this->call('sync-payment-schedule-amount');

        //Corrective measures...
        DB::table('loan_plans')->where('payment_schedules', 56)->update(['month'=>2, 'plan_type'=>1]);

    }
}
