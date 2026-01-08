# Chatwoot Migration Project - AI Agent Guidelines

This document provides context and guidelines for AI agents working on the Chatwoot migration project, which involves migrating from a Rails backend + Vue frontend to a Laravel API backend + SvelteKit frontend.

## Project Overview

**Current System:**
- Backend: Ruby on Rails (main codebase)
- Frontend: Vue.js (main codebase)
- Database: PostgreSQL
- WebSocket: ActionCable (Rails)

**Target System:**
- Backend: Laravel API (`custom/laravel/`)
- Frontend: SvelteKit SPA (`custom/ui/svelte-ui/`)
- Database: PostgreSQL (maintained)
- WebSocket: Laravel Reverb

## Project Structure

```
/
├── app/                          # Original Rails backend
├── custom/
│   ├── laravel/                  # New Laravel API backend
│   │   ├── app/
│   │   │   ├── Actions/          # Business logic (preferred pattern)
│   │   │   ├── Http/Controllers/ # Thin controllers
│   │   │   ├── Data/             # Spatie Data DTOs
│   │   │   ├── Repositories/     # Data access layer
│   │   │   ├── Events/           # Laravel Events
│   │   │   └── Listeners/        # Event Listeners
│   │   ├── routes/
│   │   │   ├── api.php           # API routes (/api/*)
│   │   │   ├── auth.php          # Auth routes (/auth/*)
│   │   │   └── web.php           # SPA fallback routes (/app/*)
│   │   └── database/
│   └── ui/
│       └── svelte-ui/            # New SvelteKit frontend
│           ├── src/
│           │   ├── routes/       # SvelteKit routes
│           │   ├── lib/          # Shared components/utilities
│           │   └── app.html      # SPA entry point
│           └── build/            # Built assets (copied to Laravel public/)
└── AGENTS.md                     # This file
```

## Migration Scope & Feature Exclusions

### Included Features
**All enterprise and standard features must be migrated EXCEPT the exclusions listed below:**

- ✅ **Authentication & Authorization**: SAML, custom roles, permissions
- ✅ **Advanced Reporting**: Custom dashboards, analytics, metrics
- ✅ **Audit Logs**: Activity tracking, compliance features
- ✅ **Custom Branding**: White-label customization, themes
- ✅ **Agent Capacity Management**: Workload distribution, capacity policies
- ✅ **Advanced Integrations**: CRM, Notion, Linear, Slack, etc.
- ✅ **Enterprise Channels**: All communication channels and their features
- ✅ **Help Center**: Knowledge base, articles, categories
- ✅ **Automation**: Rules, macros, workflows
- ✅ **Team Management**: Teams, roles, hierarchies
- ✅ **Custom Attributes**: Account and contact customization
- ✅ **Webhooks & API**: External integrations and callbacks

### Excluded Features
**The following features are explicitly EXCLUDED from the migration:**

- ❌ **Copilot**: AI assistant functionality and related features
- ❌ **Captain**: AI-powered features, responses, and document processing
- ❌ **AI/ML Components**: Any machine learning or artificial intelligence features

### Rationale for Exclusions
- **Copilot & Captain**: These AI-powered features require specialized infrastructure, models, and processing capabilities that are outside the scope of the current migration
- **Complexity**: AI features add significant complexity to both backend processing and frontend interfaces
- **Dependencies**: These features often depend on external AI services and specialized data processing pipelines
- **Focus**: Excluding AI features allows the migration to focus on core business functionality and user experience

### Implementation Guidelines
1. **Remove AI References**: When migrating code, remove or comment out copilot/captain related functionality
2. **Feature Flags**: Ensure AI features are disabled in feature flag configurations
3. **Database**: Skip migration of AI-related tables and data structures
4. **Frontend**: Exclude AI-related UI components and workflows
5. **Documentation**: Mark AI features as "Not Implemented" in API documentation

## Migration Patterns & Guidelines

### Backend Migration (Rails → Laravel)

#### 1. Controller Pattern
**Rails Pattern:**
```ruby
class Api::V1::AccountsController < ApplicationController
  def index
    @accounts = Account.all
    render json: @accounts
  end
end
```

**Laravel Pattern:**
```php
class AccountsController extends Controller
{
    public function index(ListAccountsAction $action): JsonResponse
    {
        $accounts = $action->execute();
        return AccountResource::collection($accounts);
    }
}
```

#### 2. Business Logic Pattern
**Rails Pattern:**
```ruby
# Often mixed in controllers or models
def create_account(params)
  account = Account.create!(params)
  NotificationService.notify_admins(account)
  account
end
```

**Laravel Pattern:**
```php
// app/Actions/CreateAccountAction.php
class CreateAccountAction
{
    public function execute(CreateAccountData $data): Account
    {
        $account = $this->accountRepository->create($data->toArray());
        
        event(new AccountCreated($account));
        
        return $account;
    }
}
```

#### 3. Data Transfer Objects
**Use Spatie Data for type-safe request/response handling:**
```php
// app/Data/CreateAccountData.php
class CreateAccountData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
    ) {}
}
```

#### 4. Repository Pattern
```php
// app/Repositories/AccountRepository.php
class AccountRepository
{
    public function findWithMetrics(int $id): Account
    {
        return Account::with(['users', 'conversations'])
            ->where('id', $id)
            ->firstOrFail();
    }
}
```

### Frontend Migration (Vue → SvelteKit)

#### 1. Component Structure
**Vue Pattern:**
```vue
<template>
  <div class="account-card">
    <h3>{{ account.name }}</h3>
    <p>{{ account.email }}</p>
  </div>
</template>

<script>
export default {
  props: ['account']
}
</script>
```

**SvelteKit Pattern:**
```svelte
<!-- AccountCard.svelte -->
<script>
  let { account } = $props();
</script>

<div class="account-card">
  <h3>{account.name}</h3>
  <p>{account.email}</p>
</div>
```

#### 2. State Management
**Vue (Vuex/Pinia):**
```js
const store = useAccountStore()
store.fetchAccounts()
```

**SvelteKit (Runes):**
```svelte
<script>
  import { accountsStore } from '$lib/stores/accounts.js';
  
  let accounts = $state([]);
  
  $effect(() => {
    accountsStore.fetchAccounts().then(data => {
      accounts = data;
    });
  });
</script>
```

#### 3. API Integration
**Create consistent API client:**
```js
// src/lib/api/client.js
export class ApiClient {
  async get(endpoint) {
    const response = await fetch(`/api/v1${endpoint}`);
    return response.json();
  }
  
  async post(endpoint, data) {
    const response = await fetch(`/api/v1${endpoint}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    return response.json();
  }
}
```

#### 4. Automatic API Data Transformation
**CRITICAL**: The SvelteKit application includes automatic case conversion between frontend and backend:

**Frontend Code (camelCase)**:
```typescript
// TypeScript interfaces and component code use camelCase
interface Account {
  usersCount: number;
  inboxesCount: number;
  conversationsCount: number;
  contactsCount: number;
}

// Component usage
let account = $state({
  usersCount: 0,
  inboxesCount: 0
});
```

**API Transformation Layer** (`src/lib/api/transformers.ts`):
- **Outgoing Requests**: Automatically converts camelCase → snake_case
  - `usersCount` → `users_count`
  - `inboxesCount` → `inboxes_count`
- **Incoming Responses**: Automatically converts snake_case → camelCase  
  - `users_count` → `usersCount`
  - `inboxes_count` → `inboxesCount`

**Backend API (snake_case)**:
```php
// Laravel API returns snake_case (Rails convention)
{
  "users_count": 42,
  "inboxes_count": 5,
  "conversations_count": 128
}
```

**Key Rules**:
1. **Always use camelCase in frontend TypeScript/JavaScript code**
2. **Never manually convert case** - the API client handles this automatically
3. **Backend APIs should use snake_case** (Rails/Laravel convention)
4. **Transformation happens in API client hooks** - transparent to components
5. **Check `src/lib/api/transformers.ts` for transformation logic**

This ensures consistent code style while maintaining compatibility between frontend (JavaScript camelCase) and backend (Rails/Laravel snake_case) conventions.

## Development Workflow

### 1. Setting Up Development Environment
```bash
# Laravel backend
cd custom/laravel
composer install
php artisan migrate
php artisan serve # http://localhost:8000

# SvelteKit frontend  
cd custom/ui/svelte-ui
npm install
npm run dev # http://localhost:5173
```

### 2. API Development Process
1. **Create Action** for business logic
2. **Create Repository** for data access
3. **Create Data DTOs** for request/response
4. **Create Controller** (thin, delegates to Action)
5. **Add API routes** in `routes/api.php`
6. **Test with Postman/Thunder Client**

### 3. Frontend Development Process
1. **Create API client methods** in `src/lib/api/`
2. **Create Svelte components** in `src/lib/components/`
3. **Create routes** in `src/routes/app/`
4. **Implement state management** with runes
5. **Style with Tailwind CSS**

## Key Migration Considerations

### 1. Authentication
- Rails: Devise + JWT
- Laravel: Laravel Sanctum + custom auth
- Frontend: Store tokens in httpOnly cookies

### 2. WebSocket Integration
- Rails: ActionCable
- Laravel: Laravel Reverb
- Frontend: Native WebSocket API or Socket.io client

### 3. File Uploads
- Rails: Active Storage
- Laravel: Laravel Storage with S3/local disk
- Frontend: FormData with progress tracking

### 4. Background Jobs
- Rails: Sidekiq
- Laravel: Laravel Horizon + Redis
- Queue jobs for email, notifications, etc.

### 5. Database Migrations
- Keep existing PostgreSQL schema
- Use Laravel migrations to match Rails schema
- Maintain data integrity during transition

## Laravel-Rails API Parity Guidelines

### ✅ COMPLETED: Superadmin Onboarding Feature Parity

The Laravel API now has **complete feature parity** with Rails for superadmin onboarding and feature initialization:

**✅ Implemented Components:**
1. **ConfigLoaderService** (`app/Services/ConfigLoaderService.php`) - Processes YAML configuration files
2. **AccountObserver** (`app/Observers/AccountObserver.php`) - Automatically initializes features on account creation
3. **Feature Flag System** - Complete bit flag system with 30+ features mapped
4. **Installation Commands** - Console commands for configuration management
5. **Onboarding Controllers** - Full API endpoints with feature initialization
6. **Database Seeders** - Automatic configuration loading during setup
7. **Comprehensive Tests** - Full test coverage in `SuperAdminOnboardingTest`

**✅ Feature Parity Achieved:**
- ✅ **Redis Flag Control**: Uses same `chatwoot_installation_onboarding` key as Rails
- ✅ **SuperAdmin Creation**: Creates `type: 'SuperAdmin'` users with confirmed email
- ✅ **Administrator Role**: Links user to account as administrator via AccountUser
- ✅ **Feature Initialization**: Automatically enables 20+ default features from config
- ✅ **Configuration Loading**: Processes `features.yml` and `installation_config.yml`
- ✅ **Default Features**: Enables same feature set as Rails (email, channels, macros, teams, etc.)
- ✅ **Premium Features**: Keeps advanced features disabled (audit logs, SLA, custom roles)
- ✅ **AI Features Excluded**: Copilot/Captain features remain disabled per migration guidelines

**✅ Usage:**
```bash
# Setup (development)
php artisan migrate --seed

# Setup (production)
php artisan installation:initialize --enable-onboarding

# Test
php artisan test tests/Feature/Onboarding/SuperAdminOnboardingTest.php
```

**✅ Documentation:** Complete implementation guide in `custom/laravel/ONBOARDING_IMPLEMENTATION.md`

### ✅ COMPLETED: Account Seeding Feature Parity

The Laravel API now has **complete feature parity** with Rails for account demo data seeding:

**✅ Implemented Components:**
1. **SeedAccountJob** (`app/Jobs/SeedAccountJob.php`) - Asynchronous job for account seeding
2. **AccountSeederService** (`app/Services/AccountSeederService.php`) - Complete seeding logic matching Rails
3. **Seed Data YAML** (`storage/app/seed_data.yml`) - Demo data configuration file
4. **API Endpoint** - `/api/v1/super_admin/accounts/{account}/seed` endpoint implementation
5. **Configuration** - `ENABLE_ACCOUNT_SEEDING` environment variable control

**✅ Demo Data Created:**
- ✅ **Teams**: 4 teams (Sales, Management, Administration, Warehouse) with emoji icons
- ✅ **Custom Roles**: 6 roles with specific permissions (Customer Support Lead, Sales Rep, etc.)
- ✅ **Users**: 14 demo users with realistic names, emails, and role assignments
- ✅ **Labels**: 6 colored labels for conversation categorization
- ✅ **Inboxes**: 9 different channel types (Website, Facebook, Twitter, WhatsApp, SMS, Email, API, Telegram, Line)
- ✅ **Contacts**: 9 demo contacts with conversation history
- ✅ **Conversations**: Multiple conversations with realistic message exchanges
- ✅ **Canned Responses**: 50 auto-generated quick response templates

**✅ Security Features:**
- ✅ **Production Safety**: Automatically disabled in production environment
- ✅ **Environment Control**: `ENABLE_ACCOUNT_SEEDING=true` required for seeding
- ✅ **Data Cleanup**: Removes existing data before seeding to prevent duplicates

**✅ Usage:**
```bash
# Enable seeding (add to .env)
ENABLE_ACCOUNT_SEEDING=true

# API call to seed account
POST /api/v1/super_admin/accounts/{account_id}/seed

# Process seeding job
php artisan queue:work
```

**✅ Rails Parity:** Matches `Internal::SeedAccountJob` and `Seeders::AccountSeeder` functionality exactly

---

**CRITICAL**: Maintaining functional parity between Rails backend and Laravel API is essential for seamless migration. Follow these patterns to ensure consistency:

### 1. Pagination Format Consistency

**Laravel Standard (Maintain This)**:
```php
// Laravel Controller - Use built-in pagination
$users = User::query()
    ->with(['roles', 'accountUsers.account'])
    ->paginate($request->input('per_page', 25));

// Transform collection while preserving Laravel pagination structure
$users->getCollection()->transform(function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        // ... other fields
    ];
});

return response()->json($users); // Returns Laravel's standard pagination format
```

**Laravel Pagination Response Format**:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 25,
  "total": 100,
  "from": 1,
  "to": 25,
  "path": "http://localhost:8000/api/v1/users",
  "links": {...}
}
```

**Frontend Handling**:
```typescript
// TypeScript interface for Laravel pagination
interface UsersListResponse {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

// Component usage
const response = await api.getUsers(params);
users = response.data;           // User array
totalPages = response.last_page; // Laravel pagination
totalCount = response.total;     // Total records
```

### 2. Data Transformation for Rails Parity

**User Data Structure (Rails Compatible)**:
```php
// Laravel Controller - Transform to match Rails format
$users->getCollection()->transform(function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'display_name' => $user->display_name,
        'phone_number' => $user->phone_number,
        'avatar_url' => $user->avatar_url,
        'availability' => $user->availability,
        
        // Rails compatibility fields
        'confirmed' => !is_null($user->email_verified_at),
        'locked' => $user->custom_attributes['locked'] ?? false,
        'role' => $user->getRoleNames()->first() ?? 'agent',
        'roles' => $user->getRoleNames()->toArray(),
        'accounts_count' => $user->accounts_count,
        'custom_attributes' => $user->custom_attributes,
        
        // ISO timestamp format (Rails compatible)
        'created_at' => $user->created_at?->toISOString(),
        'updated_at' => $user->updated_at?->toISOString(),
        
        // Account relationships
        'accounts' => $user->accountUsers->map(function ($accountUser) {
            return [
                'id' => $accountUser->account_id,
                'name' => $accountUser->account->name ?? '',
                'role' => $accountUser->role_name,
                'availability' => $accountUser->availability_name,
                'active_at' => $accountUser->active_at,
            ];
        }),
    ];
});
```

### 3. Status Field Mapping

**Rails → Laravel Field Mapping**:
```php
// Confirmation status
'confirmed' => !is_null($user->email_verified_at)  // Rails: confirmed?, Laravel: email_verified_at

// Lock status  
'locked' => $user->custom_attributes['locked'] ?? false  // Rails: locked?, Laravel: custom_attributes.locked

// Role information
'role' => $user->getRoleNames()->first() ?? 'agent'  // Rails: role, Laravel: Spatie roles
'roles' => $user->getRoleNames()->toArray()          // Rails: roles, Laravel: Spatie roles array

// Availability
'availability' => $user->availability  // Rails: availability, Laravel: availability enum
```

### 4. Relationship Data Structure

**Account-User Relationships**:
```php
// Ensure account relationships match Rails format
'accounts' => $user->accountUsers->map(function ($accountUser) {
    return [
        'id' => $accountUser->account_id,
        'name' => $accountUser->account->name ?? '',
        'role' => $accountUser->role_name,        // Use enum getName() method
        'availability' => $accountUser->availability_name,  // Use enum getName() method
        'active_at' => $accountUser->active_at,
    ];
})
```

### 5. API Response Consistency Rules

**CRITICAL RULES for API Parity**:

1. **Always check Rails backend first** - Before implementing Laravel endpoints, examine the Rails equivalent:
   ```bash
   # Find Rails controllers
   find app/controllers -name "*users*" -type f
   
   # Check Rails API views/serializers
   find app/views -name "*user*" -type f
   ```

2. **Maintain Laravel conventions** - Don't override Laravel's built-in patterns:
   - Use Laravel's standard pagination format
   - Use `transform()` on collections, not custom response structures
   - Preserve Laravel's response metadata (links, path, etc.)

3. **Field name consistency** - Ensure field names match between Rails and Laravel:
   ```php
   // Good: Match Rails field names
   'confirmed' => !is_null($user->email_verified_at)
   
   // Bad: Use Laravel-specific names
   'email_verified' => !is_null($user->email_verified_at)
   ```

4. **Timestamp format standardization**:
   ```php
   // Always use ISO format for Rails compatibility
   'created_at' => $user->created_at?->toISOString()
   'updated_at' => $user->updated_at?->toISOString()
   ```

5. **Frontend TypeScript interfaces** - Update interfaces to match Laravel responses:
   ```typescript
   // Update API client return types
   getUsers: async (params?: PaginationParams): Promise<UsersListResponse>
   
   // Not: Promise<{ data: User[] }>
   ```

### 6. Testing Parity

**Verify API Parity**:
```php
// Laravel Test - Ensure response matches Rails format
test('users index returns rails-compatible format', function () {
    $user = User::factory()->create();
    
    $response = $this->getJson('/api/v1/super_admin/users');
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'email', 'confirmed', 'locked', 
                    'role', 'roles', 'accounts_count', 'created_at'
                ]
            ],
            'current_page', 'last_page', 'total'
        ]);
});
```

**Frontend Integration Testing**:
```typescript
// Test that frontend correctly handles Laravel pagination
test('handles Laravel pagination format', async () => {
  const mockResponse = {
    data: [{ id: 1, name: 'Test User' }],
    current_page: 1,
    last_page: 5,
    total: 100
  };
  
  // Verify component handles response correctly
  expect(component.users).toEqual(mockResponse.data);
  expect(component.totalPages).toBe(mockResponse.last_page);
});
```

### 7. Common Parity Pitfalls to Avoid

1. **Don't override Laravel pagination** - Use `transform()` instead of custom response structures
2. **Don't ignore Rails field names** - Always check Rails API responses for field naming
3. **Don't skip relationship data** - Include all Rails relationship data in Laravel responses
4. **Don't forget enum transformations** - Convert Laravel enums to Rails-compatible strings
5. **Don't use different timestamp formats** - Always use ISO strings for consistency

This ensures seamless migration while maintaining both Laravel best practices and Rails API compatibility.

## Code Quality Standards

### Backend (Laravel)
- Follow PSR-12 coding standards
- Use PHP 8.2+ features (typed properties, enums, etc.)
- Write tests with Pest/PHPUnit
- Use Laravel's built-in validation
- Implement proper error handling and logging

### Frontend (SvelteKit)
- Use TypeScript for type safety
- Follow Svelte 5 runes patterns
- Implement proper error boundaries
- Use Tailwind CSS for styling
- Write component tests with Vitest

## Testing Strategy

### Backend Testing
```php
// tests/Feature/AccountsTest.php
test('can list accounts', function () {
    Account::factory()->count(3)->create();
    
    $response = $this->getJson('/api/v1/accounts');
    
    $response->assertOk()
        ->assertJsonCount(3, 'data');
});
```

### Frontend Testing
```js
// src/lib/components/AccountCard.test.js
import { render } from '@testing-library/svelte';
import AccountCard from './AccountCard.svelte';

test('renders account information', () => {
  const account = { name: 'Test Account', email: 'test@example.com' };
  const { getByText } = render(AccountCard, { account });
  
  expect(getByText('Test Account')).toBeInTheDocument();
  expect(getByText('test@example.com')).toBeInTheDocument();
});
```

## Deployment Considerations

### Environment Configuration
- Use `.env` files for both Laravel and SvelteKit
- Configure CORS for API access
- Set up proper session/cookie domains
- Configure WebSocket connections for production

## Common Pitfalls to Avoid

1. **Don't mix Rails and Laravel patterns** - Use Laravel conventions consistently
2. **Avoid heavy controllers** - Keep business logic in Actions
3. **Don't ignore type safety** - Use DTOs and TypeScript
4. **Avoid direct DB queries in controllers** - Use Repositories
5. **Don't forget error handling** - Implement proper exception handling
6. **Avoid inline styles** - Use Tailwind CSS classes consistently
7. **NEVER manually convert camelCase/snake_case** - The API transformation layer handles this automatically
8. **Always use camelCase in frontend code** - Even when you know the backend uses snake_case
9. **Don't bypass the API client** - Always use the configured API client to ensure transformations work
10. **Don't migrate excluded features** - Skip copilot/captain functionality entirely
11. **Don't leave AI feature stubs** - Remove AI-related code completely rather than leaving empty implementations
12. **Don't enable AI feature flags** - Ensure copilot/captain features remain disabled in all environments

### Laravel Configuration Pitfalls

13. **NEVER use `app()` helper in config files** - Config files are loaded before the application container is available
    ```php
    // ❌ BAD - Will cause "Target class [env] does not exist" error
    'enable_feature' => env('ENABLE_FEATURE', !app()->environment('production'))
    
    // ✅ GOOD - Use env() directly for environment checks
    'enable_feature' => env('ENABLE_FEATURE', env('APP_ENV', 'production') !== 'production')
    ```
14. **Don't use Laravel helpers in config loading** - Stick to basic PHP and `env()` function only
15. **Always provide fallback values in config** - Use `env('KEY', 'default_value')` pattern consistently

### Laravel-Rails API Parity Pitfalls

16. **NEVER override Laravel pagination format** - Use `transform()` on collections, maintain Laravel's standard pagination structure
17. **Always check Rails backend first** - Examine Rails controllers/serializers before implementing Laravel endpoints
18. **Don't use Laravel-specific field names in API responses** - Maintain Rails field naming for compatibility (e.g., `confirmed` not `email_verified`)
19. **Don't ignore Rails relationship structures** - Include all Rails relationship data in Laravel API responses
20. **Don't skip enum transformations** - Convert Laravel enums to Rails-compatible string values using `getName()` methods
21. **Don't use different timestamp formats** - Always use `toISOString()` for Rails compatibility
22. **Don't forget to update TypeScript interfaces** - Match Laravel pagination response structure in frontend types
23. **Don't bypass Laravel conventions for Rails compatibility** - Transform data while preserving Laravel patterns

## Additional Documentation References

### Laravel Development Guidelines
**File**: `custom/laravel/AGENTS.md`

Key Laravel-specific patterns to follow:
- **Actions Pattern**: Use `lorisleiva/laravel-actions` for business logic
- **Spatie Data DTOs**: Type-safe request/response handling in `app/Data/`
- **Repository Pattern**: Data access layer in `app/Repositories/`
- **Events & Listeners**: Decouple side-effects using Laravel Events
- **PSR-12 Standards**: Follow PHP coding standards
- **Conventional Commits**: Use `type(scope): subject` format

**Build Commands**:
```bash
# Laravel setup
composer install && pnpm install
php artisan serve
php artisan reverb:start  # WebSocket
php artisan horizon       # Queue processing

# Testing
php artisan test          # Pest/PHPUnit
pnpm test                # Vitest for JS
```

### SvelteKit Development Guidelines
**File**: `custom/ui/svelte-ui/llms.txt`

Comprehensive Svelte 5 documentation including:
- **Runes System**: `$state`, `$derived`, `$effect`, `$props`, `$bindable`
- **Component Architecture**: Modern reactive patterns
- **State Management**: Reactive state without external libraries
- **Event Handling**: Modern event binding patterns
- **Styling**: Scoped CSS and class directives
- **TypeScript Integration**: Type-safe component development

**Key Svelte 5 Patterns**:
```svelte
<script>
  // Reactive state
  let count = $state(0);
  
  // Derived values
  let doubled = $derived(count * 2);
  
  // Component props
  let { title, items = [] } = $props();
  
  // Effects for side effects
  $effect(() => {
    console.log('Count changed:', count);
  });
</script>
```

## SPA Integration Patterns

### Asset Build & Deployment
Following `custom/laravel/AGENTS.md` patterns:

1. **Development**:
   ```bash
   # Frontend (http://localhost:5173)
   cd custom/ui/svelte-ui && npm run dev
   
   # Backend (http://localhost:8000)  
   cd custom/laravel && php artisan serve
   ```

2. **Production**:
   ```bash
   # Build SvelteKit SPA
   cd custom/ui/svelte-ui && npm run build
   
   # Copy to Laravel public directory
   cp -r build/* ../../laravel/public/app/
   ```

### Routing Configuration
- **API Routes**: `/api/*` → Laravel API (`routes/api.php`)
- **Auth Routes**: `/auth/*` → Laravel Auth (`routes/auth.php`)  
- **SPA Routes**: `/app/*` → SvelteKit SPA (`public/app/index.html`)

### Development Proxy Setup
```js
// vite.config.js in SvelteKit
export default {
  server: {
    proxy: {
      '/api': 'http://localhost:8000',
      '/auth': 'http://localhost:8000',
    }
  }
}
```

## Resources for AI Agents

When working on this migration:

1. **Always check existing patterns** in `custom/laravel/` and `custom/ui/svelte-ui/`
2. **Reference documentation files**:
   - `custom/laravel/AGENTS.md` for Laravel patterns
   - `custom/ui/svelte-ui/llms.txt` for Svelte 5 documentation
   - `custom/laravel/FOLDER_STRUCTURE.md` for architecture details
3. **Follow the Action → Repository → Model flow** for backend development
4. **Use Svelte 5 runes** for reactive state management (`$state`, `$derived`, `$effect`)
5. **Maintain API consistency** with proper versioning and resource formatting
6. **Reference original Rails code** for business logic understanding
7. **IMPORTANT**: Always use camelCase in frontend code - API transformation is automatic
8. **Check API transformation layer** in `src/lib/api/transformers.ts` and `src/lib/api/client.ts`

### Rails Backend Parity Workflow

**CRITICAL**: Before implementing any Laravel API endpoint, follow this workflow:

1. **Find Rails equivalent**:
   ```bash
   # Search for Rails controllers
   find app/controllers -name "*users*" -type f
   find app/controllers -name "*accounts*" -type f
   
   # Search for API views/serializers
   find app/views -name "*user*" -type f
   find app/views -name "*account*" -type f
   ```

2. **Analyze Rails response structure**:
   ```ruby
   # Check Rails controller actions
   # Look for render json: statements
   # Examine jbuilder templates in app/views/
   ```

3. **Identify Rails field mappings**:
   ```ruby
   # Rails model methods to check:
   user.confirmed?        # → Laravel: !is_null($user->email_verified_at)
   user.locked?           # → Laravel: $user->custom_attributes['locked'] ?? false
   user.role              # → Laravel: $user->getRoleNames()->first()
   user.account_users     # → Laravel: $user->accountUsers relationship
   ```

4. **Implement Laravel with Rails parity**:
   ```php
   // Use transform() to match Rails structure
   $users->getCollection()->transform(function ($user) {
       return [
           // Match Rails field names exactly
           'confirmed' => !is_null($user->email_verified_at),
           'locked' => $user->custom_attributes['locked'] ?? false,
           // Include all Rails relationship data
           'accounts' => $user->accountUsers->map(...),
       ];
   });
   ```

5. **Update frontend TypeScript interfaces**:
   ```typescript
   // Match Laravel pagination format
   interface UsersListResponse {
     data: User[];
     current_page: number;
     last_page: number;
     total: number;
   }
   ```

6. **Test parity**:
   ```bash
   # Compare API responses
   curl http://rails-app/api/v1/users
   curl http://laravel-app/api/v1/super_admin/users
   
   # Verify field names and structure match
   ```

## Getting Help

- **Laravel Patterns**: Check `custom/laravel/AGENTS.md` and existing implementations
- **Svelte Patterns**: Reference `custom/ui/svelte-ui/llms.txt` for comprehensive documentation
- **Architecture**: Review `custom/laravel/FOLDER_STRUCTURE.md` for structure guidelines
- **API Consistency**: Look at existing API endpoints in `custom/laravel/routes/`
- **Business Logic**: Check original Rails code for context and requirements

---

This document should be updated as the migration progresses and new patterns emerge. Always reference the specific documentation files mentioned above for detailed implementation guidance.