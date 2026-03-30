<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Work', 'description' => 'Work-related tasks'],
            ['name' => 'Personal', 'description' => 'Personal growth and tasks'],
            ['name' => 'Shopping', 'description' => 'Shopping and errands'],
            ['name' => 'Health', 'description' => 'Health and fitness tasks'],
            ['name' => 'Education', 'description' => 'Learning and educational tasks'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
