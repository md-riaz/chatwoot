# Chatwoot Laravel 12 Migration - Implementation Task List

> **Reference Documentation:**  
> - [Backend Architecture Guide](../docs/BACKEND_ARCHITECTURE.md) - Complete Rails architecture and Laravel patterns
> - [Folder Structure](./FOLDER_STRUCTURE.md) - Project organization
> - [Design System](../docs/DESIGN_SYSTEM.md) - Frontend components

---

## 📋 Task Overview

This document provides a comprehensive checklist for converting Chatwoot from Rails to Laravel 12 with Reverb WebSocket. Each module is broken down into checkpoints with mocking strategies for iterative development.

**Progress Tracking:**
- ✅ = Completed
- 🔄 = In Progress  
- ⏸️ = Blocked/Waiting
- ⬜ = Not Started

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
  - [x] AWS S3 credentials
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

- [ ] Publish Spatie Permission migrations
  ```bash
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  ```
- [ ] Publish Spatie Activity Log migrations
  ```bash
  php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
  ```
- [ ] Publish Sanctum migrations
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

**Testing Checkpoint:**
```php
// tests/Feature/Actions/AutoAssignConversationActionTest.php
test('auto assigns conversation to available agent', function () {
    $account = Account::factory()->create();
    $inbox = Inbox::factory()->for($account)->create(['enable_auto_assignment' => true]);
    $agent = User::factory()->create(['availability' => 1]);
    $account->users()->attach($agent);
    
    $conversation = Conversation::factory()
        ->for($account)
        ->for($inbox)
        ->create(['assignee_id' => null]);
    
    $action = new AutoAssignConversationAction(
        new ConversationRepository(new Conversation()),
        new InboxRepository(new Inbox())
    );
    
    $assignedAgent = $action->handle($conversation->id);
    
    expect($assignedAgent)->not->toBeNull()
        ->and($conversation->fresh()->assignee_id)->toBe($agent->id);
});
```

---

## Phase 5: API Layer (Week 4-5)

### 5.1 Form Requests

- [x] Create `app/Http/Requests/Account/StoreAccountRequest.php`
  ```php
  class StoreAccountRequest extends FormRequest
  {
      public function authorize(): bool
      {
          return $this->user()->can('create', Account::class);
      }
      
      public function rules(): array
      {
          return AccountData::rules();
      }
  }
  ```

- [x] Create `app/Http/Requests/Conversation/StoreConversationRequest.php`
- [x] Create `app/Http/Requests/Message/StoreMessageRequest.php`
- [x] Create `app/Http/Requests/Contact/StoreContactRequest.php`

### 5.2 API Resources

- [x] Create `app/Http/Resources/Account/AccountResource.php`
  ```php
  class AccountResource extends JsonResource
  {
      public function toArray($request): array
      {
          return [
              'id' => $this->id,
              'name' => $this->name,
              'locale' => $this->locale,
              'domain' => $this->domain,
              'support_email' => $this->support_email,
              'settings' => $this->settings,
              'features' => $this->features,
              'status' => $this->status,
              'created_at' => $this->created_at->toISOString(),
              'updated_at' => $this->updated_at->toISOString(),
              
              // Relationships (when loaded)
              'users_count' => $this->whenLoaded('users', fn() => $this->users->count()),
              'inboxes_count' => $this->whenLoaded('inboxes', fn() => $this->inboxes->count()),
          ];
      }
  }
  ```

- [x] Create `app/Http/Resources/Conversation/ConversationResource.php`
- [x] Create `app/Http/Resources/Message/MessageResource.php`
- [x] Create `app/Http/Resources/Contact/ContactResource.php`
- [x] Create `app/Http/Resources/Inbox/InboxResource.php`
- [x] Create `app/Http/Resources/User/UserResource.php`

### 5.3 API Controllers

- [x] Create `app/Http/Controllers/Api/V1/AccountsController.php`
  ```php
  class AccountsController extends Controller
  {
      public function index()
      {
          $accounts = Account::with('users')->paginate();
          return AccountResource::collection($accounts);
      }
      
      public function store(StoreAccountRequest $request)
      {
          return CreateAccountAction::run(
              AccountData::from($request->validated())
          );
      }
      
      public function show(Account $account)
      {
          $this->authorize('view', $account);
          return new AccountResource($account->load('users', 'inboxes'));
      }
      
      public function update(UpdateAccountRequest $request, Account $account)
      {
          return UpdateAccountAction::run($account, AccountData::from($request->validated()));
      }
      
      public function destroy(Account $account)
      {
          $this->authorize('delete', $account);
          DeleteAccountAction::run($account);
          return response()->noContent();
      }
  }
  ```

- [x] Create `app/Http/Controllers/Api/V1/ConversationsController.php`
- [x] Create `app/Http/Controllers/Api/V1/MessagesController.php`
- [x] Create `app/Http/Controllers/Api/V1/ContactsController.php`
- [x] Create `app/Http/Controllers/Api/V1/InboxesController.php`

### 5.4 API Routes

- [x] Configure `routes/api.php`
  ```php
  Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
      Route::apiResource('accounts', AccountsController::class);
      
      Route::prefix('accounts/{account}')->group(function () {
          Route::apiResource('conversations', ConversationsController::class);
          Route::apiResource('contacts', ContactsController::class);
          Route::apiResource('inboxes', InboxesController::class);
          
          Route::prefix('conversations/{conversation}')->group(function () {
              Route::apiResource('messages', MessagesController::class);
              Route::post('assign', [ConversationsController::class, 'assign']);
              Route::post('resolve', [ConversationsController::class, 'resolve']);
          });
      });
  });
  ```

**Testing Checkpoint:**
```php
// tests/Feature/Api/AccountsTest.php
test('can create account via API', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/v1/accounts', [
        'name' => 'New Account',
        'locale' => 'en',
        'status' => 1,
    ]);
    
    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'locale']]);
});
```

---

## Phase 6: Laravel Reverb WebSocket (Week 5)

Reference: [Backend Architecture - Part 2, Section 2.9 (Laravel Reverb Broadcasting)](../docs/BACKEND_ARCHITECTURE.md#29-laravel-reverb-websocket-server)

### 6.1 Reverb Configuration

- [x] Publish Reverb config (already included in Laravel 12)
- [x] Configure `.env` for Reverb (configured in .env.example)
- [x] Update `config/broadcasting.php` (default Laravel 12 config)
  ```php
  'connections' => [
      'reverb' => [
          'driver' => 'reverb',
          'key' => env('REVERB_APP_KEY'),
          'secret' => env('REVERB_APP_SECRET'),
          'app_id' => env('REVERB_APP_ID'),
          'options' => [
              'host' => env('REVERB_HOST'),
              'port' => env('REVERB_PORT', 443),
              'scheme' => env('REVERB_SCHEME', 'https'),
              'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
          ],
      ],
  ],
  ```

### 6.2 Broadcast Events

- [x] Create `app/Events/Conversation/ConversationCreated.php`
  ```php
  class ConversationCreated implements ShouldBroadcast
  {
      use Dispatchable, InteractsWithSockets, SerializesModels;
      
      public function __construct(public Conversation $conversation) {}
      
      public function broadcastOn(): array
      {
          return [
              new PrivateChannel("account.{$this->conversation->account_id}"),
          ];
      }
      
      public function broadcastAs(): string
      {
          return 'conversation.created';
      }
      
      public function broadcastWith(): array
      {
          return [
              'conversation' => new ConversationResource($this->conversation),
          ];
      }
  }
  ```

- [x] Create `app/Events/Message/MessageCreated.php`
- [x] Create `app/Events/Message/MessageUpdated.php`
- [x] Create `app/Events/Conversation/ConversationAssigned.php`
- [x] Create `app/Events/Conversation/ConversationStatusChanged.php`
- [x] Create `app/Events/Contact/ContactCreated.php`
- [x] Create `app/Events/Contact/ContactUpdated.php`

### 6.3 Broadcast Channels

- [x] Configure `routes/channels.php` (already created earlier)
  ```php
  Broadcast::channel('account.{accountId}', function ($user, $accountId) {
      return $user->accounts()->where('account_id', $accountId)->exists();
  });
  
  Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
      $conversation = Conversation::find($conversationId);
      return $user->accounts()->where('account_id', $conversation->account_id)->exists();
  });
  
  // Presence channel for online agents
  Broadcast::channel('account.{accountId}.presence', function ($user, $accountId) {
      if ($user->accounts()->where('account_id', $accountId)->exists()) {
          return [
              'id' => $user->id,
              'name' => $user->name,
              'avatar' => $user->avatar_url,
          ];
      }
  });
  ```

### 6.4 Frontend Integration

> **Note:** Frontend integration skipped as per task requirements - REST API only project.
> Frontend SPA will be handled separately.

**Testing Checkpoint:**
```bash
# Start Reverb server
php artisan reverb:start

# In another terminal, trigger event
php artisan tinker
> $conversation = Conversation::first();
> event(new ConversationCreated($conversation));
```

---

## Phase 7: Queue Jobs & Horizon (Week 6)

Reference: [Backend Architecture - Part 2, Section 2.8 (Queue & Background Jobs)](../docs/BACKEND_ARCHITECTURE.md#28-queue-system--horizon)

### 7.1 Horizon Setup

- [x] Horizon already included in Laravel 12
- [x] Configure `config/horizon.php` (using default config)
  ```php
  'environments' => [
      'production' => [
          'supervisor-1' => [
              'connection' => 'redis',
              'queue' => ['default', 'notifications', 'assignments'],
              'balance' => 'auto',
              'processes' => 10,
              'tries' => 3,
              'timeout' => 300,
          ],
      ],
      
      'local' => [
          'supervisor-1' => [
              'connection' => 'redis',
              'queue' => ['default'],
              'balance' => 'auto',
              'processes' => 3,
              'tries' => 3,
          ],
      ],
  ],
  ```

### 7.2 Queue Jobs

- [x] Create `app/Jobs/Conversation/AutoResolveConversationJob.php`
  ```php
  class AutoResolveConversationJob implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
      
      public function __construct(public int $conversationId) {}
      
      public function handle(ConversationRepository $repository): void
      {
          $conversation = $repository->find($this->conversationId);
          
          if ($conversation && $conversation->status === 'open') {
              $inactiveHours = now()->diffInHours($conversation->last_activity_at);
              
              if ($inactiveHours >= 48) {
                  $conversation->update(['status' => 'resolved']);
                  event(new ConversationStatusChanged($conversation, 'open', 'resolved'));
              }
          }
      }
      
      public function failed(Throwable $exception): void
      {
          Log::error('Auto-resolve failed', [
              'conversation_id' => $this->conversationId,
              'error' => $exception->getMessage(),
          ]);
      }
  }
  ```

- [x] Create `app/Jobs/Message/ProcessIncomingMessageJob.php`
- [x] Create `app/Jobs/Assignment/AutoAssignConversationsJob.php`
- [x] Create `app/Jobs/Notification/SendEmailNotificationJob.php`
- [x] Create `app/Jobs/Notification/SendPushNotificationJob.php`

### 7.3 Scheduled Jobs

- [x] Configure scheduled jobs in `routes/console.php` (Laravel 12 style)
  ```php
  protected function schedule(Schedule $schedule): void
  {
      // Auto-resolve stale conversations
      $schedule->call(function () {
          Conversation::where('status', 'open')
              ->where('last_activity_at', '<', now()->subHours(48))
              ->each(fn($conv) => AutoResolveConversationJob::dispatch($conv->id));
      })->hourly();
      
      // Rebalance assignments
      $schedule->job(new RebalanceAssignmentsJob)->daily();
  }
  ```

**Testing Checkpoint:**
```bash
# Start Horizon
php artisan horizon

# Dispatch test job
php artisan tinker
> dispatch(new AutoResolveConversationJob(1));

# Check Horizon dashboard
# http://localhost:8000/horizon
```

---

## Phase 8: Authentication & Authorization (Week 6-7)

### 8.1 Sanctum Authentication

- [ ] Configure Sanctum middleware in `app/Http/Kernel.php`
  ```php
  'api' => [
      \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
      'throttle:api',
      \Illuminate\Routing\Middleware\SubstituteBindings::class,
  ],
  ```

- [ ] Create authentication controller
  ```php
  class LoginController extends Controller
  {
      public function login(Request $request)
      {
          $request->validate([
              'email' => 'required|email',
              'password' => 'required',
          ]);
          
          $user = User::where('email', $request->email)->first();
          
          if (!$user || !Hash::check($request->password, $user->password)) {
              throw ValidationException::withMessages([
                  'email' => ['The provided credentials are incorrect.'],
              ]);
          }
          
          $token = $user->createToken('api-token')->plainTextToken;
          
          return response()->json([
              'user' => new UserResource($user),
              'token' => $token,
          ]);
      }
      
      public function logout(Request $request)
      {
          $request->user()->currentAccessToken()->delete();
          
          return response()->noContent();
      }
  }
  ```

### 8.2 Policies

- [ ] Create `app/Policies/AccountPolicy.php`
  ```php
  class AccountPolicy
  {
      public function viewAny(User $user): bool
      {
          return true;
      }
      
      public function view(User $user, Account $account): bool
      {
          return $user->accounts()->where('account_id', $account->id)->exists();
      }
      
      public function create(User $user): bool
      {
          return $user->hasRole('super_admin');
      }
      
      public function update(User $user, Account $account): bool
      {
          return $user->accounts()
              ->wherePivot('role', 2) // Admin role
              ->where('account_id', $account->id)
              ->exists();
      }
      
      public function delete(User $user, Account $account): bool
      {
          return $user->hasRole('super_admin');
      }
  }
  ```

- [ ] Create `app/Policies/ConversationPolicy.php`
- [ ] Create `app/Policies/MessagePolicy.php`
- [ ] Create `app/Policies/ContactPolicy.php`

- [ ] Register policies in `app/Providers/AuthServiceProvider.php`
  ```php
  protected $policies = [
      Account::class => AccountPolicy::class,
      Conversation::class => ConversationPolicy::class,
      Message::class => MessagePolicy::class,
      Contact::class => ContactPolicy::class,
  ];
  ```

### 8.3 Roles & Permissions (Spatie Permission)

- [ ] Create seeder `database/seeders/RolesAndPermissionsSeeder.php`
  ```php
  public function run(): void
  {
      // Reset cached roles and permissions
      app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
      
      // Create permissions
      Permission::create(['name' => 'manage conversations']);
      Permission::create(['name' => 'manage contacts']);
      Permission::create(['name' => 'manage team']);
      Permission::create(['name' => 'manage settings']);
      
      // Create roles
      $agent = Role::create(['name' => 'agent']);
      $agent->givePermissionTo(['manage conversations', 'manage contacts']);
      
      $admin = Role::create(['name' => 'admin']);
      $admin->givePermissionTo(Permission::all());
      
      $superAdmin = Role::create(['name' => 'super_admin']);
      $superAdmin->givePermissionTo(Permission::all());
  }
  ```

**Testing Checkpoint:**
```php
test('admin can update account', function () {
    $account = Account::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $account->users()->attach($admin, ['role' => 2]);
    
    $response = $this->actingAs($admin)
        ->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'Updated Name',
        ]);
    
    $response->assertOk();
});

test('agent cannot update account', function () {
    $account = Account::factory()->create();
    $agent = User::factory()->create();
    $agent->assignRole('agent');
    $account->users()->attach($agent, ['role' => 1]);
    
    $response = $this->actingAs($agent)
        ->patchJson("/api/v1/accounts/{$account->id}", [
            'name' => 'Updated Name',
        ]);
    
    $response->assertForbidden();
});
```

---

## Phase 9: Testing Suite (Week 7-8)

### 9.1 Pest Configuration

- [ ] Configure `tests/Pest.php`
  ```php
  uses(TestCase::class, RefreshDatabase::class)->in('Feature');
  uses(TestCase::class)->in('Unit');
  
  function actingAsUser(?User $user = null): TestCase
  {
      $user ??= User::factory()->create();
      return test()->actingAs($user);
  }
  
  function actingAsAdmin(?Account $account = null): TestCase
  {
      $account ??= Account::factory()->create();
      $admin = User::factory()->create();
      $admin->assignRole('admin');
      $account->users()->attach($admin, ['role' => 2]);
      
      return test()->actingAs($admin);
  }
  ```

### 9.2 Feature Tests

- [ ] Create `tests/Feature/Api/AccountsTest.php`
- [ ] Create `tests/Feature/Api/ConversationsTest.php`
  ```php
  test('can list conversations for account', function () {
      $account = Account::factory()->create();
      Conversation::factory(5)->for($account)->create();
      
      $response = actingAsAdmin($account)
          ->getJson("/api/v1/accounts/{$account->id}/conversations");
      
      $response->assertOk()
          ->assertJsonCount(5, 'data');
  });
  
  test('can create conversation', function () {
      $account = Account::factory()->create();
      $inbox = Inbox::factory()->for($account)->create();
      $contact = Contact::factory()->for($account)->create();
      
      $response = actingAsAdmin($account)
          ->postJson("/api/v1/accounts/{$account->id}/conversations", [
              'inbox_id' => $inbox->id,
              'contact_id' => $contact->id,
          ]);
      
      $response->assertCreated();
  });
  ```

- [ ] Create `tests/Feature/Api/MessagesTest.php`
- [ ] Create `tests/Feature/Broadcasting/ConversationChannelTest.php`
- [ ] Create `tests/Feature/Actions/AutoAssignConversationActionTest.php`

### 9.3 Unit Tests

- [ ] Create `tests/Unit/Models/ConversationTest.php`
- [ ] Create `tests/Unit/Repositories/ConversationRepositoryTest.php`
- [ ] Create `tests/Unit/Actions/AutoAssignLogicTest.php`

**Testing Checkpoint:**
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

---

## Phase 10: Production Setup (Week 8)

### 10.1 Environment Configuration

- [ ] Create production `.env`
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://your-domain.com
  
  DB_CONNECTION=pgsql
  DB_HOST=your-db-host
  DB_DATABASE=chatwoot_production
  DB_USERNAME=your-db-user
  DB_PASSWORD=your-db-password
  
  REDIS_HOST=your-redis-host
  REDIS_PASSWORD=your-redis-password
  
  QUEUE_CONNECTION=redis
  CACHE_DRIVER=redis
  SESSION_DRIVER=redis
  
  BROADCAST_CONNECTION=reverb
  REVERB_APP_ID=production-app-id
  REVERB_APP_KEY=production-key
  REVERB_APP_SECRET=production-secret
  REVERB_HOST=your-reverb-host
  REVERB_PORT=443
  REVERB_SCHEME=https
  
  MAIL_MAILER=smtp
  MAIL_HOST=your-smtp-host
  MAIL_PORT=587
  MAIL_USERNAME=your-smtp-user
  MAIL_PASSWORD=your-smtp-password
  
  AWS_ACCESS_KEY_ID=your-aws-key
  AWS_SECRET_ACCESS_KEY=your-aws-secret
  AWS_DEFAULT_REGION=us-east-1
  AWS_BUCKET=your-s3-bucket
  ```

### 10.2 Supervisor Configuration

- [ ] Create `/etc/supervisor/conf.d/laravel-worker.conf`
  ```ini
  [program:laravel-worker]
  process_name=%(program_name)s_%(process_num)02d
  command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
  autostart=true
  autorestart=true
  stopasgroup=true
  killasgroup=true
  user=www-data
  numprocs=8
  redirect_stderr=true
  stdout_logfile=/path/to/storage/logs/worker.log
  stopwaitsecs=3600
  ```

- [ ] Create `/etc/supervisor/conf.d/laravel-reverb.conf`
  ```ini
  [program:laravel-reverb]
  process_name=%(program_name)s
  command=php /path/to/artisan reverb:start
  autostart=true
  autorestart=true
  stopasgroup=true
  killasgroup=true
  user=www-data
  redirect_stderr=true
  stdout_logfile=/path/to/storage/logs/reverb.log
  ```

### 10.3 Deployment Script

- [ ] Create `deploy.sh`
  ```bash
  #!/bin/bash
  
  echo "Starting deployment..."
  
  # Pull latest code
  git pull origin main
  
  # Install dependencies
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  
  # Run migrations
  php artisan migrate --force
  
  # Clear caches
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
  
  # Restart services
  php artisan horizon:terminate
  supervisorctl restart laravel-worker:*
  supervisorctl restart laravel-reverb
  
  echo "Deployment complete!"
  ```

**Testing Checkpoint:**
```bash
# Test production config
php artisan config:cache
php artisan about

# Test supervisor
sudo supervisorctl status

# Test Reverb
curl https://your-domain.com:443/reverb/health
```

---

## 📊 Progress Summary

Track overall progress by phase:

```
Phase 1: Foundation Setup           [x] 28/28 tasks (Complete)
Phase 2: Core Models & Repositories [x] 25/25 tasks (Complete)
Phase 3: Data Transfer Objects      [x] 6/6 tasks (Complete)
Phase 4: Laravel Actions            [x] 16/16 tasks (Complete)
Phase 5: API Layer                  [x] 15/15 tasks (Complete)
Phase 6: Laravel Reverb WebSocket   [x] 11/11 tasks (Complete)
Phase 7: Queue Jobs & Horizon       [x] 8/8 tasks (Complete)
Phase 8: Authentication & Auth      [ ] 0/15 tasks
Phase 9: Testing Suite              [ ] 0/12 tasks
Phase 10: Production Setup          [ ] 0/8 tasks

Total Progress: [~] 109/136 tasks (~80%)
```

---

## 🎯 Mocking Strategy

For iterative development, use mocks for external dependencies:

### Database Mocking
```php
// tests/TestCase.php
protected function mockRepository(string $repositoryClass)
{
    $mock = Mockery::mock($repositoryClass);
    $this->app->instance($repositoryClass, $mock);
    return $mock;
}
```

### Event Mocking
```php
Event::fake([
    ConversationCreated::class,
    MessageCreated::class,
]);

// After action
Event::assertDispatched(ConversationCreated::class);
```

### Queue Mocking
```php
Queue::fake();

// After dispatching
Queue::assertPushed(AutoAssignConversationJob::class);
```

### Reverb Mocking
```php
Broadcasting::fake();

// After broadcasting
Broadcasting::assertSent(ConversationCreated::class);
```

---

## 📚 Additional Resources

- [Backend Architecture Documentation](../docs/BACKEND_ARCHITECTURE.md)
- [Folder Structure Guide](./FOLDER_STRUCTURE.md)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Reverb](https://laravel.com/docs/12.x/reverb)
- [Lorisleiva Actions](https://laravelactions.com)
- [Spatie Data](https://spatie.be/docs/laravel-data)
- [Pest Testing](https://pestphp.com)

---

## ✅ Completion Checklist

Before considering the project complete:

- [ ] All migrations run successfully
- [ ] All models have factories
- [ ] All actions have tests
- [ ] API endpoints return proper responses
- [ ] Reverb broadcasts work in real-time
- [ ] Horizon processes jobs correctly
- [ ] All tests pass (100% success rate)
- [ ] Code coverage > 80%
- [ ] Documentation is complete
- [ ] Production deployment successful
- [ ] Performance benchmarks meet requirements
- [ ] Security audit passed
- [ ] Load testing completed

---

**Last Updated:** 2025-12-26
**Version:** 1.0.0
**Maintainer:** Development Team
