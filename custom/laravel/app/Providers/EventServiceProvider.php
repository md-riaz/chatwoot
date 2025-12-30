<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Message\MessageCreated;
use App\Events\Message\MessageUpdated;
use App\Events\Conversation\ConversationCreated;
use App\Events\Conversation\ConversationAssigned;
use App\Events\Conversation\ConversationStatusChanged;
use App\Events\Contact\ContactCreated;
use App\Events\Contact\ContactUpdated;
use App\Listeners\EnqueueOpenAiEnrichment;
use App\Listeners\HandleConversationCreated;
use App\Listeners\HandleConversationAssigned;
use App\Listeners\HandleConversationStatusChanged;
use App\Listeners\HandleContactCreated;
use App\Listeners\HandleContactUpdated;

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
        ],
        // Also process updates to messages (reuse enrichment listener)
        MessageUpdated::class => [
            EnqueueOpenAiEnrichment::class,
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

        ContactCreated::class => [
            HandleContactCreated::class,
        ],

        ContactUpdated::class => [
            HandleContactUpdated::class,
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
