<?php

namespace App\Console\Commands;

use App\Models\LoanPlan;
use Illuminate\Console\Command;

class DataTransferLoanPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:loan-plan {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer CSV Data to loan_plans table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $firstRow = true;

        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($firstRow) {
                    $firstRow = false; continue;
                }


                LoanPlan::create([
                    'id' => $row[0],
                    'month' => $row[1],
                    'loan_type_id' => $row[2],
                    'interest' => $row[3],
                    'penalty' => $row[4],
                    'config' => $row[5],
                    'payment_schedules' => $row[1]
                ]);

            }
            fclose($handle);
            $this->info("Transfer finished.");
        } else {
            $this->error("Unable to open file: $file");
        }
    }
}
