# Laravel Reverb WebSocket Migration Plan

## Overview

This document outlines the complete migration plan to achieve full parity between Rails ActionCable and Laravel Reverb, ensuring no functionality is lost while following Laravel conventions.

## Current State Analysis

### ✅ Implemented Events (Partial Parity)
- `notification.created` - Laravel: `NotificationCreatedBroadcast`
- `message.created` - Laravel: `MessageCreated`
- `message.updated` - Laravel: `MessageUpdated`
- `conversation.created` - Laravel: `ConversationCreated`
- `conversation.status_changed` - Laravel: `ConversationStatusChanged`
- `conversation.updated` - Laravel: `ConversationUpdated`
- `contact.created` - Laravel: `ContactCreated`
- `contact.updated` - Laravel: `ContactUpdated`

### ❌ Missing Events (Need Implementation)
- `notification.updated`
- `notification.deleted`
- `account.cache_invalidated`
- `first.reply.created`
- `conversation.read`
- `conversation.typing_on`
- `conversation.typing_off`
- `assignee.changed` (Laravel has `conversation.assigned` - needs alignment)
- `team.changed`
- `conversation.contact_changed`
- `contact.merged`
- `contact.deleted`
- `conversation.mentioned`
- `presence.update`

## Migration Strategy

### Phase 1: Channel Architecture Alignment

#### 1.1 Laravel-Native Channel Strategy
**Goal**: Use Laravel's secure channel authorization system for all functionality

```php
// routes/channels.php - Laravel-native channel definitions
Broadcast::channel('account.{accountId}', function ($user, $accountId) {
    return $user->accounts()->where('id', $accountId)->exists();
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    return $conversation && $user->accounts()->where('id', $conversation->account_id)->exists();
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Contact-based channels for widget support
Broadcast::channel('contact.{contactId}', function ($user, $contactId) {
    // For authenticated contacts (widget users)
    if ($user instanceof Contact) {
        return (int) $user->id === (int) $contactId;
    }
    // For agents viewing contact conversations
    $contact = Contact::find($contactId);
    return $contact && $user->accounts()->where('id', $contact->account_id)->exists();
});

// Presence channels for real-time status
Broadcast::channel('account.{accountId}.presence', function ($user, $accountId) {
    if ($user->accounts()->where('id', $accountId)->exists()) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->getAvatarUrl(),
            'availability' => $user->availability,
            'type' => 'agent'
        ];
    }
});

Broadcast::channel('contact.{contactId}.presence', function ($user, $contactId) {
    if ($user instanceof Contact && (int) $user->id === (int) $contactId) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'type' => 'contact'
        ];
    }
});

// Inbox-specific channels for widget conversations
Broadcast::channel('inbox.{inboxId}', function ($user, $inboxId) {
    $inbox = Inbox::find($inboxId);
    return $inbox && $user->accounts()->where('id', $inbox->account_id)->exists();
});
```

#### 1.2 Multi-User Authentication Support
**Goal**: Support both agent and contact authentication in Laravel-native way

```php
// app/Http/Middleware/WebSocketAuth.php
class WebSocketAuth
{
    public function handle($request, Closure $next)
    {
        // Support agent authentication via Sanctum
        if ($token = $request->bearerToken()) {
            $user = PersonalAccessToken::findToken($token)?->tokenable;
            if ($user instanceof User) {
                Auth::setUser($user);
                return $next($request);
            }
        }

        // Support contact authentication via session/custom token
        if ($contactToken = $request->header('X-Contact-Token')) {
            $contact = Contact::where('pubsub_token', $contactToken)->first();
            if ($contact) {
                Auth::setUser($contact);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}

// config/broadcasting.php - Enhanced Pusher config for multi-auth
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => true,
        'auth_endpoint' => '/broadcasting/auth', // Custom auth endpoint
    ],
],
```

#### 1.3 Channel Broadcasting Strategy
**Goal**: Ensure all Rails functionality is covered with Laravel channels

```php
// app/Services/WebSocket/BroadcastTargetService.php
class BroadcastTargetService
{
    public function getAccountChannels(int $accountId): array
    {
        return [
            "private-account.{$accountId}",
            "presence-account.{$accountId}.presence"
        ];
    }

    public function getUserChannels(int $userId): array
    {
        return ["private-user.{$userId}"];
    }

    public function getConversationChannels(Conversation $conversation): array
    {
        $channels = [
            "private-account.{$conversation->account_id}",
            "private-conversation.{$conversation->id}"
        ];

        // Add contact channel if conversation has contact
        if ($conversation->contact_id) {
            $channels[] = "private-contact.{$conversation->contact_id}";
        }

        // Add inbox channel for widget conversations
        if ($conversation->inbox_id) {
            $channels[] = "private-inbox.{$conversation->inbox_id}";
        }

        return $channels;
    }

    public function getContactChannels(Contact $contact): array
    {
        $channels = [
            "private-account.{$contact->account_id}",
            "private-contact.{$contact->id}"
        ];

        // Add inbox channels for contact's conversations
        $inboxIds = $contact->conversations()->distinct('inbox_id')->pluck('inbox_id');
        foreach ($inboxIds as $inboxId) {
            $channels[] = "private-inbox.{$inboxId}";
        }

        return $channels;
    }
}
```

### Phase 2: Missing Event Implementation

#### 2.1 Notification Events

```php
// app/Events/Notification/NotificationUpdated.php
class NotificationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Notification $notification,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->notification->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification->toArray(),
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Notification/NotificationDeleted.php
class NotificationDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $notificationId,
        public int $userId,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->userId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notificationId,
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

#### 2.2 Conversation Events

```php
// app/Events/Conversation/ConversationRead.php
class ConversationRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $reader
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->only(['id', 'unread_count']),
            'reader' => $this->reader->only(['id', 'name']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Conversation/ConversationTyping.php
class ConversationTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User|Contact $typer,
        public bool $isTyping = true
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];

        // Include contact channel if typer is contact
        if ($this->typer instanceof Contact) {
            $channels[] = new PrivateChannel("contact.{$this->typer->id}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return $this->isTyping ? 'conversation.typing_on' : 'conversation.typing_off';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'typer' => [
                'id' => $this->typer->id,
                'name' => $this->typer->name,
                'type' => $this->typer instanceof Contact ? 'contact' : 'agent',
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    public function broadcastToOthers(): bool
    {
        return true; // Exclude the typer from receiving their own typing events
    }
}

// app/Events/Conversation/AssigneeChanged.php (Rename from ConversationAssigned)
class AssigneeChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ?User $previousAssignee,
        public ?User $newAssignee,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'assignee.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_assignee' => $this->previousAssignee?->only(['id', 'name', 'avatar_url']),
            'new_assignee' => $this->newAssignee?->only(['id', 'name', 'avatar_url']),
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Conversation/TeamChanged.php
class TeamChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public ?Team $previousTeam,
        public ?Team $newTeam,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'team.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_team' => $this->previousTeam?->only(['id', 'name']),
            'new_team' => $this->newTeam?->only(['id', 'name']),
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Conversation/ConversationContactChanged.php
class ConversationContactChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public Contact $previousContact,
        public Contact $newContact,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
            new PrivateChannel("conversation.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.contact_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'previous_contact' => $this->previousContact->toArray(),
            'new_contact' => $this->newContact->toArray(),
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Conversation/ConversationMentioned.php
class ConversationMentioned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public User $mentionedUser,
        public Message $message,
        public User $mentioner
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("user.{$this->mentionedUser->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.mentioned';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'message' => $this->message->toArray(),
            'mentioned_user' => $this->mentionedUser->only(['id', 'name', 'avatar_url']),
            'mentioner' => $this->mentioner->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Conversation/FirstReplyCreated.php
class FirstReplyCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Conversation $conversation,
        public Message $message,
        public User $agent
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->conversation->account_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'first.reply.created';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
            'message' => $this->message->toArray(),
            'agent' => $this->agent->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

#### 2.3 Contact Events

```php
// app/Events/Contact/ContactMerged.php
class ContactMerged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Contact $primaryContact,
        public Contact $mergedContact,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->primaryContact->account_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contact.merged';
    }

    public function broadcastWith(): array
    {
        return [
            'primary_contact' => $this->primaryContact->toArray(),
            'merged_contact' => $this->mergedContact->toArray(),
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Contact/ContactDeleted.php
class ContactDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $contactId,
        public int $accountId,
        public array $contactData,
        public ?User $performer = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->accountId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'contact.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->contactId,
            'contact' => $this->contactData,
            'performer' => $this->performer?->only(['id', 'name', 'avatar_url']),
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

#### 2.4 System Events

```php
// app/Events/Account/AccountCacheInvalidated.php
class AccountCacheInvalidated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Account $account,
        public array $invalidatedKeys = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("account.{$this->account->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'account.cache_invalidated';
    }

    public function broadcastWith(): array
    {
        return [
            'account_id' => $this->account->id,
            'invalidated_keys' => $this->invalidatedKeys,
            'timestamp' => now()->toISOString(),
        ];
    }
}

// app/Events/Presence/PresenceUpdate.php
class PresenceUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User|Contact $user,
        public int $accountId,
        public string $status, // 'online', 'offline', 'away'
        public ?array $metadata = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel("account.{$this->accountId}.presence"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'presence.update';
    }

    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user instanceof User 
                    ? $this->user->getAvatarUrl() 
                    : $this->user->avatar_url,
                'type' => $this->user instanceof Contact ? 'contact' : 'agent',
            ],
            'status' => $this->status,
            'metadata' => $this->metadata,
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

### Phase 3: Event Listener Integration

#### 3.1 Unified Event Listener

```php
// app/Listeners/WebSocketEventListener.php
class WebSocketEventListener
{
    public function handleNotificationCreated(NotificationCreated $event): void
    {
        broadcast(new NotificationCreatedBroadcast($event->notification));
    }

    public function handleNotificationUpdated(NotificationUpdated $event): void
    {
        broadcast(new NotificationUpdated($event->notification, $event->performer));
    }

    public function handleNotificationDeleted(NotificationDeleted $event): void
    {
        broadcast(new NotificationDeleted(
            $event->notificationId,
            $event->userId,
            $event->performer
        ));
    }

    public function handleMessageCreated(MessageCreated $event): void
    {
        broadcast(new MessageCreated($event->message));
        
        // Check if this is the first reply
        if ($this->isFirstReply($event->message)) {
            broadcast(new FirstReplyCreated(
                $event->message->conversation,
                $event->message,
                $event->message->user
            ));
        }
    }

    public function handleConversationRead(ConversationRead $event): void
    {
        broadcast(new ConversationRead($event->conversation, $event->reader));
    }

    public function handleTypingStarted(TypingStarted $event): void
    {
        broadcast(new ConversationTyping(
            $event->conversation,
            $event->typer,
            true
        ));
    }

    public function handleTypingStopped(TypingStopped $event): void
    {
        broadcast(new ConversationTyping(
            $event->conversation,
            $event->typer,
            false
        ));
    }

    public function handleAssigneeChanged(AssigneeChanged $event): void
    {
        broadcast(new AssigneeChanged(
            $event->conversation,
            $event->previousAssignee,
            $event->newAssignee,
            $event->performer
        ));
    }

    public function handleTeamChanged(TeamChanged $event): void
    {
        broadcast(new TeamChanged(
            $event->conversation,
            $event->previousTeam,
            $event->newTeam,
            $event->performer
        ));
    }

    public function handleContactMerged(ContactMerged $event): void
    {
        broadcast(new ContactMerged(
            $event->primaryContact,
            $event->mergedContact,
            $event->performer
        ));
    }

    public function handleContactDeleted(ContactDeleted $event): void
    {
        broadcast(new ContactDeleted(
            $event->contactId,
            $event->accountId,
            $event->contactData,
            $event->performer
        ));
    }

    public function handleUserPresenceChanged(UserPresenceChanged $event): void
    {
        // Broadcast to all accounts the user belongs to
        foreach ($event->user->accounts as $account) {
            broadcast(new PresenceUpdate(
                $event->user,
                $account->id,
                $event->status,
                $event->metadata
            ));
        }
    }

    public function handleContactPresenceChanged(ContactPresenceChanged $event): void
    {
        broadcast(new PresenceUpdate(
            $event->contact,
            $event->contact->account_id,
            $event->status,
            $event->metadata
        ));
    }

    private function isFirstReply(Message $message): bool
    {
        return $message->message_type === 'outgoing' &&
               $message->conversation->messages()
                   ->where('message_type', 'outgoing')
                   ->count() === 1;
    }
}
```

#### 3.2 Event Service Provider Registration

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    // Notification Events
    NotificationCreated::class => [
        WebSocketEventListener::class . '@handleNotificationCreated',
    ],
    NotificationUpdated::class => [
        WebSocketEventListener::class . '@handleNotificationUpdated',
    ],
    NotificationDeleted::class => [
        WebSocketEventListener::class . '@handleNotificationDeleted',
    ],

    // Message Events
    MessageCreated::class => [
        WebSocketEventListener::class . '@handleMessageCreated',
    ],
    MessageUpdated::class => [
        WebSocketEventListener::class . '@handleMessageUpdated',
    ],
    MessageDeleted::class => [
        WebSocketEventListener::class . '@handleMessageDeleted',
    ],

    // Conversation Events
    ConversationCreated::class => [
        WebSocketEventListener::class . '@handleConversationCreated',
    ],
    ConversationUpdated::class => [
        WebSocketEventListener::class . '@handleConversationUpdated',
    ],
    ConversationRead::class => [
        WebSocketEventListener::class . '@handleConversationRead',
    ],
    TypingStarted::class => [
        WebSocketEventListener::class . '@handleTypingStarted',
    ],
    TypingStopped::class => [
        WebSocketEventListener::class . '@handleTypingStopped',
    ],
    AssigneeChanged::class => [
        WebSocketEventListener::class . '@handleAssigneeChanged',
    ],
    TeamChanged::class => [
        WebSocketEventListener::class . '@handleTeamChanged',
    ],
    ConversationContactChanged::class => [
        WebSocketEventListener::class . '@handleConversationContactChanged',
    ],
    ConversationMentioned::class => [
        WebSocketEventListener::class . '@handleConversationMentioned',
    ],

    // Contact Events
    ContactCreated::class => [
        WebSocketEventListener::class . '@handleContactCreated',
    ],
    ContactUpdated::class => [
        WebSocketEventListener::class . '@handleContactUpdated',
    ],
    ContactMerged::class => [
        WebSocketEventListener::class . '@handleContactMerged',
    ],
    ContactDeleted::class => [
        WebSocketEventListener::class . '@handleContactDeleted',
    ],

    // Presence Events
    UserPresenceChanged::class => [
        WebSocketEventListener::class . '@handleUserPresenceChanged',
    ],
    ContactPresenceChanged::class => [
        WebSocketEventListener::class . '@handleContactPresenceChanged',
    ],

    // System Events
    AccountCacheInvalidated::class => [
        WebSocketEventListener::class . '@handleAccountCacheInvalidated',
    ],
];
```

### Phase 4: Frontend Integration (SvelteKit with Pusher.js)

#### 4.1 Enhanced Reverb Client with Event Management

```typescript
// src/lib/websocket/reverb-client.ts - Enhanced version
import type { Channel, PresenceChannel } from 'pusher-js';
import Pusher from 'pusher-js';
import { getWebSocketStore } from './store.svelte';

export interface ReverbConfig {
  host: string;
  port: number;
  key: string;
  cluster?: string;
  forceTLS?: boolean;
  authEndpoint?: string;
  auth?: {
    headers: {
      Authorization: string;
    };
  };
}

export interface EventSubscription {
  channel: Channel | PresenceChannel;
  eventName: string;
  callback: (data: any) => void;
  unsubscribe: () => void;
}

export class ReverbClient {
  private pusher: Pusher | null = null;
  private subscriptions = new Map<string, EventSubscription>();
  private config: ReverbConfig;
  private wsStore = getWebSocketStore();

  constructor(config: ReverbConfig) {
    this.config = config;
  }

  /**
   * Connect to Laravel Reverb with enhanced error handling
   */
  connect(): void {
    if (this.pusher) {
      console.warn('Reverb client already connected');
      return;
    }

    this.wsStore.setState('connecting');

    // Determine connection type
    const isProxied = !this.config.port || this.config.port === 80 || this.config.port === 443;
    
    const pusherConfig: any = {
      wsHost: this.config.host,
      forceTLS: this.config.forceTLS || false,
      enabledTransports: ['ws', 'wss'],
      cluster: this.config.cluster || '',
      authEndpoint: this.config.authEndpoint,
      auth: this.config.auth,
      // Enable Pusher debugging in development
      enableLogging: import.meta.env.DEV,
    };

    if (isProxied) {
      // Proxied connection (production)
      pusherConfig.wsPort = this.config.forceTLS ? 443 : 80;
      pusherConfig.wssPort = 443;
      pusherConfig.wsPath = '/ws';
      pusherConfig.wssPath = '/ws';
    } else {
      // Direct connection (development)
      pusherConfig.wsPort = this.config.port;
      pusherConfig.wssPort = this.config.port;
    }

    this.pusher = new Pusher(this.config.key, pusherConfig);

    // Enhanced connection event handlers
    this.pusher.connection.bind('connected', () => {
      console.log('Reverb connected');
      this.wsStore.setState('connected');
    });

    this.pusher.connection.bind('disconnected', () => {
      console.log('Reverb disconnected');
      this.wsStore.setState('disconnected');
    });

    this.pusher.connection.bind('error', (error: any) => {
      console.error('Reverb connection error:', error);
      this.wsStore.setError(error.message || 'Connection error');
      this.wsStore.setState('failed');
    });

    this.pusher.connection.bind('unavailable', () => {
      console.warn('Reverb connection unavailable');
      this.wsStore.setState('failed');
    });

    // Handle reconnection attempts
    this.pusher.connection.bind('connecting', () => {
      this.wsStore.incrementReconnectAttempts();
      this.wsStore.setState('reconnecting');
    });
  }

  /**
   * Disconnect from Laravel Reverb
   */
  disconnect(): void {
    if (this.pusher) {
      // Unsubscribe from all channels
      this.subscriptions.forEach(({ unsubscribe }) => unsubscribe());
      this.subscriptions.clear();

      this.pusher.disconnect();
      this.pusher = null;
      this.wsStore.setState('disconnected');
    }
  }

  /**
   * Subscribe to account-level events
   */
  subscribeToAccount(accountId: number, eventHandlers: AccountEventHandlers): () => void {
    const unsubscribeFunctions: (() => void)[] = [];

    // Subscribe to account channel
    unsubscribeFunctions.push(
      this.subscribePrivate(`account.${accountId}`, 'message.created', eventHandlers.onMessageCreated),
      this.subscribePrivate(`account.${accountId}`, 'conversation.created', eventHandlers.onConversationCreated),
      this.subscribePrivate(`account.${accountId}`, 'conversation.updated', eventHandlers.onConversationUpdated),
      this.subscribePrivate(`account.${accountId}`, 'conversation.status_changed', eventHandlers.onConversationStatusChanged),
      this.subscribePrivate(`account.${accountId}`, 'assignee.changed', eventHandlers.onAssigneeChanged),
      this.subscribePrivate(`account.${accountId}`, 'team.changed', eventHandlers.onTeamChanged),
      this.subscribePrivate(`account.${accountId}`, 'contact.created', eventHandlers.onContactCreated),
      this.subscribePrivate(`account.${accountId}`, 'contact.updated', eventHandlers.onContactUpdated),
      this.subscribePrivate(`account.${accountId}`, 'contact.merged', eventHandlers.onContactMerged),
      this.subscribePrivate(`account.${accountId}`, 'contact.deleted', eventHandlers.onContactDeleted),
      this.subscribePrivate(`account.${accountId}`, 'first.reply.created', eventHandlers.onFirstReplyCreated),
      this.subscribePrivate(`account.${accountId}`, 'account.cache_invalidated', eventHandlers.onCacheInvalidated)
    );

    // Subscribe to presence channel
    unsubscribeFunctions.push(
      this.subscribePresence(`account.${accountId}.presence`, {
        onMessage: (eventName: string, data: any) => {
          if (eventName === 'presence.update' && eventHandlers.onPresenceUpdate) {
            eventHandlers.onPresenceUpdate(data);
          }
        },
        onMemberAdded: eventHandlers.onMemberAdded,
        onMemberRemoved: eventHandlers.onMemberRemoved,
      })
    );

    // Return combined unsubscribe function
    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Subscribe to user-level events
   */
  subscribeToUser(userId: number, eventHandlers: UserEventHandlers): () => void {
    const unsubscribeFunctions: (() => void)[] = [];

    unsubscribeFunctions.push(
      this.subscribePrivate(`user.${userId}`, 'notification.created', eventHandlers.onNotificationCreated),
      this.subscribePrivate(`user.${userId}`, 'notification.updated', eventHandlers.onNotificationUpdated),
      this.subscribePrivate(`user.${userId}`, 'notification.deleted', eventHandlers.onNotificationDeleted),
      this.subscribePrivate(`user.${userId}`, 'conversation.mentioned', eventHandlers.onConversationMentioned)
    );

    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Subscribe to conversation-level events
   */
  subscribeToConversation(conversationId: number, eventHandlers: ConversationEventHandlers): () => void {
    const unsubscribeFunctions: (() => void)[] = [];

    unsubscribeFunctions.push(
      this.subscribePrivate(`conversation.${conversationId}`, 'message.created', eventHandlers.onMessageCreated),
      this.subscribePrivate(`conversation.${conversationId}`, 'message.updated', eventHandlers.onMessageUpdated),
      this.subscribePrivate(`conversation.${conversationId}`, 'message.deleted', eventHandlers.onMessageDeleted),
      this.subscribePrivate(`conversation.${conversationId}`, 'conversation.read', eventHandlers.onConversationRead),
      this.subscribePrivate(`conversation.${conversationId}`, 'conversation.typing_on', eventHandlers.onTypingOn),
      this.subscribePrivate(`conversation.${conversationId}`, 'conversation.typing_off', eventHandlers.onTypingOff),
      this.subscribePrivate(`conversation.${conversationId}`, 'conversation.contact_changed', eventHandlers.onContactChanged)
    );

    return () => {
      unsubscribeFunctions.forEach(unsubscribe => unsubscribe());
    };
  }

  /**
   * Subscribe to a private channel (enhanced)
   */
  subscribePrivate(channelName: string, eventName: string, callback: (data: any) => void): () => void {
    if (!this.pusher) {
      throw new Error('Reverb client not connected');
    }

    const fullChannelName = `private-${channelName}`;
    const subscriptionKey = `${fullChannelName}:${eventName}`;

    // Check if already subscribed
    if (this.subscriptions.has(subscriptionKey)) {
      console.warn(`Already subscribed to ${subscriptionKey}`);
      return this.subscriptions.get(subscriptionKey)!.unsubscribe;
    }

    const channel = this.pusher.subscribe(fullChannelName);
    channel.bind(eventName, callback);

    const unsubscribe = () => {
      channel.unbind(eventName, callback);
      this.pusher?.unsubscribe(fullChannelName);
      this.subscriptions.delete(subscriptionKey);
    };

    this.subscriptions.set(subscriptionKey, {
      channel,
      eventName,
      callback,
      unsubscribe,
    });

    return unsubscribe;
  }

  /**
   * Subscribe to a presence channel (enhanced)
   */
  subscribePresence(
    channelName: string,
    callbacks: {
      onMessage?: (eventName: string, data: any) => void;
      onMemberAdded?: (member: any) => void;
      onMemberRemoved?: (member: any) => void;
    }
  ): () => void {
    if (!this.pusher) {
      throw new Error('Reverb client not connected');
    }

    const fullChannelName = `presence-${channelName}`;
    const subscriptionKey = fullChannelName;

    // Check if already subscribed
    if (this.subscriptions.has(subscriptionKey)) {
      console.warn(`Already subscribed to ${subscriptionKey}`);
      return this.subscriptions.get(subscriptionKey)!.unsubscribe;
    }

    const channel = this.pusher.subscribe(fullChannelName) as PresenceChannel;

    // Bind event handlers
    if (callbacks.onMessage) {
      channel.bind_global(callbacks.onMessage);
    }

    if (callbacks.onMemberAdded) {
      channel.bind('pusher:member_added', callbacks.onMemberAdded);
    }

    if (callbacks.onMemberRemoved) {
      channel.bind('pusher:member_removed', callbacks.onMemberRemoved);
    }

    const unsubscribe = () => {
      if (callbacks.onMessage) {
        channel.unbind_global(callbacks.onMessage);
      }
      if (callbacks.onMemberAdded) {
        channel.unbind('pusher:member_added', callbacks.onMemberAdded);
      }
      if (callbacks.onMemberRemoved) {
        channel.unbind('pusher:member_removed', callbacks.onMemberRemoved);
      }
      this.pusher?.unsubscribe(fullChannelName);
      this.subscriptions.delete(subscriptionKey);
    };

    this.subscriptions.set(subscriptionKey, {
      channel,
      eventName: 'presence',
      callback: () => {}, // Placeholder
      unsubscribe,
    });

    return unsubscribe;
  }

  /**
   * Get connection state
   */
  get connectionState() {
    return this.pusher?.connection.state || 'disconnected';
  }

  /**
   * Check if connected
   */
  get isConnected() {
    return this.pusher?.connection.state === 'connected';
  }

  /**
   * Get current subscriptions count
   */
  get subscriptionsCount() {
    return this.subscriptions.size;
  }

  /**
   * Get list of subscribed channels
   */
  get subscribedChannels() {
    return Array.from(this.subscriptions.keys());
  }
}

// Event handler interfaces
export interface AccountEventHandlers {
  onMessageCreated: (data: any) => void;
  onConversationCreated: (data: any) => void;
  onConversationUpdated: (data: any) => void;
  onConversationStatusChanged: (data: any) => void;
  onAssigneeChanged: (data: any) => void;
  onTeamChanged: (data: any) => void;
  onContactCreated: (data: any) => void;
  onContactUpdated: (data: any) => void;
  onContactMerged: (data: any) => void;
  onContactDeleted: (data: any) => void;
  onFirstReplyCreated: (data: any) => void;
  onCacheInvalidated: (data: any) => void;
  onPresenceUpdate?: (data: any) => void;
  onMemberAdded?: (member: any) => void;
  onMemberRemoved?: (member: any) => void;
}

export interface UserEventHandlers {
  onNotificationCreated: (data: any) => void;
  onNotificationUpdated: (data: any) => void;
  onNotificationDeleted: (data: any) => void;
  onConversationMentioned: (data: any) => void;
}

export interface ConversationEventHandlers {
  onMessageCreated: (data: any) => void;
  onMessageUpdated: (data: any) => void;
  onMessageDeleted: (data: any) => void;
  onConversationRead: (data: any) => void;
  onTypingOn: (data: any) => void;
  onTypingOff: (data: any) => void;
  onContactChanged: (data: any) => void;
}

// Singleton instance
let reverbClient: ReverbClient | null = null;

/**
 * Get or create Reverb client instance
 */
export function getReverbClient(config?: ReverbConfig): ReverbClient {
  if (!reverbClient && config) {
    reverbClient = new ReverbClient(config);
  }

  if (!reverbClient) {
    throw new Error('Reverb client not initialized. Provide config on first call.');
  }

  return reverbClient;
}

/**
 * Reset Reverb client (useful for testing)
 */
export function resetReverbClient(): void {
  if (reverbClient) {
    reverbClient.disconnect();
    reverbClient = null;
  }
}
```

#### 4.2 WebSocket Event Manager

```typescript
// src/lib/websocket/event-manager.ts
import { getReverbClient } from './reverb-client';
import { conversationsStore } from '$lib/stores/conversations.svelte';
import { notificationsStore } from '$lib/stores/notifications.svelte';
import { contactsStore } from '$lib/stores/contacts.svelte';
import { presenceStore } from '$lib/stores/presence.svelte';
import type { 
  AccountEventHandlers, 
  UserEventHandlers, 
  ConversationEventHandlers 
} from './reverb-client';

export class WebSocketEventManager {
  private accountUnsubscribe: (() => void) | null = null;
  private userUnsubscribe: (() => void) | null = null;
  private conversationUnsubscribes = new Map<number, () => void>();

  /**
   * Initialize WebSocket subscriptions for a user account
   */
  initializeForAccount(accountId: number, userId: number): void {
    const client = getReverbClient();

    // Subscribe to account-level events
    this.accountUnsubscribe = client.subscribeToAccount(accountId, this.getAccountEventHandlers());

    // Subscribe to user-level events
    this.userUnsubscribe = client.subscribeToUser(userId, this.getUserEventHandlers());
  }

  /**
   * Subscribe to conversation-specific events
   */
  subscribeToConversation(conversationId: number): void {
    if (this.conversationUnsubscribes.has(conversationId)) {
      return; // Already subscribed
    }

    const client = getReverbClient();
    const unsubscribe = client.subscribeToConversation(
      conversationId, 
      this.getConversationEventHandlers()
    );

    this.conversationUnsubscribes.set(conversationId, unsubscribe);
  }

  /**
   * Unsubscribe from conversation events
   */
  unsubscribeFromConversation(conversationId: number): void {
    const unsubscribe = this.conversationUnsubscribes.get(conversationId);
    if (unsubscribe) {
      unsubscribe();
      this.conversationUnsubscribes.delete(conversationId);
    }
  }

  /**
   * Clean up all subscriptions
   */
  cleanup(): void {
    // Unsubscribe from account and user events
    this.accountUnsubscribe?.();
    this.userUnsubscribe?.();

    // Unsubscribe from all conversation events
    this.conversationUnsubscribes.forEach(unsubscribe => unsubscribe());
    this.conversationUnsubscribes.clear();

    this.accountUnsubscribe = null;
    this.userUnsubscribe = null;
  }

  /**
   * Get account event handlers
   */
  private getAccountEventHandlers(): AccountEventHandlers {
    return {
      onMessageCreated: (data) => {
        console.log('Message created:', data);
        conversationsStore.handleMessageCreated(data.message);
        if (data.conversation) {
          conversationsStore.updateConversation(data.conversation);
        }
      },

      onConversationCreated: (data) => {
        console.log('Conversation created:', data);
        conversationsStore.addConversation(data.conversation);
      },

      onConversationUpdated: (data) => {
        console.log('Conversation updated:', data);
        conversationsStore.updateConversation(data.conversation);
      },

      onConversationStatusChanged: (data) => {
        console.log('Conversation status changed:', data);
        conversationsStore.updateConversation(data.conversation);
      },

      onAssigneeChanged: (data) => {
        console.log('Assignee changed:', data);
        conversationsStore.updateConversation(data.conversation);
      },

      onTeamChanged: (data) => {
        console.log('Team changed:', data);
        conversationsStore.updateConversation(data.conversation);
      },

      onContactCreated: (data) => {
        console.log('Contact created:', data);
        contactsStore.addContact(data.contact);
      },

      onContactUpdated: (data) => {
        console.log('Contact updated:', data);
        contactsStore.updateContact(data.contact);
      },

      onContactMerged: (data) => {
        console.log('Contact merged:', data);
        contactsStore.mergeContacts(data.primary_contact, data.merged_contact);
      },

      onContactDeleted: (data) => {
        console.log('Contact deleted:', data);
        contactsStore.removeContact(data.id);
      },

      onFirstReplyCreated: (data) => {
        console.log('First reply created:', data);
        conversationsStore.markFirstReply(data.conversation.id, data.message);
      },

      onCacheInvalidated: (data) => {
        console.log('Cache invalidated:', data);
        // Handle cache invalidation - refresh relevant data
        if (data.invalidated_keys?.includes('conversations')) {
          conversationsStore.refreshConversations();
        }
      },

      onPresenceUpdate: (data) => {
        console.log('Presence update:', data);
        presenceStore.updateUserPresence(data.user, data.status, data.metadata);
      },

      onMemberAdded: (member) => {
        console.log('Member added to presence:', member);
        presenceStore.addMember(member);
      },

      onMemberRemoved: (member) => {
        console.log('Member removed from presence:', member);
        presenceStore.removeMember(member);
      },
    };
  }

  /**
   * Get user event handlers
   */
  private getUserEventHandlers(): UserEventHandlers {
    return {
      onNotificationCreated: (data) => {
        console.log('Notification created:', data);
        notificationsStore.addNotification(data.notification);
      },

      onNotificationUpdated: (data) => {
        console.log('Notification updated:', data);
        notificationsStore.updateNotification(data.notification);
      },

      onNotificationDeleted: (data) => {
        console.log('Notification deleted:', data);
        notificationsStore.removeNotification(data.id);
      },

      onConversationMentioned: (data) => {
        console.log('Conversation mentioned:', data);
        notificationsStore.addMentionNotification(data.conversation, data.message);
      },
    };
  }

  /**
   * Get conversation event handlers
   */
  private getConversationEventHandlers(): ConversationEventHandlers {
    return {
      onMessageCreated: (data) => {
        console.log('Conversation message created:', data);
        conversationsStore.handleMessageCreated(data.message);
      },

      onMessageUpdated: (data) => {
        console.log('Message updated:', data);
        conversationsStore.updateMessage(data.message);
      },

      onMessageDeleted: (data) => {
        console.log('Message deleted:', data);
        conversationsStore.removeMessage(data.id);
      },

      onConversationRead: (data) => {
        console.log('Conversation read:', data);
        conversationsStore.markAsRead(data.conversation.id);
      },

      onTypingOn: (data) => {
        console.log('Typing started:', data);
        conversationsStore.setTyping(data.conversation_id, data.typer, true);
      },

      onTypingOff: (data) => {
        console.log('Typing stopped:', data);
        conversationsStore.setTyping(data.conversation_id, data.typer, false);
      },

      onContactChanged: (data) => {
        console.log('Conversation contact changed:', data);
        conversationsStore.updateConversation(data.conversation);
      },
    };
  }
}

// Singleton instance
let eventManager: WebSocketEventManager | null = null;

/**
 * Get WebSocket event manager instance
 */
export function getWebSocketEventManager(): WebSocketEventManager {
  if (!eventManager) {
    eventManager = new WebSocketEventManager();
  }
  return eventManager;
}

/**
 * Reset event manager (useful for testing)
 */
export function resetWebSocketEventManager(): void {
  if (eventManager) {
    eventManager.cleanup();
    eventManager = null;
  }
}
```

#### 4.3 Enhanced Presence Store

```typescript
// src/lib/stores/presence.svelte.ts
interface PresenceUser {
  id: number;
  name: string;
  avatar_url: string;
  type: 'agent' | 'contact';
  status: 'online' | 'offline' | 'away';
  metadata?: any;
  last_seen?: string;
}

interface TypingUser {
  id: number;
  name: string;
  type: 'agent' | 'contact';
}

interface PresenceState {
  users: Map<number, PresenceUser>;
  typingUsers: Map<number, Set<number>>; // conversationId -> Set of user IDs
  members: Map<string, any>; // Pusher presence members
}

function createPresenceStore() {
  let state = $state<PresenceState>({
    users: new Map(),
    typingUsers: new Map(),
    members: new Map()
  });

  return {
    // Reactive getters
    get users() {
      return Array.from(state.users.values());
    },

    get onlineUsers() {
      return Array.from(state.users.values()).filter(user => user.status === 'online');
    },

    getTypingUsers: (conversationId: number) => {
      const typingSet = state.typingUsers.get(conversationId);
      return typingSet ? Array.from(typingSet) : [];
    },

    getUserPresence: (userId: number) => {
      return state.users.get(userId);
    },

    isUserOnline: (userId: number) => {
      const user = state.users.get(userId);
      return user?.status === 'online';
    },

    // Actions
    updateUserPresence: (user: any, status: string, metadata?: any) => {
      state.users.set(user.id, {
        ...user,
        status: status as 'online' | 'offline' | 'away',
        metadata,
        last_seen: new Date().toISOString()
      });
    },

    setTyping: (conversationId: number, typer: TypingUser, isTyping: boolean) => {
      if (!state.typingUsers.has(conversationId)) {
        state.typingUsers.set(conversationId, new Set());
      }
      
      const typingSet = state.typingUsers.get(conversationId)!;
      if (isTyping) {
        typingSet.add(typer.id);
      } else {
        typingSet.delete(typer.id);
      }

      // Clean up empty sets
      if (typingSet.size === 0) {
        state.typingUsers.delete(conversationId);
      }
    },

    addMember: (member: any) => {
      state.members.set(member.id, member);
      // Also update user presence
      state.users.set(member.id, {
        ...member,
        status: 'online',
        last_seen: new Date().toISOString()
      });
    },

    removeMember: (member: any) => {
      state.members.delete(member.id);
      // Update user status to offline
      const user = state.users.get(member.id);
      if (user) {
        state.users.set(member.id, {
          ...user,
          status: 'offline',
          last_seen: new Date().toISOString()
        });
      }
    },

    reset: () => {
      state.users.clear();
      state.typingUsers.clear();
      state.members.clear();
    }
  };
}

export const presenceStore = createPresenceStore();
```

#### 4.4 Updated App Layout Integration

```typescript
// src/routes/app/+layout.svelte - Updated WebSocket initialization
<script lang="ts">
  import { authStore } from '$lib/stores/auth.svelte';
  import { getReverbClient } from '$lib/websocket/reverb-client';
  import { getWebSocketEventManager } from '$lib/websocket/event-manager';
  import { getWebSocketStore } from '$lib/websocket/store.svelte';
  import { onMount, onDestroy } from 'svelte';

  // WebSocket state
  let eventManager = getWebSocketEventManager();
  let wsStore = getWebSocketStore();
  
  // Initialize WebSocket on mount
  onMount(async () => {
    // Validate auth session first
    try {
      await authStore.validateSession();
      
      if (authStore.isLoggedIn && authStore.currentAccountId && authStore.currentUser?.id) {
        await initializeWebSocket();
      }
    } catch (error) {
      console.error('Session validation failed:', error);
    }
  });

  // Cleanup on destroy
  onDestroy(() => {
    eventManager.cleanup();
    const client = getReverbClient();
    client.disconnect();
  });

  async function initializeWebSocket() {
    const token = localStorage.getItem('auth_token');
    if (!token || !authStore.currentAccountId || !authStore.currentUser?.id) {
      return;
    }

    try {
      // WebSocket configuration
      const wsUrl = import.meta.env.VITE_WS_URL || 'ws://localhost:8080/ws';
      let wsHost = '127.0.0.1';
      let wsPort = 8080;
      let useTLS = false;
      let reverbKey = import.meta.env.VITE_REVERB_APP_KEY || 'clearline-app-key';
      
      // Parse WebSocket URL
      try {
        const url = new URL(wsUrl);
        wsHost = url.hostname;
        useTLS = url.protocol === 'wss:';
        
        if (url.pathname === '/' || url.pathname === '') {
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else if (url.pathname.startsWith('/ws')) {
          wsPort = url.port ? parseInt(url.port) : (url.protocol === 'wss:' ? 443 : 80);
        } else if (url.pathname.startsWith('/app/')) {
          const pathParts = url.pathname.split('/');
          if (pathParts.length >= 3) {
            reverbKey = pathParts[2];
          }
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else {
          wsPort = url.port ? parseInt(url.port) : 8080;
        }
      } catch (error) {
        console.error('Invalid WebSocket URL, using defaults:', error);
      }

      // Initialize Reverb client
      const client = getReverbClient({
        host: wsHost,
        port: wsPort,
        key: reverbKey,
        forceTLS: useTLS,
        authEndpoint: `${import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'}/api/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        },
      });

      // Connect to WebSocket
      client.connect();

      // Initialize event subscriptions
      eventManager.initializeForAccount(authStore.currentAccountId, authStore.currentUser.id);

      console.log('WebSocket initialized successfully');
    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
      wsStore.setError(error.message || 'WebSocket initialization failed');
    }
  }

  // Reactive: reinitialize WebSocket when account changes
  $effect(() => {
    if (authStore.currentAccountId && authStore.currentUser?.id) {
      initializeWebSocket();
    }
  });
</script>

<!-- Rest of the layout template -->
```

#### 4.5 WebSocket Testing

```typescript
// src/lib/websocket/reverb-client.test.ts
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { ReverbClient } from './reverb-client';

// Mock Pusher
const mockPusher = {
  connection: {
    bind: vi.fn(),
    state: 'connected'
  },
  subscribe: vi.fn(() => ({
    bind: vi.fn(),
    unbind: vi.fn(),
    bind_global: vi.fn(),
    unbind_global: vi.fn()
  })),
  unsubscribe: vi.fn(),
  disconnect: vi.fn()
};

vi.mock('pusher-js', () => ({
  default: vi.fn(() => mockPusher)
}));

describe('ReverbClient', () => {
  let client: ReverbClient;

  beforeEach(() => {
    vi.clearAllMocks();
    client = new ReverbClient({
      host: 'localhost',
      port: 8080,
      key: 'test-key'
    });
  });

  afterEach(() => {
    client.disconnect();
  });

  it('should connect successfully', () => {
    client.connect();
    expect(client.isConnected).toBe(true);
  });

  it('should subscribe to account events', () => {
    client.connect();
    
    const handlers = {
      onMessageCreated: vi.fn(),
      onConversationCreated: vi.fn(),
      onConversationUpdated: vi.fn(),
      onConversationStatusChanged: vi.fn(),
      onAssigneeChanged: vi.fn(),
      onTeamChanged: vi.fn(),
      onContactCreated: vi.fn(),
      onContactUpdated: vi.fn(),
      onContactMerged: vi.fn(),
      onContactDeleted: vi.fn(),
      onFirstReplyCreated: vi.fn(),
      onCacheInvalidated: vi.fn()
    };

    const unsubscribe = client.subscribeToAccount(1, handlers);
    
    expect(mockPusher.subscribe).toHaveBeenCalledWith('private-account.1');
    expect(mockPusher.subscribe).toHaveBeenCalledWith('presence-account.1.presence');
    
    // Test unsubscribe
    unsubscribe();
    expect(mockPusher.unsubscribe).toHaveBeenCalled();
  });

  it('should handle connection errors', () => {
    client.connect();
    
    const errorHandler = mockPusher.connection.bind.mock.calls.find(
      call => call[0] === 'error'
    )[1];
    
    errorHandler({ message: 'Test error' });
    
    // Should set error state
    expect(client.connectionState).toBe('connected'); // Mock always returns connected
  });
});
```

### Phase 5: Testing Strategy

#### 5.1 Backend Event Testing

```php
// tests/Feature/WebSocket/WebSocketEventsTest.php
class WebSocketEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_created_broadcasts_to_user_channel(): void
    {
        Event::fake([NotificationCreatedBroadcast::class]);
        
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);
        
        event(new NotificationCreated($notification));
        
        Event::assertDispatched(NotificationCreatedBroadcast::class, function ($event) use ($notification) {
            return $event->notification->id === $notification->id;
        });
    }

    public function test_message_created_broadcasts_to_multiple_channels(): void
    {
        Event::fake([MessageCreated::class]);
        
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);
        
        event(new MessageCreated($message));
        
        Event::assertDispatched(MessageCreated::class, function ($event) use ($message) {
            $channels = $event->broadcastOn();
            return count($channels) >= 2 && // account + conversation channels
                   $event->message->id === $message->id;
        });
    }

    public function test_typing_event_excludes_typer_from_broadcast(): void
    {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();
        
        $event = new ConversationTyping($conversation, $user, true);
        
        $this->assertTrue($event->broadcastToOthers());
    }

    public function test_presence_update_includes_correct_user_data(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create();
        $user->accounts()->attach($account);
        
        $event = new PresenceUpdate($user, $account->id, 'online');
        $data = $event->broadcastWith();
        
        $this->assertEquals($user->id, $data['user']['id']);
        $this->assertEquals('online', $data['status']);
        $this->assertEquals('agent', $data['user']['type']);
    }
}
```

#### 5.2 Frontend WebSocket Testing

```typescript
// src/lib/websocket/client.test.ts
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { webSocketClient } from './client';

// Mock WebSocket
class MockWebSocket {
    static CONNECTING = 0;
    static OPEN = 1;
    static CLOSING = 2;
    static CLOSED = 3;

    readyState = MockWebSocket.CONNECTING;
    onopen: ((event: Event) => void) | null = null;
    onclose: ((event: CloseEvent) => void) | null = null;
    onmessage: ((event: MessageEvent) => void) | null = null;
    onerror: ((event: Event) => void) | null = null;

    constructor(public url: string) {
        setTimeout(() => {
            this.readyState = MockWebSocket.OPEN;
            this.onopen?.(new Event('open'));
        }, 10);
    }

    send(data: string) {
        // Mock send implementation
    }

    close() {
        this.readyState = MockWebSocket.CLOSED;
        this.onclose?.(new CloseEvent('close'));
    }
}

global.WebSocket = MockWebSocket as any;

describe('WebSocketClient', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        webSocketClient.disconnect();
    });

    it('should connect successfully', async () => {
        const connectPromise = webSocketClient.connect('test-token');
        
        // Wait for connection
        await new Promise(resolve => setTimeout(resolve, 20));
        
        expect(webSocketClient.connected).toBeTruthy();
    });

    it('should handle subscription and unsubscription', () => {
        const callback = vi.fn();
        const unsubscribe = webSocketClient.subscribe('test-channel', callback);
        
        // Simulate message
        const mockMessage = {
            event: 'test.event',
            channel: 'test-channel',
            data: { test: 'data' }
        };
        
        // Trigger message handler
        webSocketClient['handleMessage'](mockMessage);
        
        expect(callback).toHaveBeenCalledWith(expect.objectContaining({
            event: 'test.event',
            channel: 'test-channel',
            data: { test: 'data' }
        }));
        
        // Test unsubscribe
        unsubscribe();
        webSocketClient['handleMessage'](mockMessage);
        
        // Should not be called again
        expect(callback).toHaveBeenCalledTimes(1);
    });
});
```

### Phase 6: Migration Checklist

#### 6.1 Backend Implementation Checklist

- [ ] **Channel Authorization**
  - [ ] Account-based channels (`account.{id}`)
  - [ ] User-based channels (`user.{id}`)
  - [ ] Conversation-based channels (`conversation.{id}`)
  - [ ] Contact-based channels (`contact.{id}`)
  - [ ] Presence channels (`account.{id}.presence`)

- [ ] **Missing Event Classes**
  - [ ] `NotificationUpdated`
  - [ ] `NotificationDeleted`
  - [ ] `ConversationRead`
  - [ ] `ConversationTyping` (on/off)
  - [ ] `AssigneeChanged` (rename from `ConversationAssigned`)
  - [ ] `TeamChanged`
  - [ ] `ConversationContactChanged`
  - [ ] `ConversationMentioned`
  - [ ] `FirstReplyCreated`
  - [ ] `ContactMerged`
  - [ ] `ContactDeleted`
  - [ ] `AccountCacheInvalidated`
  - [ ] `PresenceUpdate`

- [ ] **Event Listener Integration**
  - [ ] `WebSocketEventListener` class
  - [ ] Event service provider registration
  - [ ] Proper event-to-broadcast mapping

- [ ] **Payload Parity**
  - [ ] Include `performer` data in all events
  - [ ] Match Rails field naming conventions
  - [ ] Include timestamp in ISO format
  - [ ] Proper relationship data inclusion

#### 6.2 Frontend Implementation Checklist

- [ ] **WebSocket Client**
  - [ ] Laravel Reverb connection setup
  - [ ] Authentication handling
  - [ ] Reconnection logic
  - [ ] Subscription management

- [ ] **Event Handlers**
  - [ ] Account-level event handling
  - [ ] User-level event handling
  - [ ] Conversation-level event handling
  - [ ] Presence event handling

- [ ] **Store Integration**
  - [ ] Conversations store updates
  - [ ] Notifications store updates
  - [ ] Contacts store updates
  - [ ] Presence store implementation

- [ ] **Type Definitions**
  - [ ] WebSocket event interfaces
  - [ ] Event-specific type definitions
  - [ ] Store state interfaces

#### 6.3 Testing Checklist

- [ ] **Backend Tests**
  - [ ] Event broadcasting tests
  - [ ] Channel authorization tests
  - [ ] Payload structure tests
  - [ ] Event listener tests

- [ ] **Frontend Tests**
  - [ ] WebSocket client tests
  - [ ] Event handler tests
  - [ ] Store integration tests
  - [ ] Component integration tests

- [ ] **Integration Tests**
  - [ ] End-to-end WebSocket flow tests
  - [ ] Cross-browser compatibility tests
  - [ ] Performance tests

### Phase 7: Deployment Considerations

#### 7.1 Environment Configuration

```bash
# .env additions for Laravel Reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Frontend environment variables
VITE_WS_URL=ws://localhost:8080
VITE_PUSHER_APP_KEY=your-app-key
VITE_PUSHER_HOST=localhost
VITE_PUSHER_PORT=8080
```

#### 7.2 Production Setup

```bash
# Start Laravel Reverb server
php artisan reverb:start --host=0.0.0.0 --port=8080

# Process queued broadcasts
php artisan queue:work --queue=default,broadcasts

# Monitor WebSocket connections
php artisan reverb:ping
```

## Summary

This migration plan ensures complete parity between Rails ActionCable and Laravel Reverb by:

1. **Implementing all missing events** with proper Laravel conventions
2. **Maintaining payload compatibility** with Rails expectations  
3. **Using Laravel-native channel authorization** for secure, modern WebSocket authentication
4. **Supporting multi-user authentication** (agents via Sanctum, contacts via custom tokens)
5. **Providing comprehensive frontend integration** with SvelteKit
6. **Including thorough testing strategies** for both backend and frontend
7. **Following Laravel best practices** while preserving all functionality

The implementation prioritizes Laravel's secure, modern patterns while ensuring no functionality is lost during the migration from Vue + ActionCable to SvelteKit + Reverb. All Rails features are preserved through Laravel-native approaches rather than compatibility layers.