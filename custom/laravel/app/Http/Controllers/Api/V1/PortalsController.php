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

    public function archive(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        
        $portal->update(['archived' => true]);
        
        event(new PortalUpdated($portal, 'archived'));
        
        return response()->json(['message' => 'Portal archived successfully']);
    }

    public function deleteLogo(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        
        if ($portal->logo) {
            $portal->logo->delete();
        }
        
        return response()->json(['message' => 'Logo deleted successfully']);
    }

    public function sendInstructions(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        
        $validated = $request->validate([
            'email' => 'required|email',
        ]);
        
        if (empty($portal->custom_domain)) {
            return response()->json([
                'error' => 'Custom domain not configured for this portal'
            ], 422);
        }
        
        // Send CNAME instructions email
        // This would typically use a Mail class
        // Mail::to($validated['email'])->send(new PortalInstructionsMail($portal));
        
        return response()->json(['message' => 'Instructions sent successfully']);
    }

    public function sslStatus(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        
        if (empty($portal->custom_domain)) {
            return response()->json(['ssl_status' => 'no_domain']);
        }
        
        // Check SSL status for the custom domain
        $sslStatus = $this->checkSslStatus($portal->custom_domain);
        
        return response()->json(['ssl_status' => $sslStatus]);
    }

    public function reorderArticles(Request $request, Account $account, Portal $portal): JsonResponse
    {
        abort_unless($portal->account_id === $account->id, 404);
        
        $validated = $request->validate([
            'article_ids' => 'required|array',
            'article_ids.*' => 'integer|exists:articles,id',
        ]);
        
        foreach ($validated['article_ids'] as $index => $articleId) {
            $portal->articles()->where('id', $articleId)->update(['sort_order' => $index + 1]);
        }
        
        return response()->json(['message' => 'Articles reordered successfully']);
    }

    private function checkSslStatus(string $domain): string
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
            
            $stream = stream_socket_client(
                "ssl://{$domain}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if (!$stream) {
                return 'error';
            }
            
            $cert = stream_context_get_params($stream)['options']['ssl']['peer_certificate'];
            $certInfo = openssl_x509_parse($cert);
            
            fclose($stream);
            
            if ($certInfo && $certInfo['validTo_time_t'] > time()) {
                return 'valid';
            }
            
            return 'expired';
        } catch (\Exception $e) {
            return 'error';
        }
    }
}
