<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CriterionSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * database/seeders/DatabaseSeeder.php
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            CriterionSeeder::class,
            UserSeeder::class,
        ]);
    }
}
