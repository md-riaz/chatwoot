# Chatwoot Backend Architecture: The Laravel Way

> **Laravel-First Implementation Guide**  
> This document explains Chatwoot's backend architecture from a Laravel perspective, showing how Laravel's elegant patterns provide superior solutions compared to Rails' approach. Perfect for developers migrating from Rails or building similar functionality in Laravel.

---

## Table of Contents

1. [Why Laravel Over Rails](#why-laravel-over-rails)
2. [System Overview](#system-overview)
3. [Core Architecture Patterns](#core-architecture-patterns)
4. [Domain Models & Eloquent ORM](#domain-models--eloquent-orm)
5. [Service Layer: Laravel Actions](#service-layer-laravel-actions)
6. [Repository Pattern](#repository-pattern)
7. [Queue System & Jobs](#queue-system--jobs)
8. [Real-time with Laravel Echo & Pusher](#real-time-with-laravel-echo--pusher)
9. [API Development with Laravel](#api-development-with-laravel)
10. [Authentication & Authorization](#authentication--authorization)
11. [Database Design & Migrations](#database-design--migrations)
12. [Testing Strategy](#testing-strategy)
13. [Complete Feature Implementation](#complete-feature-implementation)
14. [Performance Optimization](#performance-optimization)
15. [Deployment & Scaling](#deployment--scaling)

---

## Why Laravel Over Rails

### Superior Developer Experience

**1. Modern PHP vs Ruby**
- **PHP 8.3+**: JIT compiler, fibers, attributes, union types, named arguments
- **Rails uses Ruby 3**: Slower execution, less tooling, smaller ecosystem
- **Performance**: PHP 8+ is 2-3x faster than Ruby in most benchmarks
- **Type Safety**: PHP's gradual typing with PHPStan/Psalm beats Ruby's Sorbet

```php
// Laravel: Modern PHP with type safety
class MessageService
{
    public function __construct(
        private MessageRepository $messages,
        private EventDispatcher $events,
    ) {}
    
    public function create(CreateMessageDTO $dto): Message
    {
        return DB::transaction(fn() => $this->messages->create($dto));
    }
}
```

```ruby
# Rails: Loose typing, runtime errors
class Messages::MessageBuilder
  def initialize(user, conversation, params)
    @user = user
    @conversation = conversation
    @params = params
  end
  
  def perform
    # No compile-time type checking
    create_message
  end
end
```

**2. Eloquent ORM vs ActiveRecord**

Laravel's Eloquent is more elegant and powerful:

```php
// Laravel: Intuitive, chainable, type-hinted
$conversations = Conversation::query()
    ->where('status', 'open')
    ->with(['messages' => fn($q) => $q->latest()->limit(10)])
    ->whereHas('inbox', fn($q) => $q->where('account_id', $accountId))
    ->latest('last_activity_at')
    ->paginate(25);

// Type hints everywhere, IDE autocomplete perfect
foreach ($conversations as $conversation) {
    $conversation->messages; // Returns Collection<Message>
}
```

```ruby
# Rails: Verbose, less readable
conversations = Conversation
  .where(status: 'open')
  .includes(:messages)
  .joins(:inbox)
  .where(inboxes: { account_id: account_id })
  .order(last_activity_at: :desc)
  .page(params[:page])
  .per(25)

# No type hints, runtime errors common
conversations.each do |conversation|
  conversation.messages # Could be anything
end
```

**3. Laravel's Superior Package Ecosystem**

- **Composer** (PHP) vs **Bundler** (Ruby): Faster, more reliable
- **Laravel Ecosystem**:
  - Laravel Horizon (queue monitoring)
  - Laravel Telescope (debugging)
  - Laravel Sanctum (API authentication)
  - Laravel Cashier (payments)
  - Laravel Scout (search)
  - Laravel Socialite (OAuth)
  
- **Rails Ecosystem**: Fragmented, often unmaintained gems

**4. Better Tooling & IDE Support**

- **PHPStorm**: Best-in-class Laravel support
- **VS Code**: Excellent Laravel extensions (Laravel Extension Pack)
- **Static Analysis**: PHPStan, Psalm, Larastan
- **Rails**: Limited IDE support, weaker tooling

**5. Laravel's Clean Architecture**

```
app/
├── Actions/          # Single-purpose action classes (better than Rails services)
├── Models/           # Eloquent models (cleaner than ActiveRecord)
├── Http/
│   ├── Controllers/  # Thin controllers
│   ├── Requests/     # Form validation (better than Strong Params)
│   └── Resources/    # API responses (better than Jbuilder)
├── Jobs/             # Queue jobs (cleaner than Sidekiq jobs)
├── Events/           # Event system (better than ActiveSupport::Notifications)
├── Listeners/        # Event listeners
└── Repositories/     # Data access layer
```

vs Rails' scattered structure:
```
app/
├── models/
├── controllers/
├── services/      # Not standard
├── builders/      # Custom pattern
├── finders/       # Custom pattern
├── jobs/
└── concerns/      # Overused
```

---

## System Overview

### Laravel Architecture for Customer Support Platform

```
┌────────────────────────────────────────────────────────────────┐
│                      CLIENT LAYER                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐        │
│  │ Vue.js SPA   │  │ Mobile Apps  │  │ Widget SDK   │        │
│  │ (Inertia.js) │  │              │  │              │        │
│  └──────────────┘  └──────────────┘  └──────────────┘        │
└────────────────────────┬───────────────────────────────────────┘
                         │
            ┌────────────┴────────────┐
            │   Laravel API           │
            │   Laravel Echo          │
            │   (Sanctum Auth)        │
            └────────────┬────────────┘
                         │
┌────────────────────────────────────────────────────────────────┐
│                   APPLICATION LAYER (Laravel)                   │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                   HTTP Layer                              │  │
│  │  Controllers → Requests (Validation) → Actions            │  │
│  │  Resources (API Responses)                                │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           │                                     │
│  ┌────────────────────────┴─────────────────────────────────┐  │
│  │                   Business Logic                          │  │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │  │
│  │  │   Actions    │  │ Repositories │  │   Events     │  │  │
│  │  │ (Business    │  │ (Data Access)│  │ (Side        │  │  │
│  │  │  Logic)      │  │              │  │  Effects)    │  │  │
│  │  └──────────────┘  └──────────────┘  └──────────────┘  │  │
│  └────────────────────────┬─────────────────────────────────┘  │
│                           │                                     │
│  ┌────────────────────────┴─────────────────────────────────┐  │
│  │                   Domain Layer                            │  │
│  │  Eloquent Models with Relationships & Business Logic      │  │
│  │  Account → Inbox → Conversation → Message                 │  │
│  │  Contact → ContactInbox                                   │  │
│  │  User → Team                                              │  │
│  └────────────────────────┬─────────────────────────────────┘  │
└───────────────────────────┼──────────────────────────────────┘
                            │
┌───────────────────────────┴──────────────────────────────────┐
│                      DATA LAYER                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │   MySQL/     │  │    Redis     │  │   S3/Storage │       │
│  │  PostgreSQL  │  │ (Cache/Queue)│  │ (Media)      │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└────────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────────┐
│                   BACKGROUND PROCESSING                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐       │
│  │ Laravel Queue│  │   Horizon    │  │  Scheduled   │       │
│  │   Workers    │  │ (Monitoring) │  │    Tasks     │       │
│  └──────────────┘  └──────────────┘  └──────────────┘       │
└────────────────────────────────────────────────────────────────┘
```

### Technology Stack (Laravel Way)

```yaml
Framework: Laravel 11.x
Language: PHP 8.3+
Database: MySQL 8+ / PostgreSQL 15+
Cache/Queue: Redis 7+
Queue: Laravel Queue (Redis driver)
Real-time: Laravel Echo + Pusher/Soketi
Search: Laravel Scout + Meilisearch/Algolia
Storage: Laravel Storage (S3)
Authentication: Laravel Sanctum
API: Laravel API Resources
Testing: Pest / PHPUnit
Static Analysis: Larastan (PHPStan)
```

**Key Packages:**
```json
{
    "require": {
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/horizon": "^5.0",
        "laravel/telescope": "^5.0",
        "spatie/laravel-permission": "^6.0",
        "spatie/laravel-query-builder": "^6.0",
        "spatie/laravel-activitylog": "^4.0",
        "propaganistas/laravel-phone": "^5.0"
    },
    "require-dev": {
        "pestphp/pest": "^2.0",
        "larastan/larastan": "^2.0"
    }
}
```

---

## Core Architecture Patterns

### 1. Action Classes (Better than Rails Services)

**Why Actions Beat Rails Services:**
- Single responsibility
- Type-hinted dependencies
- Easy to test
- Framework-agnostic

```php
<?php

namespace App\Actions\Messages;

use App\Models\{Message, Conversation, User};
use App\Events\MessageCreated;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\DB;

class CreateMessageAction
{
    public function __construct(
        private MessageRepository $messages
    ) {}
    
    public function execute(
        User $user,
        Conversation $conversation,
        string $content,
        string $type = 'outgoing',
        array $attachments = []
    ): Message {
        return DB::transaction(function () use ($user, $conversation, $content, $type, $attachments) {
            // Create message
            $message = $this->messages->create([
                'account_id' => $conversation->account_id,
                'conversation_id' => $conversation->id,
                'inbox_id' => $conversation->inbox_id,
                'sender_id' => $user->id,
                'sender_type' => get_class($user),
                'content' => $content,
                'message_type' => $type,
            ]);
            
            // Handle attachments
            if (!empty($attachments)) {
                $this->attachFiles($message, $attachments);
            }
            
            // Update conversation
            $conversation->update([
                'last_activity_at' => now(),
                'status' => 'open',
            ]);
            
            // Dispatch event
            event(new MessageCreated($message));
            
            return $message->load('sender', 'attachments');
        });
    }
    
    private function attachFiles(Message $message, array $files): void
    {
        foreach ($files as $file) {
            $message->attachments()->create([
                'file_path' => $file->store('attachments', 's3'),
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
            ]);
        }
    }
}
```

**Rails Equivalent** (worse):
```ruby
class Messages::MessageBuilder
  def initialize(user, conversation, params)
    @user = user
    @conversation = conversation
    @params = params
  end
  
  def perform
    ActiveRecord::Base.transaction do
      @message = create_message
      attach_files if @params[:attachments].present?
      update_conversation
      dispatch_events
      trigger_notifications
    end
    @message
  end
  
  # Methods scattered, hard to test individually
end
```

**Why Laravel Action is Better:**
- Constructor injection (dependency inversion)
- Type hints throughout
- Single public method (`execute`)
- Private methods testable via public interface
- No instance variables confusion
- Framework independence

### 2. Repository Pattern (Data Access Layer)

**Why Repositories:**
- Decouple business logic from data access
- Easy to swap implementations (cache, testing)
- Query reusability
- Better than Rails Finders

```php
<?php

namespace App\Repositories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\{Collection, Builder};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ConversationRepository
{
    public function find(int $id): ?Conversation
    {
        return Conversation::query()
            ->with(['inbox', 'contact', 'assignee', 'team'])
            ->find($id);
    }
    
    public function findForAccount(int $accountId, array $filters = []): LengthAwarePaginator
    {
        return $this->query($accountId, $filters)
            ->latest('last_activity_at')
            ->paginate($filters['per_page'] ?? 25);
    }
    
    public function findOpen(int $accountId): Collection
    {
        return $this->query($accountId)
            ->where('status', 'open')
            ->get();
    }
    
    public function findUnassigned(int $accountId, int $inboxId): Collection
    {
        return $this->query($accountId)
            ->where('inbox_id', $inboxId)
            ->whereNull('assignee_id')
            ->where('status', 'open')
            ->get();
    }
    
    private function query(int $accountId, array $filters = []): Builder
    {
        $query = Conversation::query()
            ->where('account_id', $accountId)
            ->with(['inbox', 'contact', 'assignee']);
            
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['inbox_id'])) {
            $query->where('inbox_id', $filters['inbox_id']);
        }
        
        if (isset($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }
        
        if (isset($filters['team_id'])) {
            $query->where('team_id', $filters['team_id']);
        }
        
        return $query;
    }
    
    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }
    
    public function update(Conversation $conversation, array $data): bool
    {
        return $conversation->update($data);
    }
}
```

**Rails Finder** (inferior):
```ruby
class ConversationFinder
  def initialize(current_user, params)
    @current_user = current_user
    @params = params
  end
  
  def perform
    conversations = base_query
    conversations = filter_by_status(conversations)
    conversations = filter_by_assignee(conversations)
    
    {
      conversations: conversations.limit(page_size),
      count: conversations.count
    }
  end
  
  # Less reusable, harder to test
end
```

### 3. Form Requests (Better than Strong Parameters)

```php
<?php

namespace App\Http\Requests\Conversations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Conversation::class);
    }
    
    public function rules(): array
    {
        return [
            'inbox_id' => ['required', 'exists:inboxes,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'message' => ['required', 'string', 'max:10000'],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'], // 10MB
            'source_id' => ['sometimes', 'string'],
            'custom_attributes' => ['sometimes', 'array'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'inbox_id.required' => 'Please select an inbox',
            'contact_id.required' => 'Please select a contact',
            'message.required' => 'Message content is required',
        ];
    }
    
    public function validated($key = null, $default = null)
    {
        // Can transform data here
        $data = parent::validated($key, $default);
        
        if (isset($data['custom_attributes'])) {
            $data['custom_attributes'] = json_encode($data['custom_attributes']);
        }
        
        return $data;
    }
}
```

**Rails Strong Parameters** (worse):
```ruby
def conversation_params
  params.require(:conversation).permit(
    :inbox_id,
    :contact_id,
    :message,
    attachments: [],
    custom_attributes: {}
  )
end
```

**Why Form Requests Win:**
- Validation rules in one place
- Authorization included
- Custom error messages
- Data transformation
- Reusable across controllers
- Better testing

---

## Domain Models & Eloquent ORM

### Why Eloquent Beats ActiveRecord

**1. Cleaner Syntax**
**2. Better Relationship Handling**
**3. Superior Query Builder**
**4. Attribute Casting (Better than Rails serializers)**
**5. Accessors & Mutators (Computed attributes)**

### Core Models

#### 1. Account (Multi-tenant Root)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Relations\HasMany, Relations\HasManyThrough, SoftDeletes};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'domain',
        'support_email',
        'locale',
        'timezone',
        'settings',
        'limits',
        'custom_attributes',
        'status',
    ];
    
    protected $casts = [
        'settings' => 'array',
        'limits' => 'array',
        'custom_attributes' => 'array',
        'status' => 'string',
    ];
    
    // Relationships
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, AccountUser::class);
    }
    
    public function accountUsers(): HasMany
    {
        return $this->hasMany(AccountUser::class);
    }
    
    public function inboxes(): HasMany
    {
        return $this->hasMany(Inbox::class);
    }
    
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
    
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
    
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    // Business Logic Methods
    public function hasFeature(string $feature): bool
    {
        return ($this->settings['features'][$feature] ?? false) === true;
    }
    
    public function withinLimits(string $resource): bool
    {
        $current = $this->{$resource}()->count();
        $limit = $this->limits[$resource] ?? PHP_INT_MAX;
        
        return $current < $limit;
    }
    
    // Accessors
    public function getAutoResolveAfterAttribute(): ?int
    {
        return $this->settings['auto_resolve_after'] ?? null;
    }
}
```

**Rails Equivalent** (verbose):
```ruby
class Account < ApplicationRecord
  include FlagShihTzu
  include Reportable
  
  has_many :account_users, dependent: :destroy
  has_many :users, through: :account_users
  has_many :inboxes, dependent: :destroy
  has_many :conversations, dependent: :destroy
  has_many :contacts, dependent: :destroy
  has_many :teams, dependent: :destroy
  
  validates :name, presence: true
  
  # Need separate gem for feature flags
  has_flags 1 => :custom_branding,
            2 => :inbox_management
  
  # JSONB requires more setup
  store_accessor :settings, :auto_resolve_after, :timezone
end
```

#### 2. Conversation (Core Entity)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany, Relations\BelongsToMany};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'account_id',
        'inbox_id',
        'contact_id',
        'contact_inbox_id',
        'assignee_id',
        'team_id',
        'display_id',
        'status',
        'priority',
        'identifier',
        'additional_attributes',
        'custom_attributes',
        'snoozed_until',
        'last_activity_at',
        'first_reply_created_at',
        'waiting_since',
    ];
    
    protected $casts = [
        'additional_attributes' => 'array',
        'custom_attributes' => 'array',
        'last_activity_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'first_reply_created_at' => 'datetime',
        'waiting_since' => 'datetime',
    ];
    
    // Eloquent Enums (PHP 8.1+)
    protected $enums = [
        'status' => ConversationStatus::class,
        'priority' => ConversationPriority::class,
    ];
    
    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
    
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(Inbox::class);
    }
    
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
    
    public function contactInbox(): BelongsTo
    {
        return $this->belongsTo(ContactInbox::class);
    }
    
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
    
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
    
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
    
    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'conversation_labels');
    }
    
    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', ConversationStatus::OPEN);
    }
    
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assignee_id');
    }
    
    public function scopeForInbox($query, int $inboxId)
    {
        return $query->where('inbox_id', $inboxId);
    }
    
    // Business Logic
    public function isOpen(): bool
    {
        return $this->status === ConversationStatus::OPEN;
    }
    
    public function isUnassigned(): bool
    {
        return $this->assignee_id === null;
    }
    
    public function assignTo(User $agent): void
    {
        $this->update([
            'assignee_id' => $agent->id,
            'status' => ConversationStatus::OPEN,
        ]);
        
        event(new ConversationAssigned($this, $agent));
    }
    
    public function resolve(): void
    {
        $this->update([
            'status' => ConversationStatus::RESOLVED,
        ]);
        
        event(new ConversationResolved($this));
    }
    
    // Events
    protected static function booted()
    {
        static::creating(function ($conversation) {
            if (!$conversation->display_id) {
                $conversation->display_id = static::query()
                    ->where('account_id', $conversation->account_id)
                    ->max('display_id') + 1;
            }
        });
    }
}
```

**Rails Version** (less elegant):
```ruby
class Conversation < ApplicationRecord
  include Labelable
  include AssignmentHandler
  
  belongs_to :account
  belongs_to :inbox
  belongs_to :contact, optional: true
  belongs_to :assignee, class_name: 'User', optional: true
  belongs_to :team, optional: true
  
  has_many :messages, dependent: :destroy
  has_many :labels, through: :conversation_labels
  
  enum status: { open: 0, resolved: 1, pending: 2, snoozed: 3 }
  
  before_create :set_display_id
  
  def set_display_id
    self.display_id = account.conversations.maximum(:display_id).to_i + 1
  end
end
```

#### 3. Message (Polymorphic Relations)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\MorphTo, Relations\HasMany};

class Message extends Model
{
    protected $fillable = [
        'account_id',
        'conversation_id',
        'inbox_id',
        'sender_id',
        'sender_type',
        'message_type',
        'content',
        'content_type',
        'content_attributes',
        'private',
        'status',
        'source_id',
        'additional_attributes',
    ];
    
    protected $casts = [
        'content_attributes' => 'array',
        'additional_attributes' => 'array',
        'private' => 'boolean',
        'message_type' => MessageType::class,
        'content_type' => ContentType::class,
        'status' => MessageStatus::class,
    ];
    
    // Polymorphic relationship
    public function sender(): MorphTo
    {
        return $this->morphTo();
    }
    
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
    
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
    
    // Scopes
    public function scopeIncoming($query)
    {
        return $query->where('message_type', MessageType::INCOMING);
    }
    
    public function scopeOutgoing($query)
    {
        return $query->where('message_type', MessageType::OUTGOING);
    }
    
    public function scopePrivate($query)
    {
        return $query->where('private', true);
    }
    
    // Business Logic
    public function isIncoming(): bool
    {
        return $this->message_type === MessageType::INCOMING;
    }
    
    public function markAsRead(): void
    {
        if ($this->status !== MessageStatus::READ) {
            $this->update(['status' => MessageStatus::READ]);
            event(new MessageRead($this));
        }
    }
    
    // Events
    protected static function booted()
    {
        static::created(function ($message) {
            // Update conversation
            $message->conversation->update([
                'last_activity_at' => now(),
            ]);
            
            // Reopen if incoming
            if ($message->isIncoming() && $message->conversation->status === ConversationStatus::RESOLVED) {
                $message->conversation->update(['status' => ConversationStatus::OPEN]);
            }
            
            // Dispatch events
            event(new MessageCreated($message));
        });
    }
}
```

**Why Eloquent Wins:**
- Automatic type casting
- Cleaner polymorphic syntax (`morphTo()` vs manual setup)
- Model events built-in
- Attribute accessors inline
- Better query builder

---

## Service Layer: Laravel Actions

### Action vs Rails Service Objects

**Laravel Action Pattern:**
```php
<?php

namespace App\Actions\AutoAssignment;

use App\Models\{Conversation, User, Inbox};
use App\Repositories\UserRepository;
use App\Events\ConversationAssigned;

class AssignConversationAction
{
    public function __construct(
        private UserRepository $users
    ) {}
    
    public function execute(Conversation $conversation): ?User
    {
        if (!$this->shouldAutoAssign($conversation)) {
            return null;
        }
        
        $agent = $this->selectAgent($conversation);
        
        if ($agent) {
            $conversation->assignTo($agent);
            event(new ConversationAssigned($conversation, $agent));
        }
        
        return $agent;
    }
    
    private function shouldAutoAssign(Conversation $conversation): bool
    {
        return $conversation->inbox->enable_auto_assignment
            && $conversation->isUnassigned()
            && $conversation->isOpen();
    }
    
    private function selectAgent(Conversation $conversation): ?User
    {
        return app(RoundRobinSelector::class)->execute(
            $conversation->inbox,
            $this->getAvailableAgents($conversation->inbox)
        );
    }
    
    private function getAvailableAgents(Inbox $inbox): Collection
    {
        return $this->users->findAvailableForInbox($inbox->id);
    }
}
```

**Usage:**
```php
// In controller or job
$agent = app(AssignConversationAction::class)->execute($conversation);
```

**Rails Service** (inferior):
```ruby
class AutoAssignment::AssignmentService
  def initialize(conversation:)
    @conversation = conversation
    @inbox = conversation.inbox
  end
  
  def perform
    return unless should_auto_assign?
    agent = select_agent
    assign_conversation(agent) if agent
  end
  
  # Less testable, more coupling
end
```

### Complete Auto-Assignment Feature

**1. Action Class:**
```php
<?php

namespace App\Actions\AutoAssignment;

use App\Models\{User, Inbox};
use Illuminate\Support\Collection;

class RoundRobinSelector
{
    public function execute(Inbox $inbox, Collection $agents): ?User
    {
        if ($agents->isEmpty()) {
            return null;
        }
        
        // Calculate current workload
        $agentLoads = $agents->mapWithKeys(function ($agent) use ($inbox) {
            $activeCount = $agent->assignedConversations()
                ->forInbox($inbox->id)
                ->whereIn('status', ['open', 'pending'])
                ->count();
                
            return [$agent->id => $activeCount];
        });
        
        // Select agent with minimum load
        $selectedAgentId = $agentLoads->sortKeys()->keys()->first();
        
        return $agents->firstWhere('id', $selectedAgentId);
    }
}
```

**2. Event:**
```php
<?php

namespace App\Events;

use App\Models\{Conversation, User};
use Illuminate\Broadcasting\{Channel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Conversation $conversation,
        public User $assignee
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            new Channel("account.{$this->conversation->account_id}"),
            new Channel("user.{$this->assignee->id}"),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'conversation.assigned';
    }
    
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'assignee_id' => $this->assignee->id,
            'assignee_name' => $this->assignee->name,
        ];
    }
}
```

**3. Listener (Side Effects):**
```php
<?php

namespace App\Listeners;

use App\Events\ConversationAssigned;
use App\Notifications\ConversationAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAgentOfAssignment implements ShouldQueue
{
    public function handle(ConversationAssigned $event): void
    {
        $event->assignee->notify(
            new ConversationAssignedNotification($event->conversation)
        );
    }
}
```

**4. Job (if needed):**
```php
<?php

namespace App\Jobs;

use App\Models\Conversation;
use App\Actions\AutoAssignment\AssignConversationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class AutoAssignConversationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public Conversation $conversation
    ) {}
    
    public function handle(AssignConversationAction $action): void
    {
        $action->execute($this->conversation);
    }
}
```

**Why Laravel Pattern Wins:**
- Clear separation of concerns
- Each class has single responsibility
- Easy to test individually
- Type safety throughout
- Event-driven architecture
- Queue support built-in

---

## Queue System & Jobs

### Laravel Queues vs Sidekiq

**Laravel Advantages:**
- Built into framework
- Multiple drivers (Redis, Database, SQS, etc.)
- Horizon for monitoring (better than Sidekiq UI)
- Rate limiting built-in
- Job chaining
- Job batching
- Better error handling

**Example Job:**
```php
<?php

namespace App\Jobs;

use App\Models\Message;
use App\Mail\NewMessageMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    
    public function __construct(
        public Message $message
    ) {}
    
    public function handle(): void
    {
        if (!$this->message->conversation->assignee) {
            return;
        }
        
        Mail::to($this->message->conversation->assignee)
            ->send(new NewMessageMail($this->message));
    }
    
    public function failed(\Throwable $exception): void
    {
        // Log failure, send alert, etc.
        \Log::error('Email notification failed', [
            'message_id' => $this->message->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
```

**Dispatch:**
```php
// Simple
SendEmailNotificationJob::dispatch($message);

// Delayed
SendEmailNotificationJob::dispatch($message)->delay(now()->addMinutes(5));

// Specific queue
SendEmailNotificationJob::dispatch($message)->onQueue('notifications');

// Chaining
SendEmailNotificationJob::dispatch($message)
    ->chain([
        new SendPushNotificationJob($message),
        new UpdateAnalyticsJob($message),
    ]);
```

**Horizon Configuration:**
```php
// config/horizon.php
'defaults' => [
    'supervisor-1' => [
        'connection' => 'redis',
        'queue' => ['default'],
        'balance' => 'auto',
        'processes' => 10,
        'tries' => 3,
    ],
],
'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses' => 10,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
        ],
    ],
],
```

---

## Real-time with Laravel Echo & Pusher

### Why Laravel Echo Beats ActionCable

**Laravel Echo Advantages:**
- Works with Pusher, Soketi (open-source), Ably
- Simpler setup
- Better client library
- Automatic reconnection
- Private/presence channels built-in

**Server-Side Broadcasting:**
```php
<?php

namespace App\Events;

use App\Models\Message;
use App\Http\Resources\MessageResource;
use Illuminate\Broadcasting\{Channel, PresenceChannel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(
        public Message $message
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            // Public channel (widget)
            new Channel("conversation.{$this->message->conversation_id}"),
            
            // Private channel (agents)
            new PrivateChannel("account.{$this->message->account_id}.conversations"),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'message.created';
    }
    
    public function broadcastWith(): array
    {
        return [
            'message' => new MessageResource($this->message),
            'conversation_id' => $this->message->conversation_id,
        ];
    }
}
```

**Client-Side (Vue with Laravel Echo):**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`,
        },
    },
});

// Subscribe to conversation
Echo.channel(`conversation.${conversationId}`)
    .listen('.message.created', (event) => {
        // Add message to UI
        addMessageToConversation(event.message);
    });

// Private channel for agents
Echo.private(`account.${accountId}.conversations`)
    .listen('.conversation.assigned', (event) => {
        // Update conversation list
        updateConversationAssignment(event);
    });

// Presence channel (typing indicators)
Echo.join(`conversation.${conversationId}`)
    .here((users) => {
        // Users currently in conversation
    })
    .joining((user) => {
        // User joined
    })
    .leaving((user) => {
        // User left
    })
    .listenForWhisper('typing', (e) => {
        // Show typing indicator
    });

// Send typing indicator
Echo.join(`conversation.${conversationId}`)
    .whisper('typing', {
        user: currentUser,
    });
```

**Why Better than ActionCable:**
- Cleaner API
- Better documentation
- Multiple backend options
- Easier scaling
- Built-in presence channels
- Whisper events (peer-to-peer)

---

## API Development with Laravel

### API Resources (Better than Jbuilder)

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'display_id' => $this->display_id,
            'status' => $this->status,
            'priority' => $this->priority,
            'inbox' => new InboxResource($this->whenLoaded('inbox')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'team' => new TeamResource($this->whenLoaded('team')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'last_message' => new MessageResource($this->messages->last()),
            'unread_count' => $this->when($request->user(), function () {
                return $this->messages()
                    ->incoming()
                    ->where('status', '!=', 'read')
                    ->count();
            }),
            'custom_attributes' => $this->custom_attributes,
            'additional_attributes' => $this->additional_attributes,
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

**Usage:**
```php
// Single resource
return new ConversationResource($conversation);

// Collection
return ConversationResource::collection($conversations);

// With pagination
return ConversationResource::collection($conversations)
    ->additional(['meta' => ['total' => $conversations->total()]]);
```

**Why Better than Jbuilder:**
- Type-safe
- Reusable
- Conditional inclusion (`whenLoaded`, `when`)
- Better performance (no view rendering)
- Easier testing

### Controller Example

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\{CreateConversationRequest, UpdateConversationRequest};
use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Actions\Conversations\{CreateConversationAction, UpdateConversationAction};
use App\Repositories\ConversationRepository;
use Illuminate\Http\{JsonResponse, Resources\Json\AnonymousResourceCollection};

class ConversationsController extends Controller
{
    public function __construct(
        private ConversationRepository $conversations
    ) {}
    
    public function index(Request $request): AnonymousResourceCollection
    {
        $conversations = $this->conversations->findForAccount(
            $request->user()->currentAccount->id,
            $request->only(['status', 'inbox_id', 'assignee_id', 'per_page'])
        );
        
        return ConversationResource::collection($conversations);
    }
    
    public function show(Conversation $conversation): ConversationResource
    {
        $this->authorize('view', $conversation);
        
        $conversation->load(['messages', 'inbox', 'contact', 'assignee', 'labels']);
        
        return new ConversationResource($conversation);
    }
    
    public function store(
        CreateConversationRequest $request,
        CreateConversationAction $action
    ): JsonResponse {
        $conversation = $action->execute(
            $request->user(),
            $request->validated()
        );
        
        return (new ConversationResource($conversation))
            ->response()
            ->setStatusCode(201);
    }
    
    public function update(
        UpdateConversationRequest $request,
        Conversation $conversation,
        UpdateConversationAction $action
    ): ConversationResource {
        $this->authorize('update', $conversation);
        
        $conversation = $action->execute($conversation, $request->validated());
        
        return new ConversationResource($conversation);
    }
}
```

**Why Laravel Controllers Win:**
- Constructor injection
- Type-hinted parameters
- Route model binding (automatic)
- Form Request validation
- Policy authorization
- Resource responses
- Cleaner, more testable

---

## Authentication & Authorization

### Laravel Sanctum (Better than Devise Token Auth)

**Setup:**
```php
// config/sanctum.php
'expiration' => 60 * 24 * 7, // 7 days

'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
```

**User Model:**
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    
    // Issue token
    public function createToken(string $name, array $abilities = ['*'])
    {
        return $this->createToken($name, $abilities);
    }
}
```

**Login:**
```php
public function login(LoginRequest $request): JsonResponse
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }
    
    $user = User::where('email', $request->email)->first();
    
    $token = $user->createToken('api-token')->plainTextToken;
    
    return response()->json([
        'user' => new UserResource($user),
        'token' => $token,
    ]);
}
```

**Authorization with Policies:**
```php
<?php

namespace App\Policies;

use App\Models\{User, Conversation};

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->account_id === $user->currentAccount->id
            && ($conversation->assignee_id === $user->id
                || $user->isAdministrator()
                || $user->teams->contains($conversation->team_id));
    }
    
    public function update(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation) && $user->isAgent();
    }
    
    public function delete(User $user, Conversation $conversation): bool
    {
        return $conversation->account_id === $user->currentAccount->id
            && $user->isAdministrator();
    }
}
```

**Why Better than Rails:**
- Built into Laravel
- Simpler token management
- Better middleware
- Policy system cleaner than Pundit
- SPA authentication built-in

---

## Database Design & Migrations

### Laravel Migrations (Better than Rails)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained();
            $table->foreignId('contact_inbox_id')->nullable()->constrained();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            
            $table->unsignedInteger('display_id');
            $table->string('status')->default('open');
            $table->string('priority')->nullable();
            $table->string('identifier')->nullable();
            
            $table->json('additional_attributes')->nullable();
            $table->json('custom_attributes')->nullable();
            
            $table->timestamp('snoozed_until')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('agent_last_seen_at')->nullable();
            $table->timestamp('assignee_last_seen_at')->nullable();
            $table->timestamp('contact_last_seen_at')->nullable();
            $table->timestamp('first_reply_created_at')->nullable();
            $table->timestamp('waiting_since')->nullable();
            
            $table->uuid('uuid')->unique();
            $table->timestamps();
            
            // Indexes
            $table->unique(['account_id', 'display_id']);
            $table->index(['account_id', 'inbox_id', 'status', 'assignee_id'], 'conv_account_inbox_status_idx');
            $table->index(['status', 'last_activity_at']);
            $table->index('identifier');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```

**Why Laravel Migrations Win:**
- Fluent API
- Foreign key constraints easy
- Index naming automatic
- Better documentation
- UUID support built-in
- JSON column support
- Morphs (polymorphic) helpers

---

## Testing Strategy

### Pest (Better than RSpec)

```php
<?php

use App\Models\{User, Conversation, Message};
use App\Actions\Messages\CreateMessageAction;

it('creates a message and updates conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    
    $action = app(CreateMessageAction::class);
    
    $message = $action->execute(
        $user,
        $conversation,
        'Hello, world!',
        'outgoing'
    );
    
    expect($message)
        ->content->toBe('Hello, world!')
        ->message_type->toBe('outgoing')
        ->sender_id->toBe($user->id);
        
    expect($conversation->fresh())
        ->last_activity_at->not->toBeNull();
});

it('broadcasts message creation event', function () {
    Event::fake([MessageCreated::class]);
    
    $user = User::factory()->create();
    $conversation = Conversation::factory()->create();
    
    $action = app(CreateMessageAction::class);
    $message = $action->execute($user, $conversation, 'Test', 'outgoing');
    
    Event::assertDispatched(MessageCreated::class, function ($event) use ($message) {
        return $event->message->id === $message->id;
    });
});

it('auto-assigns conversation when enabled', function () {
    $inbox = Inbox::factory()->create(['enable_auto_assignment' => true]);
    $agent = User::factory()->create();
    $inbox->members()->attach($agent);
    
    $conversation = Conversation::factory()->create([
        'inbox_id' => $inbox->id,
        'assignee_id' => null,
    ]);
    
    $action = app(AssignConversationAction::class);
    $assignedAgent = $action->execute($conversation);
    
    expect($assignedAgent)->not->toBeNull()
        ->and($conversation->fresh()->assignee_id)->toBe($agent->id);
});
```

**Why Pest Wins:**
- Cleaner syntax
- Better assertions (`expect()`)
- Built-in parallelization
- Better snapshots
- Faker included
- Dataset testing
- Architecture testing

---

## Performance Optimization

### Laravel Advantages

**1. Query Optimization:**
```php
// Eager loading (N+1 prevention)
$conversations = Conversation::with([
    'messages' => fn($q) => $q->latest()->limit(10),
    'inbox:id,name',
    'contact:id,name,email',
    'assignee:id,name,avatar_url',
])->get();

// Load counts
$conversations = Conversation::withCount('messages')->get();

// Lazy eager loading
$conversations->load('labels');
```

**2. Caching:**
```php
// Remember queries
$conversations = Cache::remember(
    "account.{$accountId}.conversations.open",
    now()->addMinutes(5),
    fn() => Conversation::query()
        ->where('account_id', $accountId)
        ->where('status', 'open')
        ->get()
);

// Model caching
class Conversation extends Model
{
    protected static function boot()
    {
        parent::boot();
        
        static::updated(function ($conversation) {
            Cache::forget("conversation.{$conversation->id}");
        });
    }
}
```

**3. Queue Optimization:**
```php
// Job batching
$batch = Bus::batch([
    new SendEmailNotificationJob($message1),
    new SendEmailNotificationJob($message2),
    new SendEmailNotificationJob($message3),
])->dispatch();

// Rate limiting
RateLimiter::for('notifications', function () {
    return Limit::perMinute(60);
});
```

---

## Deployment & Scaling

### Laravel Advantages

**1. Laravel Forge:**
- One-click deployment
- Zero-downtime deployments
- SSL certificates automatic
- Database backups
- Queue management

**2. Laravel Vapor (Serverless):**
- Auto-scaling
- Pay-per-use
- Global CDN
- Database scaling

**3. Octane (Performance):**
```php
// 10x performance boost
php artisan octane:start --server=swoole
```

---

## Conclusion: Laravel is Superior

### Summary of Advantages

1. **Modern Language**: PHP 8.3+ beats Ruby 3
2. **Better ORM**: Eloquent > ActiveRecord
3. **Cleaner Architecture**: Actions, Repositories, Resources
4. **Superior Tooling**: Horizon, Telescope, Forge, Vapor
5. **Better Testing**: Pest > RSpec
6. **Easier Deployment**: Forge, Vapor, Envoyer
7. **Stronger Typing**: Static analysis with Larastan
8. **Better Documentation**: Laravel docs are legendary
9. **Larger Community**: More packages, more solutions
10. **Better Performance**: PHP 8+ is faster

### Migration Path from Rails

For teams moving from Chatwoot's Rails stack to Laravel:

1. **Database**: Same (PostgreSQL/MySQL)
2. **Models**: 1:1 mapping (ActiveRecord → Eloquent)
3. **Controllers**: Simpler in Laravel
4. **Services**: Actions pattern cleaner
5. **Jobs**: Horizon > Sidekiq
6. **Broadcasting**: Echo > ActionCable
7. **Testing**: Pest > RSpec

**Result**: Better developer experience, better performance, easier maintenance.

---

**Version:** 1.0.0  
**Last Updated:** December 2024  
**For Questions:** Laravel community is massive - solutions everywhere

---

*This document demonstrates why Laravel's elegant approach provides superior solutions to the patterns used in Chatwoot's Rails implementation. The Laravel way is cleaner, more maintainable, and more performant.*
