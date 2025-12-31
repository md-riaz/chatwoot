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
use App\Listeners\HandleMessageLifecycle;
use App\Listeners\HandleSlaBreached;
use App\Listeners\HandlePortalUpdated;
use App\Listeners\HandleArticleUpdated;

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
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
