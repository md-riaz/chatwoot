<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CsatSurveyResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CsatSurveyResponsesController extends Controller
{
    /**
     * Display a listing of CSAT survey responses for an account.
     */
    public function index(Account $account, Request $request): JsonResource
    {
        $query = CsatSurveyResponse::where('account_id', $account->id);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('inbox_id')) {
            $query->whereHas('conversation', function ($q) use ($request) {
                $q->where('inbox_id', $request->inbox_id);
            });
        }

        if ($request->has('since')) {
            $query->where('created_at', '>=', $request->since);
        }

        if ($request->has('until')) {
            $query->where('created_at', '<=', $request->until);
        }

        return JsonResource::collection($query->paginate());
    }

    /**
     * Display the specified CSAT survey response.
     */
    public function show(Account $account, CsatSurveyResponse $csatSurveyResponse): JsonResponse
    {
        abort_unless($csatSurveyResponse->account_id === $account->id, 404);

        return response()->json([
            'data' => $csatSurveyResponse->load(['conversation', 'contact', 'assignedAgent'])
        ]);
    }

    /**
     * Get CSAT metrics for an account.
     */
    public function metrics(Account $account, Request $request): JsonResponse
    {
        $query = CsatSurveyResponse::where('account_id', $account->id);

        if ($request->has('since')) {
            $query->where('created_at', '>=', $request->since);
        }

        if ($request->has('until')) {
            $query->where('created_at', '<=', $request->until);
        }

        $responses = $query->get();
        
        $totalResponses = $responses->count();
        $satisfiedResponses = $responses->where('rating', '>=', 4)->count();
        $averageRating = $responses->avg('rating');

        return response()->json([
            'data' => [
                'total_responses' => $totalResponses,
                'satisfaction_score' => $totalResponses > 0 
                    ? round(($satisfiedResponses / $totalResponses) * 100, 2) 
                    : 0,
                'average_rating' => round($averageRating ?? 0, 2),
                'response_rate' => 0, // Would need conversation count
            ]
        ]);
    }

    /**
     * Download CSAT responses as CSV.
     */
    public function download(Account $account, Request $request): JsonResponse
    {
        $query = CsatSurveyResponse::where('account_id', $account->id);

        if ($request->has('since')) {
            $query->where('created_at', '>=', $request->since);
        }

        if ($request->has('until')) {
            $query->where('created_at', '<=', $request->until);
        }

        $responses = $query->with(['conversation', 'contact', 'assignedAgent'])->get();

        // In a real implementation, this would generate and return a CSV file
        return response()->json([
            'message' => 'CSV download initiated',
            'count' => $responses->count(),
        ]);
    }
}
