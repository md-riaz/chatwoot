<?php

namespace App\Http\Controllers\Api\V1\Channels;

use App\Http\Controllers\Controller;
use App\Jobs\Channels\ProcessTiktokWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TiktokController extends Controller
{
    /**
     * TikTok webhook verification handler.
     */
    public function verify(Request $request): JsonResponse
    {
        // TikTok sends a challenge query param on verification.
        if ($request->has('challenge')) {
            return response()->json(['challenge' => $request->query('challenge')]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * TikTok webhook event handler.
     */
    public function webhook(Request $request): Response
    {
        $payload = $request->all();
        Log::info('Received TikTok webhook', [
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        // Dispatch processing to the queue to mirror other channel webhooks.
        ProcessTiktokWebhookJob::dispatch($payload);

        // Acknowledge receipt; downstream processing should be wired to a Job/Action.
        return response()->noContent();
    }
}
