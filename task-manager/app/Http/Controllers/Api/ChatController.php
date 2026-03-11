<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $userId = $request->user()->id;

        // Sauvegarde le message utilisateur
        ChatMessage::create([
            'user_id' => $userId,
            'role'    => 'user',
            'content' => $request->message
        ]);

        // Contexte utilisateur pour l'IA
        $tasks = Task::where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhere('assigned_to', $userId);
            })
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderByDesc('priority_score')
            ->take(10)
            ->get(['title', 'status', 'deadline', 'priority_score']);

        $context = "Tu es un assistant intelligent de gestion de tâches. " .
    "Voici les tâches actuelles de l'utilisateur : \n" .
    $tasks->map(function($t) {
        $deadline = $t->deadline ? $t->deadline->format('d/m/Y') : 'aucune';
        return "- {$t->title} (statut: {$t->status}, priorité: {$t->priority_score}, deadline: {$deadline})";
    })->implode("\n") .
    "\nRéponds de manière concise et utile en français.";

        // Historique des 5 derniers messages
        $history = ChatMessage::where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content
            ])
            ->values()
            ->toArray();

        // Appel Groq API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type'  => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model'    => 'llama-3.3-70b-versatile',
            'messages' => array_merge(
                [['role' => 'system', 'content' => $context]],
                $history
            ),
            'max_tokens' => 500,
        ]);

        $aiMessage = $response->json()['choices'][0]['message']['content']
            ?? "Désolé, je n'ai pas pu générer une réponse.";

        // Sauvegarde la réponse IA
        ChatMessage::create([
            'user_id' => $userId,
            'role'    => 'assistant',
            'content' => $aiMessage
        ]);

        return response()->json(['message' => $aiMessage]);
    }

    public function history(Request $request)
    {
        $messages = ChatMessage::where('user_id', $request->user()->id)
            ->orderBy('created_at')
            ->get();

        return response()->json($messages);
    }
}