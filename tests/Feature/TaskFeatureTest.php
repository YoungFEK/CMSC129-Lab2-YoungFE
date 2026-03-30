<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_trash_page_displays_soft_deleted_tasks(): void
    {
        $category = Category::create([
            'name' => 'School',
            'description' => 'School-related tasks',
        ]);

        $task = Task::create([
            'title' => 'Deleted task',
            'description' => 'This task should appear in trash',
            'due_date' => now()->addDay()->toDateString(),
            'status' => 'pending',
            'priority' => 'medium',
            'category_id' => $category->id,
        ]);

        $task->delete();

        $response = $this->get(route('tasks.trash'));

        $response->assertOk();
        $response->assertSee('Deleted task');
    }

    public function test_index_order_stays_stable_when_seeded_tasks_share_the_same_created_at(): void
    {
        $category = Category::create([
            'name' => 'Work',
            'description' => 'Work-related tasks',
        ]);

        $timestamp = now()->startOfSecond();

        $firstTask = Task::create([
            'title' => 'First seeded task',
            'description' => 'Created first',
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
            'priority' => 'low',
            'category_id' => $category->id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $secondTask = Task::create([
            'title' => 'Second seeded task',
            'description' => 'Created second',
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
            'priority' => 'high',
            'category_id' => $category->id,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $firstTask->update([
            'description' => 'Edited after creation',
        ]);

        $response = $this->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            $secondTask->title,
            $firstTask->title,
        ]);
    }
}
