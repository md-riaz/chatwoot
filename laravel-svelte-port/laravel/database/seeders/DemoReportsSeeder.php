<?php

namespace Database\Seeders;

use App\Enums\AccountUserRole;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Conversation;
use App\Models\CsatSurveyResponse;
use App\Models\Message;
use App\Models\ReportingEvent;
use App\Models\User;
use App\Services\Seeding\AccountSeederService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoReportsSeeder extends Seeder
{
    /**
     * Seed deterministic data for login and reports verification.
     */
    public function run(): void
    {
        $demoAccount = Account::query()->firstOrCreate(
            ['name' => 'Demo Reports Account'],
            [
                'locale' => 'en',
                'domain' => 'demo-reports.local',
                'support_email' => 'support@demo-reports.local',
                'settings' => [],
                'feature_flags' => 32128855,
                'limits' => ['agents' => 100, 'inboxes' => 50],
            ]
        );

        // Enable standard & enterprise features for local demo environment parity
        $demoAccount->enableFeature('custom_attributes');
        $demoAccount->enableFeature('automations');
        $demoAccount->enableFeature('audit_logs');
        $demoAccount->enableFeature('sla');
        $demoAccount->enableFeature('custom_roles');
        $demoAccount->enableFeature('saml');

        (new AccountSeederService($demoAccount))->perform();

        $demoUser = User::query()->updateOrCreate(
            ['email' => 'mdriaz@alpha.net.bd'],
            [
                'name' => 'Demo Reports Admin',
                'password' => Hash::make('12345678'),
                'type' => 'SuperAdmin',
                'email_verified_at' => now(),
            ]
        );

        AccountUser::query()->updateOrCreate(
            [
                'account_id' => $demoAccount->id,
                'user_id' => $demoUser->id,
            ],
            [
                'role' => AccountUserRole::ADMINISTRATOR,
            ]
        );
        $this->seedReportingEvents($demoAccount->id);
        $this->seedCsatResponses($demoAccount->id);
    }

    /**
     * Create report-friendly analytics events used by summary and heatmap APIs.
     */
    private function seedReportingEvents(int $accountId): void
    {
        ReportingEvent::query()->where('account_id', $accountId)->delete();

        $conversations = Conversation::query()
            ->where('account_id', $accountId)
            ->with('messages:id,conversation_id,message_type,created_at')
            ->get();

        foreach ($conversations as $index => $conversation) {
            $openedAt = now()->subDays(14 - ($index % 10))->startOfDay()->addHours(9 + ($index % 6));
            $assignedUserId = $conversation->assignee_id;

            $this->createEvent(
                accountId: $accountId,
                conversationId: $conversation->id,
                inboxId: $conversation->inbox_id,
                userId: $assignedUserId,
                name: 'conversation_opened',
                value: 1,
                eventTime: $openedAt
            );

            $messageTypes = $conversation->messages->pluck('message_type')->all();
            $incomingCount = count(array_filter($messageTypes, fn (int $type) => $type === Message::TYPE_INCOMING));
            $outgoingCount = count(array_filter($messageTypes, fn (int $type) => $type === Message::TYPE_OUTGOING));

            for ($i = 0; $i < max(1, $incomingCount); $i++) {
                $this->createEvent(
                    accountId: $accountId,
                    conversationId: $conversation->id,
                    inboxId: $conversation->inbox_id,
                    userId: $assignedUserId,
                    name: 'message_created',
                    value: Message::TYPE_INCOMING,
                    eventTime: $openedAt->copy()->addMinutes(5 + $i)
                );
            }

            for ($i = 0; $i < max(1, $outgoingCount); $i++) {
                $this->createEvent(
                    accountId: $accountId,
                    conversationId: $conversation->id,
                    inboxId: $conversation->inbox_id,
                    userId: $assignedUserId,
                    name: 'message_created',
                    value: Message::TYPE_OUTGOING,
                    eventTime: $openedAt->copy()->addMinutes(12 + $i)
                );
            }

            $firstResponseSeconds = 180 + (($index % 5) * 45);
            $resolutionSeconds = 1200 + (($index % 7) * 240);
            $replySeconds = 140 + (($index % 4) * 30);

            $this->createEvent(
                accountId: $accountId,
                conversationId: $conversation->id,
                inboxId: $conversation->inbox_id,
                userId: $assignedUserId,
                name: 'first_response',
                value: $firstResponseSeconds,
                eventTime: $openedAt->copy()->addMinutes(8)
            );

            $this->createEvent(
                accountId: $accountId,
                conversationId: $conversation->id,
                inboxId: $conversation->inbox_id,
                userId: $assignedUserId,
                name: 'reply_time',
                value: $replySeconds,
                eventTime: $openedAt->copy()->addMinutes(10)
            );

            $this->createEvent(
                accountId: $accountId,
                conversationId: $conversation->id,
                inboxId: $conversation->inbox_id,
                userId: $assignedUserId,
                name: 'conversation_resolved',
                value: $resolutionSeconds,
                eventTime: $openedAt->copy()->addMinutes(40)
            );
        }
    }

    private function createEvent(
        int $accountId,
        int $conversationId,
        int $inboxId,
        ?int $userId,
        string $name,
        float|int $value,
        \Illuminate\Support\Carbon $eventTime
    ): void {
        ReportingEvent::query()->create([
            'account_id' => $accountId,
            'conversation_id' => $conversationId,
            'inbox_id' => $inboxId,
            'user_id' => $userId,
            'name' => $name,
            'value' => $value,
            'value_in_business_hours' => $value,
            'event_start_time' => $eventTime,
            'event_end_time' => $eventTime,
            'created_at' => $eventTime,
            'updated_at' => $eventTime,
        ]);
    }

    /**
     * Seed CSAT rows so the CSAT report has realistic prefilled data.
     */
    private function seedCsatResponses(int $accountId): void
    {
        CsatSurveyResponse::query()->where('account_id', $accountId)->delete();

        $conversations = Conversation::query()
            ->where('account_id', $accountId)
            ->with(['messages:id,conversation_id,created_at', 'contact:id'])
            ->take(8)
            ->get();

        foreach ($conversations as $index => $conversation) {
            $lastMessage = $conversation->messages->sortByDesc('created_at')->first();
            if (! $lastMessage) {
                continue;
            }

            CsatSurveyResponse::query()->create([
                'account_id' => $accountId,
                'conversation_id' => $conversation->id,
                'message_id' => $lastMessage->id,
                'contact_id' => $conversation->contact_id,
                'assigned_agent_id' => $conversation->assignee_id,
                'rating' => ($index % 5) + 1,
                'feedback_message' => 'Seeded CSAT feedback for migration parity checks.',
                'created_at' => now()->subDays(8 - $index),
                'updated_at' => now()->subDays(8 - $index),
            ]);
        }
    }
}
