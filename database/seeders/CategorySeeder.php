<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Top-level categories (depth: 0)
            [
                'name' => 'Electronics',
                'department_id' => 1, // Assumed department ID for 'electronics' is 1
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'department_id' => 2,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Subcategories of Electronics (depth: 1)
            [
                'name' => 'Computers',
                'department_id' => 1,
                'parent_id' => 1, // Has parent 'Electronics'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smartphones',
                'department_id' => 1,
                'parent_id' => 1, // Has parent 'Electronics'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Subcategories of Computers (depth: 2)
            [
                'name' => 'Laptops',
                'department_id' => 1,
                'parent_id' => 3, // Has parent 'Computers'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Desktops',
                'department_id' => 1,
                'parent_id' => 3, // Has parent 'Computers'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Subcategories of Smartphones (depth: 2)
            [
                'name' => 'Android',
                'department_id' => 1,
                'parent_id' => 4, // Has parent 'Smartphones'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iphones',
                'department_id' => 1,
                'parent_id' => 4, // Has parent 'Smartphones'
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
