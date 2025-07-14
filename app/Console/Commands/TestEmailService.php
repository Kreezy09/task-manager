<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestEmailService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {--user-id= : Test with specific user ID} {--dry-run : Don\'t actually send emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email service functionality and configuration';

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing Email Service Configuration...');
        
        // Check email configuration
        $status = $this->emailService->getEmailStatus();
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Configured', $status['configured'] ? '✅ Yes' : '❌ No'],
                ['Driver', $status['driver']],
                ['From Address', $status['from_address']],
                ['From Name', $status['from_name']],
                ['Queue Enabled', $status['queue_enabled'] ? '✅ Yes' : '❌ No'],
            ]
        );

        if (!$status['configured']) {
            $this->error('❌ Email service is not properly configured!');
            $this->info('Please check your .env file and ensure MAIL_* settings are correct.');
            return 1;
        }

        $this->info('✅ Email service appears to be configured correctly.');

        // Test with a user
        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("❌ User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error('❌ No users found in database.');
                return 1;
            }
        }

        $this->info("📧 Testing with user: {$user->name} ({$user->email})");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No emails will be sent');
            $this->info('Email service configuration appears valid.');
            return 0;
        }

        // Create a test task
        $task = \App\Models\Task::create([
            'title' => 'Test Task - Email Service Test',
            'description' => 'This is a test task created by the email test command.',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $this->info('📝 Created test task for email testing...');

        try {
            $result = $this->emailService->sendTaskAssignmentEmail($user, $task);
            
            if ($result['success']) {
                $this->info('✅ Test email sent successfully!');
                $this->info("📅 Sent at: {$result['sent_at']}");
            } else {
                $this->error('❌ Test email failed to send!');
                $this->error("Error: {$result['error']}");
                
                // Log the error for debugging
                Log::error('Email test command failed', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $result['error']
                ]);
                
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception occurred during email test:');
            $this->error($e->getMessage());
            
            Log::error('Email test command exception', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'exception' => $e
            ]);
            
            return 1;
        }

        // Clean up test task
        $task->delete();
        $this->info('🧹 Cleaned up test task.');

        $this->info('✅ Email service test completed successfully!');
        return 0;
    }
} 