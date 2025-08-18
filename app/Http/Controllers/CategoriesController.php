<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name',
            'description' => 'nullable|string'
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id,
            'description' => 'nullable|string'
        ]);

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
