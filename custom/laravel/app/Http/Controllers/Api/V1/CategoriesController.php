<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriesController extends Controller
{
    /**
     * Display a listing of categories for a portal.
     */
    public function index(Account $account, Portal $portal): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        $categories = Category::where('portal_id', $portal->id)
            ->withCount('articles')
            ->paginate();

        return JsonResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
            'icon' => 'nullable|string',
            'parent_category_id' => 'nullable|exists:categories,id',
            'locale' => 'nullable|string|max:10',
        ]);

        $category = Category::create([
            ...$validated,
            'portal_id' => $portal->id,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $category], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Account $account, Portal $portal, Category $category): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($category->portal_id === $portal->id, 404);

        return response()->json([
            'data' => $category->loadCount('articles')
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Account $account, Portal $portal, Category $category): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($category->portal_id === $portal->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer',
            'icon' => 'nullable|string',
            'parent_category_id' => 'nullable|exists:categories,id',
            'locale' => 'nullable|string|max:10',
        ]);

        $category->update($validated);

        return response()->json(['data' => $category]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Account $account, Portal $portal, Category $category): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($category->portal_id === $portal->id, 404);

        $category->delete();

        return response()->json(null, 204);
    }
}
