<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\Portal\PortalUpdated;
use App\Models\Account;
use App\Models\Portal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalsController extends Controller
{
    public function index(Account $account): JsonResource
    {
        $portals = Portal::where('account_id', $account->id)
            ->withCount(['articles', 'categories'])
            ->paginate();

        return JsonResource::collection($portals);
    }

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

        $portal = Portal::create(array_merge($validated, ['account_id' => $account->id]));

        event(new PortalUpdated($portal, 'created'));

        return response()->json(['data' => $portal], 201);
    }

    public function show(Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        return response()->json(['data' => $portal->loadCount(['articles', 'categories'])]);
    }

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

        $portal->refresh();

        event(new PortalUpdated($portal, 'updated'));

        return response()->json(['data' => $portal]);
    }

    public function destroy(Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        event(new PortalUpdated($portal, 'deleted'));

        $portal->delete();

        return response()->json(null, 204);
    }

    public function articles(Account $account, Portal $portal): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        return JsonResource::collection($portal->articles()->paginate());
    }

    public function categories(Account $account, Portal $portal): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        return JsonResource::collection($portal->categories()->paginate());
    }

    public function archive(Request $request, $portal): JsonResponse
    {
        // TODO: Implement archive logic
        return response()->json(['message' => 'Portal archived']);
    }

    public function deleteLogo(Request $request, $portal): JsonResponse
    {
        // TODO: Implement logo deletion logic
        return response()->json(['message' => 'Logo deleted']);
    }

    public function sendInstructions(Request $request, $portal): JsonResponse
    {
        // TODO: Implement send instructions logic
        return response()->json(['message' => 'Instructions sent']);
    }

    public function sslStatus(Request $request, $portal): JsonResponse
    {
        // TODO: Implement SSL status logic
        return response()->json(['ssl_status' => 'unknown']);
    }

    public function reorderArticles(Request $request, $portal): JsonResponse
    {
        // TODO: Implement reorder logic
        return response()->json(['message' => 'Articles reordered']);
    }
}
