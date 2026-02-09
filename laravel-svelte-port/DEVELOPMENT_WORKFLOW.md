# Development Workflow

## Setting Up Development Environment

### Laravel Backend

```bash
cd laravel-svelte-port/laravel
composer install
cp .env.example .env
# Edit .env with database credentials
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve # http://localhost:8000
```

### SvelteKit Frontend

```bash
cd laravel-svelte-port/svelte-ui
npm install
cp .env.example .env
# Edit .env with API URL
npm run dev # http://localhost:5173
```

## API Development Process

### 1. Create Action (Business Logic)

```php
// app/Actions/CreateAccountAction.php
class CreateAccountAction
{
    public function __construct(
        private AccountRepository $repository
    ) {}
    
    public function execute(CreateAccountData $data): Account
    {
        $account = $this->repository->create($data->toArray());
        event(new AccountCreated($account));
        return $account;
    }
}
```

### 2. Create Repository (Data Access)

```php
// app/Repositories/AccountRepository.php
class AccountRepository
{
    public function create(array $data): Account
    {
        return Account::create($data);
    }
    
    public function findWithRelations(int $id): Account
    {
        return Account::with(['users', 'inboxes'])->findOrFail($id);
    }
}
```

### 3. Create Data DTOs (Request/Response)

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

### 4. Create Controller (Thin, Delegates to Action)

```php
// app/Http/Controllers/Api/V1/AccountsController.php
class AccountsController extends Controller
{
    public function store(
        CreateAccountRequest $request,
        CreateAccountAction $action
    ): JsonResponse {
        $account = $action->execute(
            CreateAccountData::from($request->validated())
        );
        
        return response()->json(['data' => $account], 201);
    }
}
```

### 5. Add API Routes

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('accounts', AccountsController::class);
});
```

### 6. Test with Pest/PHPUnit

```php
// tests/Feature/AccountsTest.php
test('can create account', function () {
    $data = [
        'name' => 'Test Account',
        'email' => 'test@example.com',
    ];
    
    $response = $this->postJson('/api/v1/accounts', $data);
    
    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
});
```

## Frontend Development Process

### 1. Create API Client Methods

```typescript
// src/lib/api/accounts.ts
export const accountsApi = {
  async list(params?: PaginationParams) {
    return apiClient.get<AccountsListResponse>('/accounts', { params });
  },
  
  async create(data: CreateAccountData) {
    return apiClient.post<Account>('/accounts', data);
  },
  
  async get(id: number) {
    return apiClient.get<Account>(`/accounts/${id}`);
  },
};
```

### 2. Create Svelte Components

```svelte
<!-- src/lib/components/AccountCard.svelte -->
<script lang="ts">
  import type { Account } from '$lib/types';
  
  let { account }: { account: Account } = $props();
</script>

<div class="account-card">
  <h3>{account.name}</h3>
  <p>{account.email}</p>
</div>

<style>
  .account-card {
    @apply p-4 border rounded-lg;
  }
</style>
```

### 3. Create Routes

```svelte
<!-- src/routes/app/accounts/+page.svelte -->
<script lang="ts">
  import { onMount } from 'svelte';
  import { accountsApi } from '$lib/api/accounts';
  import AccountCard from '$lib/components/AccountCard.svelte';
  
  let accounts = $state<Account[]>([]);
  let loading = $state(true);
  
  onMount(async () => {
    const response = await accountsApi.list();
    accounts = response.data;
    loading = false;
  });
</script>

{#if loading}
  <p>Loading...</p>
{:else}
  {#each accounts as account}
    <AccountCard {account} />
  {/each}
{/if}
```

### 4. Implement State Management with Runes

```typescript
// src/lib/stores/accounts.svelte.ts
import { accountsApi } from '$lib/api/accounts';

class AccountsStore {
  accounts = $state<Account[]>([]);
  loading = $state(false);
  error = $state<string | null>(null);
  
  async fetchAccounts() {
    this.loading = true;
    this.error = null;
    
    try {
      const response = await accountsApi.list();
      this.accounts = response.data;
    } catch (err) {
      this.error = err.message;
    } finally {
      this.loading = false;
    }
  }
}

export const accountsStore = new AccountsStore();
```

### 5. Style with Tailwind CSS

```svelte
<div class="container mx-auto px-4 py-8">
  <h1 class="text-3xl font-bold mb-6">Accounts</h1>
  
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    {#each accounts as account}
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold">{account.name}</h2>
        <p class="text-gray-600">{account.email}</p>
      </div>
    {/each}
  </div>
</div>
```

## SPA Integration Patterns

### Asset Build & Deployment

**Development:**
```bash
# Frontend (http://localhost:5173)
cd laravel-svelte-port/svelte-ui && npm run dev

# Backend (http://localhost:8000)
cd laravel-svelte-port/laravel && php artisan serve
```

**Production:**
```bash
# Build SvelteKit SPA
cd laravel-svelte-port/svelte-ui && npm run build

# Copy to Laravel public directory
cp -r build/* ../laravel/public/app/
```

### Routing Configuration

- **API Routes**: `/api/*` → Laravel API (`routes/api.php`)
- **Auth Routes**: `/auth/*` → Laravel Auth (`routes/auth.php`)
- **SPA Routes**: `/app/*` → SvelteKit SPA (`public/app/index.html`)

### Development Proxy Setup

```javascript
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

## Running Services

### Laravel Services

```bash
# Web server
php artisan serve

# WebSocket server
php artisan reverb:start

# Queue worker
php artisan horizon

# All services (using Composer script)
composer dev
```

### SvelteKit Services

```bash
# Development server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Run tests
npm test
```

## Environment Configuration

### Laravel (.env)

```env
APP_NAME=Chatwoot
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=chatwoot
DB_USERNAME=postgres
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

### SvelteKit (.env)

```env
PUBLIC_API_URL=http://localhost:8000
PUBLIC_WS_URL=ws://localhost:8080
```

## Deployment Considerations

- Use `.env` files for both Laravel and SvelteKit
- Configure CORS for API access
- Set up proper session/cookie domains
- Configure WebSocket connections for production
- Enable caching and optimization
- Set up SSL certificates
- Configure reverse proxy (Nginx/Apache)
