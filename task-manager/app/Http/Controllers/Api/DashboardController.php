<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Stats des tâches
        $taskStats = Task::where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tâches urgentes (deadline dans les 3 prochains jours)
        $urgentTasks = Task::with(['project'])
            ->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<=', now()->addDays(3))
            ->orderByDesc('priority_score')
            ->take(5)
            ->get();

        // Tâches en retard
        $lateTasks = Task::where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where('deadline', '<', now())
            ->count();

        // Projets actifs
        $projects = Project::where('owner_id', $userId)
            ->where('status', 'active')
            ->withCount('tasks')
            ->get()
            ->map(function($project) {
                $done = $project->tasks()->where('status', 'done')->count();
                $total = $project->tasks_count;
                $project->completion = $total > 0
                    ? (int) round(($done / $total) * 100)
                    : 0;
                return $project;
            });

        return response()->json([
            'task_stats'   => $taskStats,
            'urgent_tasks' => $urgentTasks,
            'late_tasks'   => $lateTasks,
            'projects'     => $projects,
        ]);
    }
}