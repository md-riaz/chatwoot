<?php

namespace App\Listeners;

use App\Events\Conversation\ConversationCreated;
use App\Actions\Conversation\ScheduleAutoResolveAction;
use App\Actions\Sla\DispatchSlaTimersAction;
use App\Jobs\Conversations\RunAutoAssignConversationJob;
use App\Jobs\Conversations\CreateActivityMessageJob;
use App\Jobs\Webhooks\SendWebhooksJob;
use Illuminate\Contracts\Logging\Log as LogContract;
use function Spatie\Activitylog\activity;

class HandleConversationCreated
{
    public function __construct(private LogContract $log) {}

    public function handle(ConversationCreated $event): void
    {
        $conversation = $event->conversation;

        // 1) Create an activity message announcing conversation creation
        CreateActivityMessageJob::dispatch($conversation, ['content' => 'Conversation created']);

        // 2) Run auto-assign asynchronously
        RunAutoAssignConversationJob::dispatch($conversation->id);

        // 3) Trigger SLA evaluation
        DispatchSlaTimersAction::run($conversation);

        // 4) Schedule auto-resolve parity with Rails worker
        ScheduleAutoResolveAction::run($conversation);

        // 5) Emit webhooks for 'conversation_created'
        SendWebhooksJob::dispatch($conversation->account_id, 'conversation_created', ['conversation_id' => $conversation->id]);

        // 6) Create notifications for inbox members (Rails parity)
        $inboxMembers = $conversation->inbox->members; 
        
        // Pre-fetch notification settings for efficiency
        $memberIds = $inboxMembers->pluck('id');
        $settings = \App\Models\NotificationSetting::whereIn('user_id', $memberIds)
            ->where('account_id', $conversation->account_id)
            ->get()
            ->keyBy('user_id');

        foreach ($inboxMembers as $agent) {
            $setting = $settings->get($agent->id);

            // Rails logic: return if notification_setting.blank?
            if (!$setting) {
                continue;
            }

            // Rails logic: return if notification_type == 'conversation_creation' && !user_subscribed_to_notification?
            if (!$setting->isSubscribed('conversation_creation')) {
                continue;
            }
            
            \App\Models\Notification::create([
                'user_id' => $agent->id,
                'account_id' => $conversation->account_id,
                'notification_type' => \App\Models\NotificationSetting::NOTIFICATION_TYPES['conversation_creation'],
                'primary_actor_type' => get_class($conversation),
                'primary_actor_id' => $conversation->id,
            ]);
        }

        activity()
            ->performedOn($conversation)
            ->withProperties(['event' => 'conversation_created'])
            ->event('conversation_created')
            ->log('Conversation created');

        $this->log->info('HandleConversationCreated dispatched side-effects', ['conversation_id' => $conversation->id]);
    }
}
