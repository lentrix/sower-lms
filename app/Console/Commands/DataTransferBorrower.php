<?php

namespace App\Console\Commands;

use App\Models\Borrower;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DataTransferBorrower extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data-transfer:borrower {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfers the CSV file data to the borrower table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $this->info('Data transfer started');

        if (!file_exists($file) || !is_readable($file)) {
            $this->error('File not found or is not readable');
            return;
        }

        $isFirst = false;
        $data = array();

        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if(!$isFirst) {
                    $isFirst = true;
                    continue;
                }
                $borrower = Borrower::create([
                    'id' => $row[0],
                    'first_name' => $row[1],
                    'middle_name' => $row[2],
                    'last_name' => $row[3],
                    'contact_no' => $row[4],
                    'address' => $row[5],
                    'email' => $row[6],
                    'tax_id' => $row[7],
                ]);
                echo "Created borrower with id: " . $borrower->id . " Name: " . $borrower->last_name . ", " . $borrower->first_name . "\n";

            }
            fclose($handle);
            //Change ? middle name to null
            DB::table('borrowers')->where('middle_name','?')->update(['middle_name'=>null]);
        }

        $this->info('File: ' . $file);
        $this->info('Data transfer completed');
    }
}
