<?php

namespace App\Console\Commands;

use App\Models\LoanPayment;
use App\Models\Payment;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use Exception;
use Illuminate\Console\Command;

class DataTransferPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:payment {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer data from payment CSV to payments table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');

        //create log file
        $log = fopen("payment_transfer_log.txt", "w");

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
                    $loanId = $row[0];
                    $scheduleId = $row[1];
                    $payAmount = $row[2];
                    $penaltyAmount = $row[3];
                    $date = $row[4];
                    $balance = $row[5];
                    $capitalPaid = $row[6];
                    $interestPaid = $row[7];

                    $pmt = Payment::create([
                        'loan_id' => $loanId,
                        'amount' => $payAmount,
                        'or_number' => 'TRNSFR',
                        'date' => substr($date, 0, 10)
                    ]);

                    LoanPayment::create([
                        'payment_id' => $pmt->id,
                        'payment_schedule_id' => $scheduleId,
                        'amount' => $payAmount - $penaltyAmount,
                        'interest' => $interestPaid,
                        'principal' => $capitalPaid
                    ]);

                    if($penaltyAmount) {
                        //has penalty amount
                        $penalty = Penalty::create([
                            'payment_schedule_id' => $scheduleId,
                            'amount' => $penaltyAmount
                        ]);
                        PenaltyPayment::create([
                            'payment_id' => $pmt->id,
                            'penalty_id' => $penalty->id,
                            'amount' => $penaltyAmount
                        ]);
                    }

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
