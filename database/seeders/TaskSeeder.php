<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Category;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $work     = Category::where('name', 'Work')->first()->id;
        $personal = Category::where('name', 'Personal')->first()->id;
        $school   = Category::where('name', 'School')->first()->id;
        $health   = Category::where('name', 'Health')->first()->id;
        $finance  = Category::where('name', 'Finance')->first()->id;

        $tasks = [
            // Work
            ['title' => 'Finish quarterly report',       'description' => 'Compile Q1 sales data and write summary.',          'due_date' => Carbon::today()->addDays(2),  'status' => 'in_progress', 'priority' => 'high',   'category_id' => $work],
            ['title' => 'Reply to client emails',        'description' => 'Respond to pending client inquiries in inbox.',      'due_date' => Carbon::today(),              'status' => 'pending',     'priority' => 'high',   'category_id' => $work],
            ['title' => 'Update project documentation',  'description' => 'Revise README and API docs for the new release.',    'due_date' => Carbon::today()->addDays(5),  'status' => 'pending',     'priority' => 'medium', 'category_id' => $work],
            ['title' => 'Team standup meeting',          'description' => 'Daily sync with the dev team at 9am.',               'due_date' => Carbon::today(),              'status' => 'done',        'priority' => 'medium', 'category_id' => $work],
            ['title' => 'Deploy hotfix to production',   'description' => 'Push the login bug fix to the live server.',         'due_date' => Carbon::yesterday(),          'status' => 'done',        'priority' => 'high',   'category_id' => $work],

            // School
            ['title' => 'Submit Lab 3',                  'description' => 'Complete CMSC 129 AI integration lab.',              'due_date' => Carbon::today()->addDays(7),  'status' => 'in_progress', 'priority' => 'high',   'category_id' => $school],
            ['title' => 'Study for midterm exams',       'description' => 'Review chapters 5-9 for the OS midterm.',            'due_date' => Carbon::today()->addDays(3),  'status' => 'pending',     'priority' => 'high',   'category_id' => $school],
            ['title' => 'Read assigned chapters',        'description' => 'Finish chapters 12 and 13 of the textbook.',         'due_date' => Carbon::today()->addDays(1),  'status' => 'pending',     'priority' => 'medium', 'category_id' => $school],
            ['title' => 'Group project meeting',         'description' => 'Coordinate with groupmates for the final project.',  'due_date' => Carbon::today()->addDays(4),  'status' => 'pending',     'priority' => 'medium', 'category_id' => $school],
            ['title' => 'Submit math homework',          'description' => 'Complete problem sets 10-15.',                       'due_date' => Carbon::yesterday(),          'status' => 'done',        'priority' => 'low',    'category_id' => $school],

            // Personal
            ['title' => 'Buy groceries',                 'description' => 'Get vegetables, rice, eggs, and toiletries.',        'due_date' => Carbon::today(),              'status' => 'pending',     'priority' => 'medium', 'category_id' => $personal],
            ['title' => 'Clean the room',                'description' => 'Deep clean and organize the bedroom.',               'due_date' => Carbon::today()->addDays(2),  'status' => 'pending',     'priority' => 'low',    'category_id' => $personal],
            ['title' => 'Call parents',                  'description' => 'Weekly check-in call with family.',                  'due_date' => Carbon::today(),              'status' => 'done',        'priority' => 'medium', 'category_id' => $personal],
            ['title' => 'Fix leaking faucet',            'description' => 'Repair the bathroom faucet or call a plumber.',      'due_date' => Carbon::today()->addDays(6),  'status' => 'pending',     'priority' => 'low',    'category_id' => $personal],

            // Health
            ['title' => 'Morning jog',                   'description' => '30-minute run around the neighborhood.',             'due_date' => Carbon::today(),              'status' => 'done',        'priority' => 'medium', 'category_id' => $health],
            ['title' => 'Doctor checkup appointment',    'description' => 'Annual physical exam at the clinic.',                'due_date' => Carbon::today()->addDays(10), 'status' => 'pending',     'priority' => 'high',   'category_id' => $health],
            ['title' => 'Drink vitamins daily',          'description' => 'Take Vitamin C and multivitamins every morning.',    'due_date' => Carbon::today(),              'status' => 'in_progress', 'priority' => 'medium', 'category_id' => $health],

            // Finance
            ['title' => 'Pay electricity bill',          'description' => 'Online payment via Meralco app before due date.',    'due_date' => Carbon::today()->addDays(1),  'status' => 'pending',     'priority' => 'high',   'category_id' => $finance],
            ['title' => 'Review monthly budget',         'description' => 'Check expenses vs income for the month.',            'due_date' => Carbon::today()->addDays(3),  'status' => 'pending',     'priority' => 'medium', 'category_id' => $finance],
            ['title' => 'Transfer allowance savings',    'description' => 'Move leftover allowance to savings account.',        'due_date' => Carbon::today()->addDays(5),  'status' => 'pending',     'priority' => 'low',    'category_id' => $finance],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }
    }
}
