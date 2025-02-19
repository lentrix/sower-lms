<?php

namespace App\Console\Commands;

use App\Models\Borrower;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixNtilde extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-ntilde';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix NTilde character in every borrower record.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $borrowers = Borrower::where('last_name','like',"%&Ntilde;%")->orWhere('first_name','like',"%&Ntilde;%")->get();

        DB::beginTransaction();
        foreach($borrowers as $b) {
            $lname = str_replace("&Ntilde;","Ñ",$b->last_name);
            $fname = str_replace("&Ntilde;","Ñ",$b->first_name);
            $b->update([
                'last_name' => $lname,
                'first_name' => $fname,
            ]);
            echo "$lname, $fname fixed.\n";
        }
        DB::commit();
    }
}
