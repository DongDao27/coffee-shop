<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::query()
            ->orderBy('id')
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->ignore($category->id),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $category->update($validated);

        return response()->json($category->fresh());
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return response()->noContent();
    }
}
