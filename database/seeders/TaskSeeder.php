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
        $categories = Category::all();

        // Create diverse tasks across all categories
        $tasks = [
            // Work tasks
            ['title' => 'Complete project proposal', 'description' => 'Finish the Q2 project proposal document', 'due_date' => now()->addDays(3), 'status' => 'in_progress', 'priority' => 'high', 'category_id' => 1],
            ['title' => 'Review team presentations', 'description' => 'Evaluate and provide feedback on team member presentations', 'due_date' => now()->addDays(5), 'status' => 'pending', 'priority' => 'medium', 'category_id' => 1],
            ['title' => 'Fix critical bug in API', 'description' => 'Resolve the authentication issue reported by QA', 'due_date' => now()->addDay(), 'status' => 'in_progress', 'priority' => 'high', 'category_id' => 1],
            ['title' => 'Update documentation', 'description' => 'Update API documentation with new endpoints', 'due_date' => now()->addDays(7), 'status' => 'pending', 'priority' => 'low', 'category_id' => 1],

            // Personal tasks
            ['title' => 'Morning workout', 'description' => '30 minutes cardio at the gym', 'due_date' => now(), 'status' => 'done', 'priority' => 'medium', 'category_id' => 2],
            ['title' => 'Read book chapter', 'description' => 'Read Chapter 5 of "The Lean Startup"', 'due_date' => now()->addDays(2), 'status' => 'pending', 'priority' => 'low', 'category_id' => 2],
            ['title' => 'Learn Laravel best practices', 'description' => 'Complete Laravel mastery course module 3', 'due_date' => now()->addDays(4), 'status' => 'in_progress', 'priority' => 'high', 'category_id' => 2],

            // Shopping tasks
            ['title' => 'Buy groceries', 'description' => 'Milk, eggs, bread, vegetables, chicken', 'due_date' => now()->addDays(1), 'status' => 'pending', 'priority' => 'medium', 'category_id' => 3],
            ['title' => 'Purchase office supplies', 'description' => 'Pens, notepads, sticky notes', 'due_date' => now()->addDays(3), 'status' => 'pending', 'priority' => 'low', 'category_id' => 3],
            ['title' => 'Replace phone screen', 'description' => 'Get phone screen repaired at service center', 'due_date' => now()->addDays(2), 'status' => 'pending', 'priority' => 'high', 'category_id' => 3],

            // Health tasks
            ['title' => 'Annual health checkup', 'description' => 'Schedule and attend annual physical exam', 'due_date' => now()->addDays(14), 'status' => 'pending', 'priority' => 'medium', 'category_id' => 4],
            ['title' => 'Start meditation habit', 'description' => 'Begin daily 10-minute meditation practice', 'due_date' => now()->addDays(30), 'status' => 'pending', 'priority' => 'low', 'category_id' => 4],
            ['title' => 'Refill vitamins', 'description' => 'Get vitamin supplements from pharmacy', 'due_date' => now()->addDays(5), 'status' => 'pending', 'priority' => 'medium', 'category_id' => 4],

            // Education tasks
            ['title' => 'Submit Lab 3 assignment', 'description' => 'Complete CMSC 129 Lab 3 with AI integration', 'due_date' => now()->addDays(10), 'status' => 'in_progress', 'priority' => 'high', 'category_id' => 5],
            ['title' => 'Prepare for exam', 'description' => 'Study chapters 1-8 for database course', 'due_date' => now()->addDays(21), 'status' => 'pending', 'priority' => 'high', 'category_id' => 5],
            ['title' => 'Complete online course', 'description' => 'Finish "Advanced JavaScript" course on Udemy', 'due_date' => now()->addDays(60), 'status' => 'in_progress', 'priority' => 'low', 'category_id' => 5],
            ['title' => 'Write research paper', 'description' => 'Write 2000-word paper on AI ethics', 'due_date' => now()->addDays(30), 'status' => 'pending', 'priority' => 'medium', 'category_id' => 5],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}

