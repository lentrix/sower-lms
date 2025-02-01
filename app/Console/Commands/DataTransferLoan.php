<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Exception;
use Illuminate\Console\Command;

class DataTransferLoan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:loan {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer data from CSV to loans table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        //create log file
        $log = fopen("loan_trasfer_log.txt", "w+");

        if (!file_exists($file) || !is_readable($file)) {
            $this->error("File not found or is not readable.");
            return;
        }

        $first = true;

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if($first) {
                    $first=false; continue;
                }
                try {
                    Loan::create([
                        'id' => $row[0],
                        'ref_no' => $row[1],
                        'loan_plan_id' => $row[6],
                        'borrower_id' => $row[3],
                        'purpose' => $row[4],
                        'amount' => $row[5],
                        'status' => $row[7],
                        'transaction_fee'=>$row[8],
                        'released_at' => $row[9],
                        'created_at' => $row[10]
                    ]);
                }catch(Exception $ex) {
                    $this->error("Cannot create loan: " . $ex->getMessage());
                    fwrite($log, $ex->getMessage() . "\n\n");
                }
            }
            fclose($handle);
            fclose($log);
            $this->info("Transfer finished.");
        }else {
            $this->error("Cannot open file.");
        }

    }
}
