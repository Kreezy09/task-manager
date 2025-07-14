<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $tries = 3; // Number of retry attempts
    public $timeout = 30; // Timeout in seconds
    public $backoff = [60, 180, 360]; // Delay between retries in seconds

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new task has been assigned to you.')
            ->line('Task: ' . $this->task->title)
            ->line('Description: ' . $this->task->description)
            ->line('Status: ' . ucfirst(str_replace('_', ' ', $this->task->status)))
            ->when($this->task->deadline, function ($message) {
                return $message->line('Deadline: ' . $this->task->deadline->format('F j, Y g:i A'));
            })
            ->action('View Task', url('/dashboard'))
            ->line('Please log in to your dashboard to view and update this task.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'task_description' => $this->task->description,
            'task_status' => $this->task->status,
            'task_deadline' => $this->task->deadline,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Task assignment email failed after all retries', [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'error' => $exception->getMessage(),
            'exception' => $exception
        ]);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryAfter(): int
    {
        return 60; // Retry after 60 seconds
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return $this->backoff;
    }
}
