<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The home page should redirect users to the task list.
     */
    public function test_the_application_redirects_to_the_tasks_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('tasks.index'));
    }
}
