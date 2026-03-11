<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\NotificationService;

class CommentController extends Controller
{
    // GET /api/tasks/{task}/comments
    public function index(Task $task)
    {
        return response()->json(
            $task->comments()->with('user:id,name,role')->get()
        );
    }

    // POST /api/tasks/{task}/comments
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user:id,name,role');
        // Notifier le créateur de la tâche
if ($task->user_id && $task->user_id !== $request->user()->id) {
    NotificationService::taskCommented(
        $task->user_id,
        $task->title,
        $request->user()->name
    );
}

// Notifier l'assigné si différent du commenteur
if ($task->assigned_to && 
    $task->assigned_to !== $request->user()->id &&
    $task->assigned_to !== $task->user_id) {
    NotificationService::taskCommented(
        $task->assigned_to,
        $task->title,
        $request->user()->name
    );
}
        return response()->json($comment, 201);
    }

    // DELETE /api/comments/{comment}
    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id &&
            $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Commentaire supprimé']);
    }
}