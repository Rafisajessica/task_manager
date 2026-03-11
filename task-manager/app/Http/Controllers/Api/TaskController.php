<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\PriorityService;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Models\User;

class TaskController extends Controller
{
    protected PriorityService $priorityService;

    public function __construct(PriorityService $priorityService)
    {
        $this->priorityService = $priorityService;
    }

    // GET /api/tasks
    public function index(Request $request)
    {
        $tasks = Task::with(['project', 'assignee', 'categories'])
            ->where(function($q) use ($request) {
                $q->where('created_by', $request->user()->id)
                  ->orWhere('assigned_to', $request->user()->id);
            })
            ->orderByDesc('priority_score')
            ->get();

        return response()->json($tasks);
    }

    // POST /api/tasks
    public function store(Request $request)

    {
        $this->authorize('create', Task::class);
        $validated = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'project_id'  => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'complexity'  => 'integer|between:1,5',
            'deadline'    => 'nullable|date',
            'status'      => 'in:todo,in_progress,waiting,done,cancelled',
           

        ]);


        $task = Task::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status'     => $validated['status'] ?? 'todo',
            'complexity' => $validated['complexity'] ?? 3,
        
            ]);
            // Notifier si tâche assignée
if ($task->assigned_to && $task->assigned_to !== $request->user()->id) {
    NotificationService::taskAssigned(
        $task->assigned_to,
        $task->title,
        $request->user()->name
    );
}

        // Calcul automatique du score IA
        $task->load('project');
        $score = $this->priorityService->calculate($task);
        $task->update(['priority_score' => $score]);

        return response()->json($task->fresh(['project', 'assignee']), 201);
    }

    // GET /api/tasks/{id}
    public function show(Task $task)
    {
        $task->load(['project', 'assignee', 'creator', 'categories', 'comments.user']);
        return response()->json($task);
    }

    // PUT /api/tasks/{id}
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $validated = $request->validate([
            'title'       => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'project_id'  => 'nullable|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'complexity'  => 'sometimes|integer|between:1,5',
            'deadline'    => 'nullable|date',
            'status'      => 'sometimes|in:todo,in_progress,waiting,done,cancelled',
        ]);
        // Notifier si assigné change
if (isset($validated['assigned_to']) && $validated['assigned_to'] && 
    $validated['assigned_to'] !== $request->user()->id) {
    NotificationService::taskAssigned(
        $validated['assigned_to'],
        $task->title,
        $request->user()->name
    );
}

// Notifier si statut change et tâche assignée
if (isset($validated['status']) && $task->assigned_to && 
    $task->assigned_to !== $request->user()->id) {
    NotificationService::taskStatusChanged(
        $task->assigned_to,
        $task->title,
        $validated['status']
    );
}

        $task->update($validated);

        // Recalcul automatique du score IA
        $task->load('project');
        $score = $this->priorityService->calculate($task);
        $task->update(['priority_score' => $score]);

        return response()->json($task->fresh(['project', 'assignee']));
    }

    // DELETE /api/tasks/{id}
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return response()->json(['message' => 'Tâche supprimée']);
    }
}