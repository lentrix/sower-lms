<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'full_name' => 'Hawkman Lentrix',
        //     'user_name' => 'hawkman',
        //     'email' => 'test@example.com',
        //     'password' => bcrypt('password'),
        // ]);

        $u1 = User::create([
                'full_name' => 'Benjie B. Lenteria',
                'user_name' => 'lentrix',
                'email'     => 'lentrix@materdeicollege.com',
                'password'  => bcrypt('password'),
        ]);

        $u2 = User::create([
                'full_name' => 'Rowerna Lauron',
                'user_name' => 'ahwen',
                'email'     => 'ahwen@email.com',
                'password'  => bcrypt('password'),
        ]);

        $u3 = User::create([
                'full_name' => 'Clerk 1',
                'user_name' => 'clerk1',
                'email'     => 'clerk@sower.com',
                'password'  => bcrypt('password'),
        ]);

        $manageUsers = Permission::create(['name'=>'manage users']);
        $manageSystem = Permission::create(['name'=>'manage system']);

        $u1->givePermissionTo($manageUsers);
        $u1->givePermissionTo($manageSystem);

        $u2->givePermissionTo($manageUsers);
        $u2->givePermissionTo($manageSystem);

        $this->call(CategorySeeder::class);
    }
}
