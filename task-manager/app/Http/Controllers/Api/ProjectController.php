<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // GET /api/projects
    public function index(Request $request)
    {
         
        $projects = Project::with(['owner', 'tasks'])
            ->where('owner_id', $request->user()->id)
            ->get()
            ->map(function($project) {
                $project->completion = $this->completion($project);
                return $project;
            });

        return response()->json($projects);
    }

    // POST /api/projects
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string',
            'priority_level' => 'in:low,normal,high,critical',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create([
            ...$validated,
            'owner_id' => $request->user()->id,
        ]);

        return response()->json($project, 201);
    }

    // GET /api/projects/{id}
    public function show(Project $project)
    {
        $project->load(['owner', 'tasks.assignee']);
        $project->completion = $this->completion($project);
        return response()->json($project);
    }

    // PUT /api/projects/{id}
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:150',
            'description'    => 'nullable|string',
            'status'         => 'in:active,archived,completed',
            'priority_level' => 'in:low,normal,high,critical',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
        ]);

        $project->update($validated);
        return response()->json($project);
    }

    // DELETE /api/projects/{id}
    public function destroy(Project $project)
    {
         $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Projet supprimé']);
    }

    private function completion(Project $project): int
    {
        $total = $project->tasks->count();
        if ($total === 0) return 0;
        $done = $project->tasks->where('status', 'done')->count();
        return (int) round(($done / $total) * 100);
    }
}