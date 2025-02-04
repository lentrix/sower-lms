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

                $planType = null;
                switch($row[1]) {
                    case 2: $planType = 1; break;
                    case 3: $planType = 2; break;
                    default: $planType = 3; break;
                }

                $categoryId = null;
                switch($row[3]) {
                    case 3 : $categoryId = 1; break;
                    case 6 : $categoryId = 2; break;
                    case 4 : $categoryId = 3; break;
                    default: $categoryId = 2;
                }

                if(!$categoryId) dd($row[3]);

                LoanPlan::create([
                    'id' => $row[0],
                    'month' => $row[1],
                    'plan_type' => $planType,
                    'category_id' => $categoryId,
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
