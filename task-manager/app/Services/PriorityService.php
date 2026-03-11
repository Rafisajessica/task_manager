<?php

namespace App\Services;

use App\Models\Task;
use App\Models\PriorityLog;

class PriorityService
{
    public function calculate(Task $task): float
    {
        $scores = [
            'deadline'    => $this->deadlineScore($task),
            'complexity'  => $this->complexityScore($task),
            'project'     => $this->projectScore($task),
            'workload'    => $this->workloadScore($task),
        ];

        $final = (
            $scores['deadline']   * 0.40 +
            $scores['complexity'] * 0.25 +
            $scores['project']    * 0.20 +
            $scores['workload']   * 0.15
        );

        $final = min(round($final, 2), 100);

        // Sauvegarde le log
        PriorityLog::create([
            'task_id'   => $task->id,
            'old_score' => $task->priority_score,
            'new_score' => $final,
            'reason'    => $scores,
        ]);

        return $final;
    }

    // Facteur 1 — Urgence deadline (0-100)
    private function deadlineScore(Task $task): float
    {
        if (!$task->deadline) return 20;

        $daysLeft = now()->diffInDays($task->deadline, false);

        if ($daysLeft < 0)   return 100; // déjà en retard
        if ($daysLeft <= 1)  return 90;
        if ($daysLeft <= 3)  return 75;
        if ($daysLeft <= 7)  return 55;
        if ($daysLeft <= 14) return 35;
        return 15;
    }

    // Facteur 2 — Complexité déclarée (0-100)
    private function complexityScore(Task $task): float
    {
        return ($task->complexity / 5) * 100;
    }

    // Facteur 3 — Importance du projet (0-100)
    private function projectScore(Task $task): float
    {
        if (!$task->project) return 20;

        return match($task->project->priority_level) {
            'critical' => 100,
            'high'     => 75,
            'normal'   => 50,
            'low'      => 25,
            default    => 20,
        };
    }

    // Facteur 4 — Charge de travail de l'assigné (0-100)
    private function workloadScore(Task $task): float
    {
        if (!$task->assigned_to) return 20;

        $activeTasks = Task::where('assigned_to', $task->assigned_to)
            ->whereIn('status', ['todo', 'in_progress'])
            ->where('id', '!=', $task->id)
            ->count();

        if ($activeTasks >= 10) return 100;
        if ($activeTasks >= 7)  return 75;
        if ($activeTasks >= 4)  return 50;
        if ($activeTasks >= 2)  return 30;
        return 10;
    }
}