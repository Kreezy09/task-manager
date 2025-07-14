<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class QueueController extends Controller
{
    /**
     * Get queue statistics
     */
    public function stats()
    {
        // Only admins can view queue stats
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $stats = Cache::remember('queue_stats', 30, function () {
            return [
                'pending_jobs' => DB::table('jobs')->count(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'email_jobs' => DB::table('jobs')
                    ->where('payload', 'like', '%TaskAssigned%')
                    ->count(),
                'failed_email_jobs' => DB::table('failed_jobs')
                    ->where('payload', 'like', '%TaskAssigned%')
                    ->count(),
                'last_updated' => now()->toISOString(),
            ];
        });

        return response()->json($stats);
    }

    /**
     * Get pending email jobs
     */
    public function pendingEmails()
    {
        // Only admins can view queue details
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $jobs = DB::table('jobs')
            ->where('payload', 'like', '%TaskAssigned%')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'attempts' => $job->attempts,
                    'created_at' => $job->created_at,
                    'available_at' => $job->available_at,
                    'reserved_at' => $job->reserved_at,
                ];
            });

        return response()->json($jobs);
    }

    /**
     * Get failed email jobs
     */
    public function failedEmails()
    {
        // Only admins can view queue details
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $jobs = DB::table('failed_jobs')
            ->where('payload', 'like', '%TaskAssigned%')
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'exception' => $job->exception,
                    'failed_at' => $job->failed_at,
                ];
            });

        return response()->json($jobs);
    }

    /**
     * Retry a failed job
     */
    public function retryJob(Request $request, $jobId)
    {
        // Only admins can retry jobs
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            \Artisan::call('queue:retry', ['id' => $jobId]);
            return response()->json(['message' => 'Job retry initiated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retry job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Clear all failed jobs
     */
    public function clearFailedJobs()
    {
        // Only admins can clear failed jobs
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            \Artisan::call('queue:flush');
            return response()->json(['message' => 'All failed jobs cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to clear failed jobs: ' . $e->getMessage()], 500);
        }
    }
} 