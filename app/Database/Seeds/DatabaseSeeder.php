<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run all seeders in correct order.
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(TeamSeeder::class);
        $this->call(ConfigSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UserBackfillSeeder::class);
    }
}
