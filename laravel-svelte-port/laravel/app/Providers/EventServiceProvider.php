<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Message\MessageCreated;
use App\Events\Message\MessageUpdated;
use App\Events\Message\MessageDeleted;
use App\Events\Conversation\ConversationCreated;
use App\Events\Conversation\ConversationAssigned;
use App\Events\Conversation\ConversationStatusChanged;
use App\Events\Conversation\ConversationUpdated;
use App\Events\Contact\ContactCreated;
use App\Events\Contact\ContactUpdated;
use App\Events\Sla\SlaBreached;
use App\Events\Portal\PortalUpdated;
use App\Events\Article\ArticleUpdated;
use App\Listeners\EnqueueOpenAiEnrichment;
use App\Listeners\HandleConversationCreated;
use App\Listeners\HandleConversationAssigned;
use App\Listeners\HandleConversationStatusChanged;
use App\Listeners\HandleConversationUpdated;
use App\Listeners\HandleContactCreated;
use App\Listeners\HandleContactUpdated;
use App\Listeners\HandleMessageCreated;
use App\Listeners\HandleMessageLifecycle;
use App\Listeners\HandleSlaBreached;
use App\Listeners\HandlePortalUpdated;
use App\Listeners\HandleArticleUpdated;
use App\Listeners\WebSocketEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageCreated::class => [
            EnqueueOpenAiEnrichment::class,
            HandleMessageLifecycle::class,
            HandleMessageCreated::class,
            WebSocketEventListener::class . '@handleMessageCreated', // Check for first reply
        ],
        
        // Also process updates to messages (reuse enrichment listener)
        MessageUpdated::class => [
            EnqueueOpenAiEnrichment::class,
            HandleMessageLifecycle::class,
        ],
        
        MessageDeleted::class => [
            HandleMessageLifecycle::class,
        ],

        ConversationCreated::class => [
            HandleConversationCreated::class,
        ],

        ConversationAssigned::class => [
            HandleConversationAssigned::class,
        ],

        ConversationStatusChanged::class => [
            HandleConversationStatusChanged::class,
        ],

        ConversationUpdated::class => [
            HandleConversationUpdated::class,
        ],

        ContactCreated::class => [
            HandleContactCreated::class,
        ],

        ContactUpdated::class => [
            HandleContactUpdated::class,
        ],

        SlaBreached::class => [
            HandleSlaBreached::class,
        ],

        PortalUpdated::class => [
            HandlePortalUpdated::class,
        ],

        ArticleUpdated::class => [
            HandleArticleUpdated::class,
        ],

        \App\Events\Notification\NotificationCreated::class => [
            \App\Listeners\HandleNotificationCreated::class,
            \App\Listeners\HandlePushNotification::class,
        ],

        // WebSocket Events - New events for real-time functionality
        \App\Events\Notification\NotificationUpdated::class => [
            WebSocketEventListener::class . '@handleNotificationUpdated',
        ],

        \App\Events\Notification\NotificationDeleted::class => [
            WebSocketEventListener::class . '@handleNotificationDeleted',
        ],

        \App\Events\Conversation\ConversationRead::class => [
            WebSocketEventListener::class . '@handleConversationRead',
        ],

        \App\Events\Conversation\ConversationTyping::class => [
            // This will be triggered directly, no listener needed
        ],

        \App\Events\Conversation\AssigneeChanged::class => [
            WebSocketEventListener::class . '@handleAssigneeChanged',
        ],

        \App\Events\Conversation\TeamChanged::class => [
            WebSocketEventListener::class . '@handleTeamChanged',
        ],

        \App\Events\Conversation\ConversationContactChanged::class => [
            WebSocketEventListener::class . '@handleConversationContactChanged',
        ],

        \App\Events\Conversation\ConversationMentioned::class => [
            WebSocketEventListener::class . '@handleConversationMentioned',
        ],

        \App\Events\Contact\ContactMerged::class => [
            WebSocketEventListener::class . '@handleContactMerged',
        ],

        \App\Events\Contact\ContactDeleted::class => [
            WebSocketEventListener::class . '@handleContactDeleted',
        ],

        \App\Events\Account\AccountCacheInvalidated::class => [
            WebSocketEventListener::class . '@handleAccountCacheInvalidated',
        ],

        \App\Events\Presence\PresenceUpdate::class => [
            // This will be triggered directly, no listener needed
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Register model observers
        \App\Models\Contact::observe(\App\Observers\ContactObserver::class);
    }
}
