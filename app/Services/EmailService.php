<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Notifications\TaskAssigned;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

class EmailService
{
    /**
     * Send task assignment notification with comprehensive error handling
     *
     * @param User $user
     * @param Task $task
     * @return array
     */
    public function sendTaskAssignmentEmail(User $user, Task $task): array
    {
        $result = [
            'success' => false,
            'error' => null,
            'retry_count' => 0,
            'sent_at' => null
        ];

        try {
            // Validate user has email
            if (empty($user->email)) {
                throw new Exception('User does not have a valid email address');
            }

            // Send notification
            $user->notify(new TaskAssigned($task));
            
            $result['success'] = true;
            $result['sent_at'] = now();
            
            Log::info('Task assignment email sent successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            
            Log::error('Failed to send task assignment email', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);

            // Try fallback notification method if configured
            $this->sendFallbackNotification($user, $task, $e);
        }

        return $result;
    }

    /**
     * Send task reassignment notification with comprehensive error handling
     *
     * @param User $user
     * @param Task $task
     * @param int|null $previousUserId
     * @return array
     */
    public function sendTaskReassignmentEmail(User $user, Task $task, ?int $previousUserId = null): array
    {
        $result = [
            'success' => false,
            'error' => null,
            'retry_count' => 0,
            'sent_at' => null
        ];

        try {
            // Validate user has email
            if (empty($user->email)) {
                throw new Exception('User does not have a valid email address');
            }

            // Send notification
            $user->notify(new TaskAssigned($task));
            
            $result['success'] = true;
            $result['sent_at'] = now();
            
            Log::info('Task reassignment email sent successfully', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'previous_user_id' => $previousUserId
            ]);

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            
            Log::error('Failed to send task reassignment email', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'previous_user_id' => $previousUserId,
                'error' => $e->getMessage(),
                'exception' => $e
            ]);

            // Try fallback notification method if configured
            $this->sendFallbackNotification($user, $task, $e);
        }

        return $result;
    }

    /**
     * Send fallback notification when email fails
     *
     * @param User $user
     * @param Task $task
     * @param Exception $originalException
     * @return void
     */
    private function sendFallbackNotification(User $user, Task $task, Exception $originalException): void
    {
        try {
            // Log the fallback attempt
            Log::warning('Attempting fallback notification method', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'original_error' => $originalException->getMessage()
            ]);

            // Here you could implement alternative notification methods:
            // - SMS notification
            // - Push notification
            // - Slack/Discord webhook
            // - Database notification
            // - Log to admin dashboard
            
            // For now, we'll just log that fallback was attempted
            Log::info('Fallback notification method attempted', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'method' => 'log_only'
            ]);

        } catch (Exception $e) {
            Log::error('Fallback notification also failed', [
                'task_id' => $task->id,
                'user_id' => $user->id,
                'fallback_error' => $e->getMessage(),
                'original_error' => $originalException->getMessage()
            ]);
        }
    }

    /**
     * Check if email service is properly configured
     *
     * @return bool
     */
    public function isEmailConfigured(): bool
    {
        $mailConfig = config('mail');
        
        // Check if mail driver is configured
        if (empty($mailConfig['default'])) {
            return false;
        }

        // Check if SMTP settings are configured for SMTP driver
        if ($mailConfig['default'] === 'smtp') {
            $smtpConfig = $mailConfig['mailers']['smtp'] ?? [];
            return !empty($smtpConfig['host']) && !empty($smtpConfig['username']);
        }

        // For other drivers, just check if default is set
        return true;
    }

    /**
     * Get email configuration status
     *
     * @return array
     */
    public function getEmailStatus(): array
    {
        return [
            'configured' => $this->isEmailConfigured(),
            'driver' => config('mail.default'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'queue_enabled' => config('queue.default') !== 'sync'
        ];
    }
} 