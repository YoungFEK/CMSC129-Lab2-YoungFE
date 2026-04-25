<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Work',     'description' => 'Professional and job-related tasks'],
            ['name' => 'Personal', 'description' => 'Personal errands and goals'],
            ['name' => 'School',   'description' => 'Academic assignments and studying'],
            ['name' => 'Health',   'description' => 'Fitness, wellness, and medical tasks'],
            ['name' => 'Finance',  'description' => 'Bills, budgets, and money management'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
