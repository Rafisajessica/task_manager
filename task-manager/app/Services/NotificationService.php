<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function send(int $userId, string $type, string $title, string $message, array $data = [])
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    public static function taskAssigned(int $assignedTo, string $taskTitle, string $assignedByName)
    {
        self::send(
            $assignedTo,
            'task_assigned',
            'Nouvelle tâche assignée',
            "{$assignedByName} vous a assigné : {$taskTitle}",
            ['task_title' => $taskTitle]
        );
    }

    public static function taskCommented(int $taskOwnerId, string $taskTitle, string $commenterName)
    {
        self::send(
            $taskOwnerId,
            'task_commented',
            'Nouveau commentaire',
            "{$commenterName} a commenté : {$taskTitle}",
            ['task_title' => $taskTitle]
        );
    }

    public static function taskStatusChanged(int $assignedTo, string $taskTitle, string $newStatus)
    {
        $statusMap = [
            'todo'        => 'À faire',
            'in_progress' => 'En cours',
            'waiting'     => 'En attente',
            'done'        => 'Terminée',
            'cancelled'   => 'Annulée',
        ];
        self::send(
            $assignedTo,
            'task_status_changed',
            'Statut modifié',
            "La tâche \"{$taskTitle}\" est maintenant : " . ($statusMap[$newStatus] ?? $newStatus),
            ['task_title' => $taskTitle, 'status' => $newStatus]
        );
    }
}