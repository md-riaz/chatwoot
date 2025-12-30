<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Portal;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalsController extends Controller
    /**
     * Archive a portal.
     */
    public function archive(Request $request, $portal): JsonResponse
    {
        // TODO: Implement archive logic
        return response()->json(['message' => 'Portal archived']);
    }

    /**
     * Delete portal logo.
     */
    public function deleteLogo(Request $request, $portal): JsonResponse
    {
        // TODO: Implement logo deletion logic
        return response()->json(['message' => 'Logo deleted']);
    }

    /**
     * Send instructions for portal.
     */
    public function sendInstructions(Request $request, $portal): JsonResponse
    {
        // TODO: Implement send instructions logic
        return response()->json(['message' => 'Instructions sent']);
    }

    /**
     * Get SSL status for portal.
     */
    public function sslStatus(Request $request, $portal): JsonResponse
    {
        // TODO: Implement SSL status logic
        return response()->json(['ssl_status' => 'unknown']);
    }

    /**
     * Reorder articles in a portal.
     */
    public function reorderArticles(Request $request, $portal): JsonResponse
    {
        // TODO: Implement reorder logic
        return response()->json(['message' => 'Articles reordered']);
    }
{
    /**
     * Display a listing of portals for an account.
     */
    public function index(Account $account): JsonResource
    {
        $portals = Portal::where('account_id', $account->id)
            ->withCount(['articles', 'categories'])
            ->paginate();

        return JsonResource::collection($portals);
    }

    /**
     * Store a newly created portal.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:portals,slug',
            'custom_domain' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'homepage_link' => 'nullable|url',
            'page_title' => 'nullable|string',
            'header_text' => 'nullable|string',
            'archived' => 'boolean',
        ]);

        $portal = Portal::create([
            ...$validated,
            'account_id' => $account->id,
        ]);

        return response()->json(['data' => $portal], 201);
    }

    /**
     * Display the specified portal.
     */
    public function show(Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        return response()->json([
            'data' => $portal->loadCount(['articles', 'categories'])
        ]);
    }

    /**
     * Update the specified portal.
     */
    public function update(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'slug' => 'string|max:255|unique:portals,slug,' . $portal->id,
            'custom_domain' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'homepage_link' => 'nullable|url',
            'page_title' => 'nullable|string',
            'header_text' => 'nullable|string',
            'archived' => 'boolean',
        ]);

        $portal->update($validated);

        return response()->json(['data' => $portal]);
    }

    /**
     * Remove the specified portal.
     */
    public function destroy(Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        $portal->delete();

        return response()->json(null, 204);
    }

    /**
     * List articles for a portal.
     */
    public function articles(Account $account, Portal $portal): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        return JsonResource::collection($portal->articles()->paginate());
    }

    /**
     * List categories for a portal.
     */
    public function categories(Account $account, Portal $portal): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        return JsonResource::collection($portal->categories()->paginate());
    }
}
