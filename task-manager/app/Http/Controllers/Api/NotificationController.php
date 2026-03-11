<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json($notifications);
    }

    // GET /api/notifications/unread-count
    public function unreadCount(Request $request)
    {
        $count = $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    // PUT /api/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $request->user()
            ->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Marquée comme lue']);
    }

    // PUT /api/notifications/read-all
    public function markAllRead(Request $request)
    {
        $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Toutes marquées comme lues']);
    }
}