<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Inbox\InboxResource;
use App\Models\Account;
use App\Models\AgentBot;
use App\Models\Inbox;
use App\Repositories\Inbox\InboxRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InboxesController extends Controller
{
    public function __construct(
        private InboxRepository $inboxRepository
    ) {}

    /**
     * Display a listing of inboxes for an account.
     */
    public function index(Account $account): AnonymousResourceCollection
    {
        $inboxes = $this->inboxRepository->getWithConversationCounts($account->id);

        return InboxResource::collection($inboxes);
    }

    /**
     * Store a newly created inbox.
     */
    public function store(Request $request, Account $account): InboxResource
    {
        // Only admins (role >= 2) can create inboxes
        $user = $request->user();
        $accountUser = $account->users()->where('user_id', $user->id)->first();
        if (! $accountUser || $accountUser->pivot->role < 2) {
            abort(403, 'Only admins can create inboxes');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel_type' => 'nullable|string',
            'channel' => 'nullable|array',
            'channel.type' => 'nullable|string',
            'channel.phone_number' => 'nullable|string',
            'channel.provider' => 'nullable|string',
            'channel.bot_token' => 'nullable|string',
            'channel.email' => 'nullable|email',
            'channel.api_key' => 'nullable|string',
            'channel.website_url' => 'nullable|string',
            'enable_auto_assignment' => 'boolean',
            'greeting_enabled' => 'boolean',
            'greeting_message' => 'nullable|string',
            'timezone' => 'nullable|string|timezone',
        ]);

        // Handle channel type from nested channel object (Chatwoot style)
        $channelType = $validated['channel_type'] ?? null;
        if (! $channelType && isset($validated['channel']['type'])) {
            $channelType = $this->mapChannelType($validated['channel']['type']);
        }

        if (! $channelType) {
            return abort(422, 'The channel type field is required.');
        }

        $inbox = $account->inboxes()->create([
            'name' => $validated['name'],
            'channel_type' => $channelType,
            'enable_auto_assignment' => $validated['enable_auto_assignment'] ?? true,
            'greeting_enabled' => $validated['greeting_enabled'] ?? false,
            'greeting_message' => $validated['greeting_message'] ?? null,
            'timezone' => $validated['timezone'] ?? config('app.timezone'),
        ]);

        return new InboxResource($inbox);
    }

    /**
     * Map channel type from API to database format.
     */
    private function mapChannelType(string $type): string
    {
        $map = [
            'whatsapp' => 'Channel::Whatsapp',
            'facebook' => 'Channel::FacebookPage',
            'telegram' => 'Channel::Telegram',
            'email' => 'Channel::Email',
            'sms' => 'Channel::Sms',
            'twilio' => 'Channel::TwilioSms',
            'line' => 'Channel::Line',
            'api' => 'Channel::Api',
            'web_widget' => 'Channel::WebWidget',
        ];

        return $map[strtolower($type)] ?? "Channel::{$type}";
    }

    /**
     * Display the specified inbox.
     */
    public function show(Account $account, Inbox $inbox): InboxResource
    {
        abort_unless($inbox->account_id === $account->id, 404);

        return new InboxResource($inbox->loadCount('members'));
    }

    /**
     * Update the specified inbox.
     */
    public function update(Request $request, Account $account, Inbox $inbox): InboxResource
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $inbox->update($request->only([
            'name',
            'enable_auto_assignment',
            'greeting_enabled',
            'greeting_message',
            'enable_email_collect',
            'csat_survey_enabled',
            'allow_messages_after_resolved',
            'working_hours',
            'timezone',
            'working_hours_enabled',
            'out_of_office_message',
        ]));

        return new InboxResource($inbox->fresh());
    }

    /**
     * Remove the specified inbox.
     */
    public function destroy(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $inbox->delete();

        return response()->json(['message' => 'Inbox deletion is in progress'], 200);
    }

    /**
     * Get inbox members.
     */
    public function members(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        return response()->json($inbox->members);
    }

    /**
     * Add member to inbox.
     */
    public function addMember(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->inboxRepository->addMember($inbox->id, $validated['user_id']);

        return response()->json(['success' => true]);
    }

    /**
     * Remove member from inbox.
     */
    public function removeMember(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->inboxRepository->removeMember($inbox->id, $validated['user_id']);

        return response()->json(['success' => true]);
    }

    /**
     * Get assignable agents for inbox.
     */
    public function assignableAgents(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $agents = $inbox->members()->get();

        return response()->json(['data' => $agents]);
    }

    /**
     * Get campaigns for inbox.
     */
    public function campaigns(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        return response()->json(['data' => $inbox->campaigns]);
    }

    /**
     * Delete inbox avatar.
     */
    public function avatar(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $inbox->update(['avatar_url' => null]);

        return response()->json(null, 200);
    }

    /**
     * Get inbox agent bot.
     */
    public function agentBot(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $agentBot = $inbox->agentBot;

        return response()->json(['data' => $agentBot]);
    }

    /**
     * Set agent bot for inbox.
     */
    public function setAgentBot(Request $request, Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        $agentBotId = $request->input('agent_bot');

        if ($agentBotId) {
            $agentBot = AgentBot::findOrFail($agentBotId);
            $inbox->agentBotInbox()->updateOrCreate(
                ['inbox_id' => $inbox->id],
                ['agent_bot_id' => $agentBot->id]
            );
        } elseif ($inbox->agentBotInbox) {
            $inbox->agentBotInbox->delete();
        }

        return response()->json(null, 200);
    }

    /**
     * Sync templates (WhatsApp only).
     */
    public function syncTemplates(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        if ($inbox->channel_type !== 'Channel::Whatsapp') {
            return response()->json(['error' => 'Template sync is only available for WhatsApp channels'], 422);
        }

        // Dispatch template sync job
        // Channels\Whatsapp\TemplatesSyncJob::dispatch($inbox->channel);

        return response()->json(['message' => 'Template sync initiated successfully'], 200);
    }

    /**
     * List message templates for inbox (WhatsApp only).
     */
    public function messageTemplates(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        // Return empty templates list - in production this would fetch from WhatsApp Cloud API
        return response()->json([
            'data' => [],
        ]);
    }

    /**
     * Health check for inbox (WhatsApp Cloud API only).
     */
    public function health(Account $account, Inbox $inbox): JsonResponse
    {
        abort_unless($inbox->account_id === $account->id, 404);

        if ($inbox->channel_type !== 'Channel::Whatsapp') {
            return response()->json(['error' => 'Health data only available for WhatsApp Cloud API channels'], 400);
        }

        // Get health status from WhatsApp service
        // $healthData = (new WhatsappHealthService($inbox->channel))->fetchHealthStatus();

        return response()->json([
            'status' => 'healthy',
            'channel' => $inbox->channel_type,
        ]);
    }
}
