<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Exception;

class EmailErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }

    /** @test */
    public function task_creation_succeeds_even_when_email_fails()
    {
        // Mock EmailService to simulate email failure
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendTaskAssignmentEmail')
                ->andReturn([
                    'success' => false,
                    'error' => 'SMTP connection failed',
                    'sent_at' => null
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'user_id' => $this->user->id,
                'status' => 'pending',
                'deadline' => now()->addDay()
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'title' => 'Test Task',
            'email_sent' => false,
            'email_error' => 'SMTP connection failed'
        ]);

        // Verify task was created in database
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function task_update_succeeds_even_when_email_fails()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Original Task'
        ]);

        // Mock EmailService to simulate email failure
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendTaskReassignmentEmail')
                ->andReturn([
                    'success' => false,
                    'error' => 'Authentication failed',
                    'sent_at' => null
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Task',
                'user_id' => $this->user->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'Updated Task',
            'email_sent' => false,
            'email_error' => 'Authentication failed'
        ]);

        // Verify task was updated in database
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task'
        ]);
    }

    /** @test */
    public function email_success_is_properly_handled()
    {
        // Mock EmailService to simulate email success
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendTaskAssignmentEmail')
                ->andReturn([
                    'success' => true,
                    'error' => null,
                    'sent_at' => now()
                ]);
        });

        $response = $this->actingAs($this->admin)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'user_id' => $this->user->id,
                'status' => 'pending'
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'title' => 'Test Task',
            'email_sent' => true,
            'email_error' => null
        ]);
    }

    /** @test */
    public function email_status_endpoint_returns_correct_information()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/email-status');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'configured',
            'driver',
            'from_address',
            'from_name',
            'queue_enabled'
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_email_status()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/email-status');

        $response->assertStatus(403);
    }

    /** @test */
    public function email_service_validates_user_email()
    {
        // Create user without email
        $userWithoutEmail = User::factory()->create(['email' => '']);
        $task = Task::factory()->create(['user_id' => $userWithoutEmail->id]);

        $emailService = app(EmailService::class);
        $result = $emailService->sendTaskAssignmentEmail($userWithoutEmail, $task);

        $this->assertFalse($result['success']);
        $this->assertEquals('User does not have a valid email address', $result['error']);
    }

    /** @test */
    public function email_failures_are_logged()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Failed to send task assignment email' &&
                       isset($context['task_id']) &&
                       isset($context['user_id']) &&
                       isset($context['error']);
            });

        // Mock EmailService to simulate email failure
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendTaskAssignmentEmail')
                ->andReturn([
                    'success' => false,
                    'error' => 'Test error',
                    'sent_at' => null
                ]);
        });

        $this->actingAs($this->admin)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'user_id' => $this->user->id,
                'status' => 'pending'
            ]);
    }

    /** @test */
    public function email_successes_are_logged()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Task assignment email sent successfully' &&
                       isset($context['task_id']) &&
                       isset($context['user_id']) &&
                       isset($context['user_email']);
            });

        // Mock EmailService to simulate email success
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('sendTaskAssignmentEmail')
                ->andReturn([
                    'success' => true,
                    'error' => null,
                    'sent_at' => now()
                ]);
        });

        $this->actingAs($this->admin)
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'user_id' => $this->user->id,
                'status' => 'pending'
            ]);
    }
} 