<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Admin et Manager peuvent créer des tâches
    // Collaborateur peut créer uniquement si assigné à un projet
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    // Modifier : owner, assigné, ou admin
    public function update(User $user, Task $task): bool
    {
        return $user->role === 'admin'
            || $task->created_by === $user->id
            || $task->assigned_to === $user->id;
    }

    // Supprimer : owner ou admin seulement
    public function delete(User $user, Task $task): bool
    {
        return $user->role === 'admin'
            || $task->created_by === $user->id;
    }
}