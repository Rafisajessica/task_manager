<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    // Seuls Admin et Manager peuvent créer un projet
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager']);
    }

    // Seul le owner ou admin peut modifier
    public function update(User $user, Project $project): bool
    {
        return $user->role === 'admin' || $project->owner_id === $user->id;
    }

    // Seul le owner ou admin peut supprimer
    public function delete(User $user, Project $project): bool
    {
        return $user->role === 'admin' || $project->owner_id === $user->id;
    }
}