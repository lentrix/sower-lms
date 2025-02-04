<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'MEMBER',
                'description' => 'SOWER MEMBER',
                'interest_rate' => 3
            ],
            [
                'name' => 'NON-MEMBER',
                'description' => 'NOT MEMBER OF SOWER',
                'interest_rate' => 6
            ],
            [
                'name' => 'DISCOUNTED ACCOUNTS',
                'description' => 'LOANERS WITH GOOD STANDING IN PAYMENTS',
                'interest_rate' => 4
            ],
            // [
            //     'name' => 'NON MEMBER-ARAWAN',
            //     'description' => 'ARAWAN',
            // ],
            // [
            //     'name' => 'NON-MEMBER REPA',
            //     'description' => 'LOYAL REPA LEADER'
            // ]
        ];

        foreach($categories as $categ) {
            Category::create($categ);
        }

    }
}
