<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Integrations\Dyte\DyteService;
use App\Services\Integrations\Linear\LinearService;
use App\Services\Integrations\Shopify\ShopifyService;
use App\Services\Integrations\Slack\SlackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationsController extends Controller
{
    public function processEvent(Request $request, $hook): JsonResponse
    {
        // Process integration webhook events
        // This would route to specific integration processors based on the hook type
        return response()->json(['message' => 'Event processed']);
    }

    public function listAllChannels(Request $request): JsonResponse
    {
        $slackService = new SlackService(auth()->user()->account);
        $result = $slackService->listChannels();
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function createAMeeting(Request $request): JsonResponse
    {
        $conversation = auth()->user()->account->conversations()
            ->findOrFail($request->input('conversation_id'));
            
        $dyteService = new DyteService(auth()->user()->account, $conversation);
        $result = $dyteService->createMeeting(auth()->user());
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function addParticipantToMeeting(Request $request): JsonResponse
    {
        $conversation = auth()->user()->account->conversations()
            ->findOrFail($request->input('conversation_id'));
            
        $dyteService = new DyteService(auth()->user()->account, $conversation);
        $result = $dyteService->addParticipantToMeeting(
            $request->input('meeting_id'),
            auth()->user()
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function shopifyAuth(Request $request): JsonResponse
    {
        $request->validate([
            'shop_domain' => 'required|string',
        ]);
        
        $shopifyService = new ShopifyService(auth()->user()->account);
        $result = $shopifyService->generateAuthUrl($request->input('shop_domain'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function shopifyOrders(Request $request): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
        ]);
        
        $contact = auth()->user()->account->contacts()
            ->findOrFail($request->input('contact_id'));
            
        $shopifyService = new ShopifyService(auth()->user()->account);
        $result = $shopifyService->fetchCustomerOrders($contact);
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearTeams(Request $request): JsonResponse
    {
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->getTeams();
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearTeamEntities(Request $request): JsonResponse
    {
        $request->validate([
            'team_id' => 'required|string',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->getTeamEntities($request->input('team_id'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearCreateIssue(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'team_id' => 'required|string',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|string',
            'priority' => 'nullable|integer',
            'state_id' => 'nullable|string',
            'label_ids' => 'nullable|array',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->createIssue($request->validated());
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearLinkIssue(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'issue_id' => 'required|string',
            'title' => 'required|string',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->linkIssue(
            $request->input('url'),
            $request->input('issue_id'),
            $request->input('title')
        );
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearUnlinkIssue(Request $request): JsonResponse
    {
        $request->validate([
            'link_id' => 'required|string',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->unlinkIssue($request->input('link_id'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearSearchIssue(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->searchIssues($request->input('q'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }

    public function linearLinkedIssues(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ]);
        
        $linearService = new LinearService(auth()->user()->account);
        $result = $linearService->getLinkedIssues($request->input('url'));
        
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 422);
        }
        
        return response()->json($result);
    }
}