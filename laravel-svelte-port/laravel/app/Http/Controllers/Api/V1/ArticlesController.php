<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\Article\ArticleUpdated;
use App\Models\Account;
use App\Models\Article;
use App\Models\Portal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticlesController extends Controller
{
    /**
     * Display a listing of articles for a portal.
     */
    public function index(Account $account, Portal $portal, Request $request): JsonResource
    {
        abort_unless($portal->account_id === $account->id, 404);

        $query = Article::where('portal_id', $portal->id);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return JsonResource::collection($query->paginate());
    }

    /**
     * Store a newly created article.
     */
    public function store(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'string|in:draft,published,archived',
            'category_id' => 'nullable|exists:categories,id',
            'author_id' => 'nullable|exists:users,id',
            'meta' => 'nullable|array',
        ]);

        $article = Article::create([
            ...$validated,
            'portal_id' => $portal->id,
            'account_id' => $account->id,
            'author_id' => $validated['author_id'] ?? auth()->id(),
            'status' => $this->normalizeStatus($validated['status'] ?? null, Article::STATUS_DRAFT),
        ]);

        event(new ArticleUpdated($article, 'created'));

        return response()->json(['data' => $article], 201);
    }

    /**
     * Display the specified article.
     */
    public function show(Account $account, Portal $portal, Article $article): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($article->portal_id === $portal->id, 404);

        return response()->json(['data' => $article->load(['category', 'author'])]);
    }

    /**
     * Update the specified article.
     */
    public function update(Request $request, Account $account, Portal $portal, Article $article): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($article->portal_id === $portal->id, 404);

        $previousStatus = $article->status;

        $validated = $request->validate([
            'title' => 'string|max:255',
            'slug' => 'string|max:255',
            'content' => 'string',
            'description' => 'nullable|string',
            'status' => 'string|in:draft,published,archived',
            'category_id' => 'nullable|exists:categories,id',
            'meta' => 'nullable|array',
        ]);

        if (array_key_exists('status', $validated)) {
            $validated['status'] = $this->normalizeStatus($validated['status'], $article->status);
        }

        $article->update($validated);

        $article->refresh();

        event(new ArticleUpdated($article, $this->resolveAction($previousStatus, $article->status), $previousStatus));

        return response()->json(['data' => $article]);
    }

    /**
     * Remove the specified article.
     */
    public function destroy(Account $account, Portal $portal, Article $article): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        abort_unless($article->portal_id === $portal->id, 404);

        event(new ArticleUpdated($article, 'deleted'));

        $article->delete();

        return response()->json(null, 204);
    }

    private function normalizeStatus(?string $status, int $default): int
    {
        if (is_null($status)) {
            return $default;
        }

        return match ($status) {
            'published' => Article::STATUS_PUBLISHED,
            'archived' => Article::STATUS_ARCHIVED,
            default => Article::STATUS_DRAFT,
        };
    }

    private function resolveAction(int $previousStatus, int $currentStatus): string
    {
        if ($previousStatus !== $currentStatus) {
            return match ($currentStatus) {
                Article::STATUS_PUBLISHED => 'published',
                Article::STATUS_ARCHIVED => 'archived',
                default => 'updated',
            };
        }

        return 'updated';
    }
}
