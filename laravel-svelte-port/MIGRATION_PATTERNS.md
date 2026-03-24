# Migration Patterns & Guidelines

## Backend Migration (Rails → Laravel)

### 1. Controller Pattern

**Rails**:
```ruby
class Api::V1::AccountsController < ApplicationController
  def index
    @accounts = Account.all
    render json: @accounts
  end
end
```

**Laravel**:
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

### 2. Business Logic Pattern

**Rails**:
```ruby
def create_account(params)
  account = Account.create!(params)
  NotificationService.notify_admins(account)
  account
end
```

**Laravel**:
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

### 3. Data Transfer Objects

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

### 4. Repository Pattern

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

## Frontend Migration (Vue → SvelteKit)

### 1. Component Structure

**Vue**:
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

**SvelteKit**:
```svelte
<script>
  let { account } = $props();
</script>

<div class="account-card">
  <h3>{account.name}</h3>
  <p>{account.email}</p>
</div>
```

### 2. State Management

**Vue (Vuex/Pinia)**:
```js
const store = useAccountStore()
store.fetchAccounts()
```

**SvelteKit (Runes)**:
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

### 3. API Integration

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

### 4. Automatic API Data Transformation

**CRITICAL**: Automatic case conversion between frontend and backend.

**Frontend (camelCase)**:
```typescript
interface Account {
  usersCount: number;
  inboxesCount: number;
}

let account = $state({
  usersCount: 0,
  inboxesCount: 0
});
```

**Backend (snake_case)**:
```php
{
  "users_count": 42,
  "inboxes_count": 5
}
```

**Transformation** (`src/lib/api/transformers.ts`):
- Outgoing: `usersCount` → `users_count`
- Incoming: `users_count` → `usersCount`

**Rules**:
1. Always use camelCase in frontend code
2. Never manually convert case
3. Backend uses snake_case
4. Transformation is automatic and transparent

### 5. OAuth Channel Handoff Pattern

For provider-backed inbox channels such as Facebook, do not push provider OAuth callback handling into the SPA.

**Preferred Laravel pattern:**
1. SPA calls an authenticated Laravel endpoint to start authorization
2. Laravel generates provider redirect URL and stores short-lived state server-side
3. Provider redirects back to a Laravel web callback route
4. Laravel exchanges the authorization code for provider tokens
5. Laravel redirects back to the SPA with a one-time handoff key
6. SPA redeems that handoff key through an authenticated API endpoint
7. SPA uses the returned token only for the immediate next parity step, such as page discovery

**Why this pattern is preferred:**
- Keeps provider secrets and code exchange on the backend
- Preserves SPA routing while staying idiomatic for Laravel
- Avoids long-lived provider tokens in browser URLs
- Maps cleanly to Rails parity flows where backend-owned auth is expected

**Facebook implementation reference:**
- Backend callback: `GET /auth/facebook/callback`
- SPA token redemption: `GET /api/v1/accounts/{account}/callbacks/facebook/token`
- Page discovery: `GET /api/v1/accounts/{account}/channels/facebook/pages`

## Key Migration Considerations

### 1. Authentication
- Rails: Devise + JWT
- Laravel: Sanctum + custom auth
- Frontend: httpOnly cookies

### 2. WebSocket
- Rails: ActionCable
- Laravel: Laravel Reverb
- Frontend: Native WebSocket API

### 3. File Uploads & Avatars
- Rails: Active Storage
- Laravel: Spatie Media Library
- Frontend: FormData with progress

**Laravel Implementation**:
```php
class User extends Model implements HasMedia
{
    use HasAvatar;
}

$user->uploadAvatar($file);
$url = $user->getAvatarUrl('medium');
$user->deleteAvatar();
```

### 4. Background Jobs
- Rails: Sidekiq
- Laravel: Horizon + Redis
- Queue: email, notifications, etc.

### 5. Database Migrations
- Keep PostgreSQL schema
- Laravel migrations match Rails
- Maintain data integrity

## Code Quality Standards

### Backend (Laravel)
- PSR-12 coding standards
- PHP 8.2+ features
- Pest/PHPUnit tests
- Laravel validation
- Proper error handling

### Frontend (SvelteKit)
- TypeScript for type safety
- Svelte 5 runes patterns
- Error boundaries
- Tailwind CSS
- Vitest tests

## Testing Strategy

### Backend
```php
test('can list accounts', function () {
    Account::factory()->count(3)->create();
    $response = $this->getJson('/api/v1/accounts');
    $response->assertOk()->assertJsonCount(3, 'data');
});
```

### Frontend
```js
import { render } from '@testing-library/svelte';
import AccountCard from './AccountCard.svelte';

test('renders account information', () => {
  const account = { name: 'Test', email: 'test@example.com' };
  const { getByText } = render(AccountCard, { account });
  expect(getByText('Test')).toBeInTheDocument();
});
```
