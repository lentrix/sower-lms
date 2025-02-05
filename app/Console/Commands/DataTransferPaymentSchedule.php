<?php

namespace App\Console\Commands;

use App\Models\PaymentSchedule;
use Exception;
use Illuminate\Console\Command;

class DataTransferPaymentSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:payment-schedule {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer data from load_schedule CSV to payment_schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        //create log file
        $log = fopen("payment_schedule_trasfer_log.txt", "w");

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
                    $pms = PaymentSchedule::create([
                        'id' => $row[0],
                        'loan_id' => $row[1],
                        'due_date' => $row[2]
                    ]);
                    $pms->amount_due = $pms->loan->amortization;
                    $pms->save();
                }catch(Exception $ex) {
                    $this->error("Cannot create payment schedule: " . $ex->getMessage());
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
