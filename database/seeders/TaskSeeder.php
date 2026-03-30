<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Category;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = Category::all();

        // Create 15 tasks with Faker
        Task::factory(15)
            ->sequence(
                ['category_id' => $categories->first()->id ?? 1],
                ['category_id' => $categories->skip(1)->first()->id ?? 2],
                ['category_id' => $categories->skip(2)->first()->id ?? 3],
                ['category_id' => $categories->skip(3)->first()->id ?? 4],
                ['category_id' => $categories->skip(4)->first()->id ?? 5],
            )
            ->create();
    }
}
