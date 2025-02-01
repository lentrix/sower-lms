<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loanTypes = [
            [
                'name' => 'MEMBER',
                'description' => 'SOWER MEMBER'
            ],
            [
                'name' => 'NON-MEMBER',
                'description' => 'NOT MEMBER OF SOWER'
            ],
            [
                'name' => 'DISCOUNTED ACCOUNTS',
                'description' => 'LOANERS WITH GOOD STANDING IN PAYMENTS'
            ],
            [
                'name' => 'NON MEMBER-ARAWAN',
                'description' => 'ARAWAN'
            ],
            [
                'name' => 'NON-MEMBER REPA',
                'description' => 'LOYAL REPA LEADER'
            ]
        ];

        foreach($loanTypes as $type) {
            LoanType::create($type);
        }

    }
}
