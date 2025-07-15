<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class TaskController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Admins can see all tasks, users can only see their own
        if (auth()->user()->is_admin) {
            $tasks = Task::with('user')->get();
        } else {
            $tasks = Task::where('user_id', auth()->id())->with('user')->get();
        }

        return response()->json($tasks);
    }

    /**
     * Get tasks assigned to the authenticated user.
     */
    public function myTasks()
    {
        $tasks = Task::where('user_id', auth()->id())->with('user')->get();
        Log::info('User ' . auth()->id() . ' requested their tasks. Found: ' . $tasks->count());
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only admins can create tasks
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            // 'status' => 'required|in:pending,in_progress,completed', // Remove status from validation
            'deadline' => 'nullable|date',
        ]);

        $validated['status'] = 'pending'; // Force status to pending

        $task = Task::create($validated);

        // Send email notification to the assigned user
        $user = User::find($validated['user_id']);
        $emailResult = $this->emailService->sendTaskAssignmentEmail($user, $task);

        $response = $task->load('user');
        $response->email_sent = $emailResult['success'];
        $response->email_sent_at = $emailResult['sent_at'];
        if (!$emailResult['success']) {
            $response->email_error = $emailResult['error'];
        }

        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        // Users can only view their own tasks, admins can view all
        if (!auth()->user()->is_admin && $task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($task->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $user = auth()->user();
        $isAssignedUser = $task->user_id === $user->id;
        $isAdmin = $user->is_admin;

        // Only assigned user or admin can update, but with restrictions
        if (!$isAdmin && !$isAssignedUser) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // If admin, forbid updating status
        if ($isAdmin && $request->has('status')) {
            return response()->json(['message' => 'Admins cannot update task status'], 403);
        }

        // If assigned user, only allow status update
        if ($isAssignedUser && !$isAdmin) {
            $validated = $request->validate([
                'status' => 'required|in:pending,in_progress,completed',
            ]);
            $task->update(['status' => $validated['status']]);
            return response()->json($task->load('user'));
        }

        // If admin, allow updating other fields except status
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'user_id' => 'sometimes|required|exists:users,id',
            'deadline' => 'sometimes|nullable|date',
        ]);

        $task->update($validated);
        $emailResult = ['success' => false, 'error' => null, 'sent_at' => null];

        // If user_id changed, send notification to new user
        if (isset($validated['user_id']) && $validated['user_id'] !== $task->getOriginal('user_id')) {
            $user = User::find($validated['user_id']);
            $emailResult = $this->emailService->sendTaskReassignmentEmail(
                $user, 
                $task, 
                $task->getOriginal('user_id')
            );
        }

        $response = $task->load('user');
        $response->email_sent = $emailResult['success'];
        $response->email_sent_at = $emailResult['sent_at'];
        if (!$emailResult['success'] && isset($validated['user_id'])) {
            $response->email_error = $emailResult['error'];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        // Only admins can delete tasks
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Get email service status
     */
    public function emailStatus()
    {
        // Only admins can check email status
        if (!auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($this->emailService->getEmailStatus());
    }
}
