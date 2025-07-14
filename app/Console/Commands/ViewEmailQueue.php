<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use App\Notifications\TaskAssigned;

class ViewEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:emails {--failed : Show only failed jobs} {--pending : Show only pending jobs} {--stats : Show queue statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View email queue status and pending jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $showFailed = $this->option('failed');
        $showPending = $this->option('pending');
        $showStats = $this->option('stats');

        if ($showStats) {
            $this->showQueueStatistics();
            return;
        }

        if ($showFailed) {
            $this->showFailedJobs();
            return;
        }

        if ($showPending) {
            $this->showPendingJobs();
            return;
        }

        // Show all information
        $this->showQueueStatistics();
        $this->line('');
        $this->showPendingJobs();
        $this->line('');
        $this->showFailedJobs();
    }

    private function showQueueStatistics()
    {
        $this->info('📊 Email Queue Statistics');
        $this->line('');

        // Get queue statistics
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        $taskAssignedJobs = DB::table('jobs')
            ->where('payload', 'like', '%TaskAssigned%')
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Pending Jobs (Total)', $pendingJobs],
                ['Failed Jobs (Total)', $failedJobs],
                ['Task Assignment Emails (Pending)', $taskAssignedJobs],
            ]
        );
    }

    private function showPendingJobs()
    {
        $this->info('📧 Pending Email Jobs');
        $this->line('');

        $jobs = DB::table('jobs')
            ->where('payload', 'like', '%TaskAssigned%')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($jobs->isEmpty()) {
            $this->warn('No pending email jobs found.');
            return;
        }

        $jobData = [];
        foreach ($jobs as $job) {
            $payload = json_decode($job->payload, true);
            $jobData[] = [
                'ID' => $job->id,
                'Queue' => $job->queue,
                'Attempts' => $job->attempts,
                'Created' => $job->created_at,
                'Available' => $job->available_at,
            ];
        }

        $this->table(
            ['Job ID', 'Queue', 'Attempts', 'Created At', 'Available At'],
            $jobData
        );
    }

    private function showFailedJobs()
    {
        $this->info('❌ Failed Email Jobs');
        $this->line('');

        $failedJobs = DB::table('failed_jobs')
            ->where('payload', 'like', '%TaskAssigned%')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->info('No failed email jobs found.');
            return;
        }

        $jobData = [];
        foreach ($failedJobs as $job) {
            $jobData[] = [
                'ID' => $job->id,
                'Queue' => $job->queue,
                'Exception' => substr($job->exception, 0, 50) . '...',
                'Failed At' => $job->failed_at,
            ];
        }

        $this->table(
            ['Job ID', 'Queue', 'Exception', 'Failed At'],
            $jobData
        );
    }
} 