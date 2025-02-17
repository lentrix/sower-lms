<?php

namespace App\Console\Commands;

use App\Models\LoanPayment;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecomputePaymentPortions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recompute-payment-portions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute the principal and interest portions of all loan payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recomputing payment portions...');

        DB::beginTransaction();

        try {

            foreach(LoanPayment::get() as $lnPayment) {
                $this->info('Recomputing payment #' . $lnPayment->id);
                $computations = $lnPayment->payment->loan->computations();
                $intPct = $computations['interestPortionPerPaymentPercentage'];

                $interest = bcdiv($lnPayment->amount * $intPct, 1, 2);
                $principal = bcdiv($lnPayment->amount - $interest, 1, 2);

                $lnPayment->update([
                    'interest' => $interest,
                    'principal' => $principal
                ]);
            }

            DB::commit();

        } catch(Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
            return;
        }

        $this->info('All Done!');
    }
}
