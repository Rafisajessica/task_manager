<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(
            User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'role'      => 'sometimes|in:admin,manager,collaborator',
            'is_active' => 'sometimes|boolean',
            'name'      => 'sometimes|string|max:255',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Impossible de supprimer votre propre compte'], 422);
        }

        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}