<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Category::where('user_id', $request->user()->id)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'required|string|max:7'
        ]);

        $category = Category::create([
            ...$validated,
            'user_id' => $request->user()->id
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'  => 'sometimes|string|max:50',
            'color' => 'sometimes|string|max:7'
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }
}