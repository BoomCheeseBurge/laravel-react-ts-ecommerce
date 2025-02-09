<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            /**
             * The order must be appropriate with respect to the dependency of one seeder to another
             */
            RoleSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
