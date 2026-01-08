<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SegmentsController extends Controller
{
    /**
     * Display a listing of segments for an account.
     */
    public function index(Account $account): JsonResource
    {
        $segments = DB::table('segments')
            ->where('account_id', $account->id)
            ->paginate();

        return JsonResource::collection($segments);
    }

    /**
     * Store a newly created segment.
     */
    public function store(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'query' => 'required|array',
        ]);

        $segmentId = DB::table('segments')->insertGetId([
            ...$validated,
            'query' => json_encode($validated['query']),
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $segment = DB::table('segments')->find($segmentId);

        return response()->json(['data' => $segment], 201);
    }

    /**
     * Display the specified segment.
     */
    public function show(Account $account, int $segmentId): JsonResponse
    {
        $segment = DB::table('segments')
            ->where('account_id', $account->id)
            ->where('id', $segmentId)
            ->first();

        abort_unless($segment, 404);

        return response()->json(['data' => $segment]);
    }

    /**
     * Update the specified segment.
     */
    public function update(Request $request, Account $account, int $segmentId): JsonResponse
    {
        $segment = DB::table('segments')
            ->where('account_id', $account->id)
            ->where('id', $segmentId)
            ->first();

        abort_unless($segment, 404);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'query' => 'array',
        ]);

        if (isset($validated['query'])) {
            $validated['query'] = json_encode($validated['query']);
        }

        DB::table('segments')
            ->where('id', $segmentId)
            ->update([
                ...$validated,
                'updated_at' => now(),
            ]);

        $segment = DB::table('segments')->find($segmentId);

        return response()->json(['data' => $segment]);
    }

    /**
     * Remove the specified segment.
     */
    public function destroy(Account $account, int $segmentId): JsonResponse
    {
        $segment = DB::table('segments')
            ->where('account_id', $account->id)
            ->where('id', $segmentId)
            ->first();

        abort_unless($segment, 404);

        DB::table('segments')->where('id', $segmentId)->delete();

        return response()->json(null, 204);
    }

    /**
     * Get contacts in a segment.
     */
    public function contacts(Account $account, int $segmentId): JsonResource
    {
        $segment = DB::table('segments')
            ->where('account_id', $account->id)
            ->where('id', $segmentId)
            ->first();

        abort_unless($segment, 404);

        // Parse segment query and fetch matching contacts
        // This would need to implement the query builder logic
        $contacts = Contact::where('account_id', $account->id)
            ->paginate();

        return JsonResource::collection($contacts);
    }

    /**
     * Get contact count for a segment.
     */
    public function count(Account $account, int $segmentId): JsonResponse
    {
        $segment = DB::table('segments')
            ->where('account_id', $account->id)
            ->where('id', $segmentId)
            ->first();

        abort_unless($segment, 404);

        // Parse segment query and count matching contacts
        $count = Contact::where('account_id', $account->id)->count();

        return response()->json(['data' => ['count' => $count]]);
    }
}
