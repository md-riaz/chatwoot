<?php

namespace App\Services\Integrations\Dyte;

use App\Models\Account;
use App\Models\Conversation;
use App\Models\Integration\Hook;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DyteService
{
    private const API_BASE_URL = 'https://api.cluster.dyte.in/v2';

    public function __construct(
        private Account $account,
        private Conversation $conversation
    ) {}

    public function createMeeting(User $agent): array
    {
        $hook = $this->getDyteHook();
        
        if (!$hook) {
            return ['error' => 'Dyte integration not configured'];
        }

        try {
            $title = "Meeting with {$agent->name}";
            $meeting = $this->createDyteMeeting($title, $hook);
            
            if (isset($meeting['error'])) {
                return $meeting;
            }

            // Create integration message
            $message = $this->conversation->messages()->create([
                'account_id' => $this->conversation->account_id,
                'inbox_id' => $this->conversation->inbox_id,
                'message_type' => 'outgoing',
                'content_type' => 'integrations',
                'content' => $title,
                'content_attributes' => [
                    'type' => 'dyte',
                    'data' => [
                        'meeting_id' => $meeting['id'],
                    ],
                ],
                'sender_type' => 'User',
                'sender_id' => $agent->id,
            ]);

            return [
                'meeting' => $meeting,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            Log::error('Dyte API error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function addParticipantToMeeting(string $meetingId, User $user): array
    {
        $hook = $this->getDyteHook();
        
        if (!$hook) {
            return ['error' => 'Dyte integration not configured'];
        }

        try {
            $participant = $this->addParticipant($meetingId, $user, $hook);
            
            return $participant;
        } catch (\Exception $e) {
            Log::error('Dyte add participant error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function createDyteMeeting(string $title, Hook $hook): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Basic " . base64_encode("{$hook->settings['organization_id']}:{$hook->settings['api_key']}"),
            'Content-Type' => 'application/json',
        ])->post(self::API_BASE_URL . '/meetings', [
            'title' => $title,
            'preferred_region' => 'ap-south-1',
            'record_on_start' => false,
            'live_stream_on_start' => false,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Dyte API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        
        if (!$data['success']) {
            throw new \Exception('Dyte meeting creation failed: ' . json_encode($data));
        }

        return $data['data'];
    }

    private function addParticipant(string $meetingId, User $user, Hook $hook): array
    {
        $avatarUrl = $user->avatar_url ?: config('app.url') . '/integrations/dyte/user.png';

        $response = Http::withHeaders([
            'Authorization' => "Basic " . base64_encode("{$hook->settings['organization_id']}:{$hook->settings['api_key']}"),
            'Content-Type' => 'application/json',
        ])->post(self::API_BASE_URL . "/meetings/{$meetingId}/participants", [
            'name' => $user->name,
            'picture' => $avatarUrl,
            'preset_name' => 'default_preset',
            'custom_participant_id' => (string) $user->id,
        ]);

        if (!$response->successful()) {
            throw new \Exception("Dyte API error: {$response->status()} - {$response->body()}");
        }

        $data = $response->json();
        
        if (!$data['success']) {
            throw new \Exception('Dyte participant addition failed: ' . json_encode($data));
        }

        return $data['data'];
    }

    private function getDyteHook(): ?Hook
    {
        return $this->account->integrationHooks()
            ->where('app_id', 'dyte')
            ->where('status', Hook::STATUS_ENABLED)
            ->first();
    }
}