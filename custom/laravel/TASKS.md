# ClearLine Laravel 12 Migration - Implementation Task List

> **Reference Documentation:**  
> - [Backend Architecture Guide](../docs/BACKEND_ARCHITECTURE.md) - Complete Rails architecture and Laravel patterns
> - [Folder Structure](./FOLDER_STRUCTURE.md) - Project organization
> - [Design System](../docs/DESIGN_SYSTEM.md) - Frontend components

---

## 📋 Task Overview

This document provides a comprehensive checklist for converting ClearLine from Rails to Laravel 12 with Reverb WebSocket. Each module is broken down into checkpoints with mocking strategies for iterative development.

**Progress Tracking:**
- ✅ = Completed
- 🔄 = In Progress  
- ⏸️ = Blocked/Waiting
- ⬜ = Not Started

---

## Porting Follow-up Log — 2025-12-30

> Objective: Close remaining gaps to reach functional parity with the Rails API across routes, events, jobs, actions, and channel integrations.

### A. Routing & Request Surface
- ✅ Map Rails API surface to Laravel routes/controllers (reviewed `rails routes` vs `routes/api.php`; coverage captured in route groups: public/webhooks/widget/public inbox/platform/authenticated account-scoped + super admin). Documented parity notes below.
- ✅ Add webhook routes/controllers for channels: email (inbound), WhatsApp, SMS, Telegram, Line, Facebook, Twitter/X, Instagram, Voice, Slack, Shopify, API channel (existing), and **added TikTok controller + GET/POST webhook endpoints** for parity.
- ✅ Middleware/auth parity: public webhooks remain unauthenticated; widget/public inbox use token/inbox checks; account/platform/super-admin routes run under `auth:sanctum` with `EnsureAccountAccess`/`EnsureSuperAdmin`; keep locale/timezone via existing middleware stack.

### B. Domain Actions & Jobs
- ✅ Cross-check Rails service/worker/interaction classes against Laravel Actions and Jobs; add Actions for any uncovered flows (auto-resolve, SLA timers, CSAT triggers, reporting ingestion, data import pipeline).
- ✅ Wire corresponding Jobs/queue routing (Horizon) for async work: outbound deliverability, SLA event creation, notifications, campaign sends, data imports, attachment processing.
- 🔄 Validate job retry/timeout policies mirror Rails (sidekiq settings → Laravel job properties/Horizon config).

### C. Events, Listeners & Broadcasting
- ✅ Inventory Rails events and callbacks; add Laravel Events/Listeners for conversation lifecycle, message lifecycle, SLA breaches, assignment changes, contact updates, portal/article updates.
- ✅ Confirm broadcasting channels (Reverb) are defined and guarded; align payload shapes with frontend expectations.
- ✅ Add audit/activity logging parity (reuse Spatie Activity Log where appropriate).

### D. Channel Integrations
- ⬜ For each channel (email, API, web widget, WhatsApp, SMS/Twilio, Telegram, Line, Facebook, Twitter/X, Instagram, TikTok, Voice), document: inbound entrypoint, outbound sender, signature/auth, webhook verification, attachment handling, error paths.
- ⬜ Implement missing repositories/services per channel and connect to Conversations/Messages Actions.
- ⬜ Reconcile provider-specific templates/config fields with Rails schema defaults; migrate seed/config fixtures if needed.

### E. Data & SLA/Reporting
- ⬜ Finish SLA policy application flow: policy selection, Applied SLA creation, SLA event generation, timers/resets, breach notifications.
- ⬜ Align reporting event ingestion and aggregation repos with Rails metrics; ensure background rollups scheduled.
- ⬜ Confirm CSAT survey creation/dispatch/recording matches Rails rules (including business hours and snooze logic).

### F. Notifications & Webhooks
- ⬜ Mirror notification types (database/email/push) and subscription preferences; connect NotificationSubscription model to delivery services.
- ⬜ Port webhook signing/delivery/retry logic and admin UI endpoints for webhook management.

### G. Storage & Media
- ⬜ Hook Active Storage equivalents to existing attachment/media services (uploads, variants, thumbnails, external URLs); ensure disk config aligns with Rails defaults.
- ⬜ Validate large file handling, coordinate/meta persistence, and cleanup jobs.

### H. Operational Readiness
- ⬜ Add parity seeders/fixtures for demo/dev.
- ⬜ Write regression test plan: API contract tests vs Rails, job/queue smoke tests, channel end-to-end mocks.
- ⬜ Update deployment notes (env vars, cron/Horizon schedules, Reverb) to match required services.

---

## Phase 1: Foundation Setup (Week 1)

### 1.1 Environment & Configuration

- [x] Install Laravel 12 with composer
- [x] Install required packages (Sanctum, Horizon, Reverb, Actions, Spatie packages)
- [x] Install Pest testing framework
- [x] Configure `.env` file
  - [x] Database connection (PostgreSQL)
  - [x] Redis configuration
  - [x] Mail settings
  - [x] Local file storage (no S3 - using local disk)
  - [x] Reverb configuration
- [x] Set up environment-specific configs
  - [x] `config/database.php` - PostgreSQL primary
  - [x] `config/queue.php` - Redis queue driver
  - [x] `config/reverb.php` - WebSocket server settings
  - [x] `config/sanctum.php` - API authentication
  - [x] `config/horizon.php` - Queue dashboard
- [x] Remove blade views (REST API only project)
- [x] Create API routes structure (routes/api.php)
- [x] Create broadcast channels (routes/channels.php)

**Testing Checkpoint:**
```bash
php artisan config:cache
php artisan config:clear
php artisan about  # Verify environment
```

---

### 1.2 Database Foundation

#### 1.2.1 Core Tables Migration

- [x] Create migration: `accounts` table
  ```php
  Schema::create('accounts', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('locale')->default('en');
      $table->string('domain')->nullable();
      $table->string('support_email')->nullable();
      $table->json('settings')->nullable();
      $table->json('features')->nullable();
      $table->json('limits')->nullable();
      $table->integer('status')->default(1);
      $table->timestamps();
      $table->softDeletes();
      
      $table->index('domain');
      $table->index('status');
  });
  ```

- [x] Create migration: `users` table
  ```php
  Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->string('display_name')->nullable();
      $table->string('phone_number')->nullable();
      $table->string('avatar_url')->nullable();
      $table->integer('availability')->default(1);
      $table->json('custom_attributes')->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->rememberToken();
      $table->timestamps();
      $table->softDeletes();
      
      $table->index('email');
      $table->index('availability');
  });
  ```

- [x] Create migration: `account_users` pivot table
  ```php
  Schema::create('account_users', function (Blueprint $table) {
      $table->id();
      $table->foreignId('account_id')->constrained()->cascadeOnDelete();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->integer('role')->default(1); // 1=agent, 2=admin
      $table->boolean('active_at')->default(true);
      $table->integer('availability')->default(1);
      $table->json('settings')->nullable();
      $table->timestamps();
      
      $table->unique(['account_id', 'user_id']);
      $table->index(['account_id', 'role']);
  });
  ```

- [x] Create migration: `contacts` table (see Backend Architecture Part 1, Section 4)
- [x] Create migration: `inboxes` table (polymorphic for channels)
- [x] Create migration: `channels` table (polymorphic type)
- [x] Create migration: `contact_inboxes` table
- [x] Create migration: `conversations` table
- [x] Create migration: `messages` table
- [x] Create migration: `labels` table
- [x] Create migration: `teams` table
- [x] Create migration: `team_members` table
- [x] Create migration: `automation_rules` table
- [x] Create migration: `canned_responses` table
- [x] Create migration: `webhooks` table
- [x] Create migration: `notifications` table
- [x] Create migration: `attachments` table
- [x] Create migration: `mentions` table

#### 1.2.2 Package Migrations

- [x] Publish Spatie Permission migrations
  ```bash
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  ```
- [x] Publish Spatie Activity Log migrations
  ```bash
  php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
  ```
- [x] Publish Sanctum migrations
  ```bash
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  ```

**Testing Checkpoint:**
```bash
php artisan migrate
php artisan migrate:status  # Verify all migrations
```

**Mock Data Seeder:**
```php
// database/seeders/DemoDataSeeder.php
public function run()
{
    $account = Account::factory()->create(['name' => 'Demo Account']);
    $user = User::factory()->create(['email' => 'admin@demo.com']);
    $account->users()->attach($user, ['role' => 2]); // Admin
    
    Inbox::factory(3)->for($account)->create();
    Contact::factory(50)->for($account)->create();
    // Mock conversations and messages
}
```

---

## Phase 2: Core Models & Repositories (Week 2)

### 2.1 Eloquent Models

Reference: [Backend Architecture - Part 1, Section 4 (Core Domain Models)](../docs/BACKEND_ARCHITECTURE.md#4-core-domain-models)

#### 2.1.1 Account Model

- [x] Create `app/Models/Account.php`
  ```php
  class Account extends Model
  {
      use HasFactory, SoftDeletes, LogsActivity;
      
      protected $fillable = ['name', 'locale', 'domain', 'support_email', 'settings', 'features', 'limits', 'status'];
      
      protected $casts = [
          'settings' => 'array',
          'features' => 'array',
          'limits' => 'array',
          'status' => 'integer',
      ];
      
      // Relationships
      public function users(): BelongsToMany
      {
          return $this->belongsToMany(User::class, 'account_users')
                      ->withPivot('role', 'availability', 'settings')
                      ->withTimestamps();
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
      
      // Scopes
      public function scopeActive($query)
      {
          return $query->where('status', 1);
      }
  }
  ```

- [x] Create `app/Models/User.php` (extends Authenticatable)
- [x] Create `app/Models/Contact.php`
- [x] Create `app/Models/Inbox.php`
- [x] Create `app/Models/Channels/WebWidget.php` (polymorphic channel)
- [x] Create `app/Models/Channels/Email.php` (polymorphic channel)
- [x] Create `app/Models/Channels/Api.php` (polymorphic channel)
- [x] Create `app/Models/ContactInbox.php`
- [x] Create `app/Models/Conversation.php`
- [x] Create `app/Models/Message.php`
- [x] Create `app/Models/Label.php`
- [x] Create `app/Models/Team.php`
- [x] Create `app/Models/AutomationRule.php`
- [x] Create `app/Models/CannedResponse.php`
- [x] Create `app/Models/Webhook.php`
- [x] Create `app/Models/Attachment.php`

**Testing Checkpoint:**
```bash
php artisan tinker
# Test relationships
$account = Account::first();
$account->users;
$account->inboxes;
```

#### 2.1.2 Model Factories

- [x] Create `database/factories/AccountFactory.php`
- [x] Create `database/factories/UserFactory.php`
- [x] Create `database/factories/ContactFactory.php`
- [x] Create `database/factories/InboxFactory.php`
- [x] Create `database/factories/ConversationFactory.php`
- [x] Create `database/factories/MessageFactory.php`

**Testing Checkpoint:**
```bash
php artisan tinker
Account::factory()->count(5)->create();
User::factory()->count(20)->create();
```

---

### 2.2 Repository Pattern

Reference: [Backend Architecture - Part 2, Section 2.7 (Repository Pattern)](../docs/BACKEND_ARCHITECTURE.md#27-repository-pattern-data-access-layer)

#### 2.2.1 Base Repository

- [x] Create `app/Repositories/BaseRepository.php`
  ```php
  abstract class BaseRepository
  {
      protected Model $model;
      
      public function __construct(Model $model)
      {
          $this->model = $model;
      }
      
      public function all()
      {
          return $this->model->all();
      }
      
      public function find(int $id): ?Model
      {
          return $this->model->find($id);
      }
      
      public function create(array $attributes): Model
      {
          return $this->model->create($attributes);
      }
      
      public function update(int $id, array $attributes): bool
      {
          return $this->model->find($id)->update($attributes);
      }
      
      public function delete(int $id): bool
      {
          return $this->model->find($id)->delete();
      }
  }
  ```

#### 2.2.2 Specific Repositories

- [x] Create `app/Repositories/Account/AccountRepository.php`
- [x] Create `app/Repositories/Conversation/ConversationRepository.php`
  ```php
  class ConversationRepository extends BaseRepository
  {
      public function __construct(Conversation $model)
      {
          parent::__construct($model);
      }
      
      public function findForAccount(int $accountId, array $filters = [])
      {
          $query = $this->model->where('account_id', $accountId);
          
          if (isset($filters['status'])) {
              $query->where('status', $filters['status']);
          }
          
          if (isset($filters['assignee_id'])) {
              $query->where('assignee_id', $filters['assignee_id']);
          }
          
          return $query->with(['contact', 'inbox', 'assignee'])->paginate(25);
      }
      
      public function getUnassignedForInbox(int $inboxId)
      {
          return $this->model
              ->where('inbox_id', $inboxId)
              ->whereNull('assignee_id')
              ->where('status', 'open')
              ->oldest()
              ->get();
      }
  }
  ```

- [x] Create `app/Repositories/Message/MessageRepository.php`
- [x] Create `app/Repositories/Contact/ContactRepository.php`
- [x] Create `app/Repositories/Inbox/InboxRepository.php`

**Testing Checkpoint:**
```php
// tests/Unit/Repositories/ConversationRepositoryTest.php
test('can find conversations for account', function () {
    $account = Account::factory()->create();
    Conversation::factory(5)->for($account)->create();
    
    $repository = new ConversationRepository(new Conversation());
    $results = $repository->findForAccount($account->id);
    
    expect($results)->toHaveCount(5);
});
```

---

## Phase 3: Data Transfer Objects (Week 2-3)

Reference: [Backend Architecture - Part 2, Section 2.5 (Spatie Data DTOs)](../docs/BACKEND_ARCHITECTURE.md#25-spatie-data-dtos-type-safe-data-transfer)

### 3.1 Core DTOs

- [x] Create `app/Data/Account/AccountData.php`
  ```php
  use Spatie\LaravelData\Data;
  
  class AccountData extends Data
  {
      public function __construct(
          public ?int $id,
          public string $name,
          public string $locale,
          public ?string $domain,
          public ?string $support_email,
          public ?array $settings,
          public ?array $features,
          public int $status,
      ) {}
      
      public static function rules(): array
      {
          return [
              'name' => ['required', 'string', 'max:255'],
              'locale' => ['required', 'string', 'max:10'],
              'domain' => ['nullable', 'string', 'unique:accounts,domain'],
              'support_email' => ['nullable', 'email'],
              'status' => ['required', 'integer', 'in:0,1'],
          ];
      }
  }
  ```

- [x] Create `app/Data/Conversation/ConversationData.php`
- [x] Create `app/Data/Conversation/ConversationFilterData.php`
- [x] Create `app/Data/Message/MessageData.php`
- [x] Create `app/Data/Contact/ContactData.php`
- [x] Create `app/Data/Inbox/InboxData.php`

**Testing Checkpoint:**
```php
test('account data validates correctly', function () {
    $data = AccountData::from([
        'name' => 'Test Account',
        'locale' => 'en',
        'status' => 1,
    ]);
    
    expect($data->name)->toBe('Test Account');
});

test('account data validation fails for invalid email', function () {
    AccountData::from([
        'name' => 'Test',
        'locale' => 'en',
        'support_email' => 'invalid-email',
        'status' => 1,
    ]);
})->throws(ValidationException::class);
```

---

## Phase 4: Laravel Actions (Week 3-4)

Reference: [Backend Architecture - Part 2, Section 2.3 (Lorisleiva Actions)](../docs/BACKEND_ARCHITECTURE.md#23-lorisleiva-laravel-actions-business-logic)

### 4.1 Account Actions

- [x] Create `app/Actions/Account/CreateAccountAction.php`
  ```php
  use Lorisleiva\Actions\Concerns\AsAction;
  
  class CreateAccountAction
  {
      use AsAction;
      
      public function __construct(
          private AccountRepository $accountRepository
      ) {}
      
      public function handle(AccountData $data): Account
      {
          $account = $this->accountRepository->create($data->toArray());
          
          // Trigger event
          event(new AccountCreated($account));
          
          return $account;
      }
      
      public function asController(StoreAccountRequest $request): AccountResource
      {
          $account = $this->handle(AccountData::from($request->validated()));
          
          return new AccountResource($account);
      }
      
      public function rules(): array
      {
          return AccountData::rules();
      }
  }
  ```

- [x] Create `app/Actions/Account/UpdateAccountAction.php`
- [x] Create `app/Actions/Account/DeleteAccountAction.php`

### 4.2 Conversation Actions

- [x] Create `app/Actions/Conversation/CreateConversationAction.php`
- [x] Create `app/Actions/Conversation/UpdateConversationAction.php`
- [x] Create `app/Actions/Conversation/AssignConversationAction.php`
- [x] Create `app/Actions/Conversation/CloseConversationAction.php`

### 4.3 Message Actions

- [x] Create `app/Actions/Message/CreateMessageAction.php`
  ```php
  class CreateMessageAction
  {
      use AsAction;
      
      public function __construct(
          private MessageRepository $messageRepository,
          private ConversationRepository $conversationRepository
      ) {}
      
      public function handle(MessageData $data): Message
      {
          DB::transaction(function () use ($data) {
              $message = $this->messageRepository->create($data->toArray());
              
              // Update conversation
              $this->conversationRepository->update($data->conversation_id, [
                  'last_activity_at' => now(),
              ]);
              
              // Trigger events
              event(new MessageCreated($message));
              
              return $message;
          });
      }
      
      // Can run as job
      public function asJob(MessageData $data): void
      {
          $this->handle($data);
      }
  }
  ```

- [x] Create `app/Actions/Message/UpdateMessageAction.php`
- [x] Create `app/Actions/Message/DeleteMessageAction.php`

### 4.4 Contact Actions

- [x] Create `app/Actions/Contact/CreateContactAction.php`
- [x] Create `app/Actions/Contact/UpdateContactAction.php`
- [x] Create `app/Actions/Contact/MergeContactsAction.php`

### 4.5 Assignment Actions

Reference: [Backend Architecture - Part 1, Section 12.1 (Auto-Assignment Implementation)](../docs/BACKEND_ARCHITECTURE.md#121-auto-assignment-feature-complete-implementation)

- [x] Create `app/Actions/Assignment/AutoAssignConversationAction.php`
  ```php
  class AutoAssignConversationAction
  {
      use AsAction;
      
      public function __construct(
          private ConversationRepository $conversationRepository,
          private InboxRepository $inboxRepository
      ) {}
      
      public function handle(int $conversationId): ?User
      {
          $conversation = $this->conversationRepository->find($conversationId);
          
          if (!$conversation || $conversation->assignee_id) {
              return null;
          }
          
          $inbox = $this->inboxRepository->find($conversation->inbox_id);
          
          if (!$inbox->enable_auto_assignment) {
              return null;
          }
          
          $agent = $this->findBestAgent($inbox);
          
          if ($agent) {
              $conversation->update(['assignee_id' => $agent->id]);
              event(new ConversationAssigned($conversation, $agent));
          }
          
          return $agent;
      }
      
      private function findBestAgent(Inbox $inbox): ?User
      {
          // Round-robin or load-based assignment logic
          return $inbox->account
              ->users()
              ->where('availability', 1)
              ->inRandomOrder()
              ->first();
      }
      
      // Can run as listener
      public function asListener(ConversationCreated $event): void
      {
          $this->handle($event->conversation->id);
      }
  }
  ```

- [x] Create `app/Actions/Assignment/ManualAssignConversationAction.php`
- [x] Create `app/Actions/Assignment/UnassignConversationAction.php`

---

### 2.4 Service Migration: AutoAssignment::AssignmentService (Rails)

- [x] Ported to Laravel: `app/Actions/Assignment/BulkAutoAssignConversationsAction.php`
  - Purpose: Bulk assigns unassigned conversations in an inbox to available agents, respecting assignment config and (TODO) rate limits.
  - TODO: Implement round robin and agent rate limiter logic.
  - Test placeholder: `tests/Unit/Actions/Assignment/BulkAutoAssignConversationsActionTest.php`
  - Manual smoke test: `php artisan tinker --execute="app(\\App\\Actions\\Assignment\\BulkAutoAssignConversationsAction::class)->handle(App\\Models\\Inbox::first(), 10)"`

---

## Phase 3: Data Transfer Objects (Week 2-3)
