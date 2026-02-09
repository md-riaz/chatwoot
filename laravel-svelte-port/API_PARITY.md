# Laravel-Rails API Parity Guidelines

**CRITICAL**: Maintaining functional parity between Rails and Laravel APIs is essential for seamless migration.

## Completed Features

### ✅ Superadmin Onboarding Feature Parity

**Components:**
1. ConfigLoaderService - YAML configuration processing
2. AccountObserver - Auto-initialize features on account creation
3. Feature Flag System - 30+ features mapped
4. Installation Commands - Configuration management
5. Onboarding Controllers - Full API endpoints
6. Database Seeders - Auto-load configurations
7. Comprehensive Tests - Full coverage

**Parity Achieved:**
- Redis flag control: `chatwoot_installation_onboarding`
- SuperAdmin creation with confirmed email
- Administrator role via AccountUser
- 20+ default features enabled
- AI features excluded (Copilot/Captain)

**Usage:**
```bash
php artisan migrate --seed
php artisan installation:initialize --enable-onboarding
php artisan test tests/Feature/Onboarding/SuperAdminOnboardingTest.php
```

### ✅ Account Seeding Feature Parity

**Components:**
1. SeedAccountJob - Async seeding
2. AccountSeederService - Complete seeding logic
3. Seed Data DTO - Demo data configuration
4. API Endpoint - `/api/v1/super_admin/accounts/{account}/seed`

**Demo Data:**
- 4 teams with emoji icons
- 6 custom roles with permissions
- 14 demo users
- 6 colored labels
- 9 channel types (Website, Facebook, Twitter, WhatsApp, SMS, Email, API, Telegram, Line)
- 9 contacts with conversation history
- 50 canned responses

### ✅ Laravel Standard Pagination

All SuperAdmin endpoints use Laravel's standard pagination format.

**Controllers Updated:**
- AccountsController
- UsersController
- AgentBotsController
- PlatformAppsController
- AccessTokensController
- AccountUsersController
- AuditController

## Laravel Standard Pagination Format (RECOMMENDED)

**Backend:**
```php
$users = User::query()
    ->with(['roles', 'accountUsers.account'])
    ->paginate($request->input('per_page', 25));

return response()->json($users);
```

**Response:**
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

**Frontend:**
```typescript
interface UsersListResponse {
  data: User[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

const response = await api.getUsers(params);
users = response.data;
totalPages = response.last_page;
totalCount = response.total;
```

## Data Transformation for Rails Parity

**User Data Structure:**
```php
$users->getCollection()->transform(function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'display_name' => $user->display_name,
        'phone_number' => $user->phone_number,
        'avatar_url' => $user->getAvatarUrl(),
        'availability' => $user->availability,
        
        // Rails compatibility
        'confirmed' => !is_null($user->email_verified_at),
        'locked' => $user->custom_attributes['locked'] ?? false,
        'role' => $user->getRoleNames()->first() ?? 'agent',
        'roles' => $user->getRoleNames()->toArray(),
        'accounts_count' => $user->accounts_count,
        'custom_attributes' => $user->custom_attributes,
        
        // ISO timestamps
        'created_at' => $user->created_at?->toISOString(),
        'updated_at' => $user->updated_at?->toISOString(),
        
        // Relationships
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

## Status Field Mapping

**Rails → Laravel:**
```php
// Confirmation
'confirmed' => !is_null($user->email_verified_at)

// Lock status
'locked' => $user->custom_attributes['locked'] ?? false

// Role
'role' => $user->getRoleNames()->first() ?? 'agent'
'roles' => $user->getRoleNames()->toArray()

// Availability
'availability' => $user->availability
```

## API Response Consistency Rules

1. **Check Rails backend first** - Examine Rails controllers/serializers
2. **Maintain Laravel conventions** - Use standard pagination, `transform()` on collections
3. **Field name consistency** - Match Rails field names exactly
4. **Timestamp format** - Always use `toISOString()`
5. **Update TypeScript interfaces** - Match Laravel pagination structure

## Rails Backend Parity Workflow

**Before implementing any Laravel endpoint:**

1. **Find Rails equivalent:**
```bash
find app/controllers -name "*users*" -type f
find app/views -name "*user*" -type f
```

2. **Analyze Rails response structure:**
- Check controller actions
- Look for `render json:` statements
- Examine jbuilder templates

3. **Identify Rails field mappings:**
```ruby
user.confirmed?     # → !is_null($user->email_verified_at)
user.locked?        # → $user->custom_attributes['locked'] ?? false
user.role           # → $user->getRoleNames()->first()
```

4. **Implement with parity:**
```php
$users->getCollection()->transform(function ($user) {
    return [
        'confirmed' => !is_null($user->email_verified_at),
        'locked' => $user->custom_attributes['locked'] ?? false,
        'accounts' => $user->accountUsers->map(...),
    ];
});
```

5. **Update TypeScript interfaces:**
```typescript
interface UsersListResponse {
  data: User[];
  current_page: number;
  last_page: number;
  total: number;
}
```

6. **Test parity:**
```bash
curl http://rails-app/api/v1/users
curl http://laravel-app/api/v1/super_admin/users
```

## Testing Parity

**Backend:**
```php
test('users index returns rails-compatible format', function () {
    $user = User::factory()->create();
    $response = $this->getJson('/api/v1/super_admin/users');
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'confirmed', 'locked', 'role']
            ],
            'current_page', 'last_page', 'total'
        ]);
});
```

**Frontend:**
```typescript
test('handles Laravel pagination format', async () => {
  const mockResponse = {
    data: [{ id: 1, name: 'Test User' }],
    current_page: 1,
    last_page: 5,
    total: 100
  };
  
  expect(component.users).toEqual(mockResponse.data);
  expect(component.totalPages).toBe(mockResponse.last_page);
});
```

## Common Parity Pitfalls

1. ❌ Don't override Laravel pagination
2. ❌ Don't ignore Rails field names
3. ❌ Don't skip relationship data
4. ❌ Don't forget enum transformations
5. ❌ Don't use different timestamp formats
6. ❌ Don't use incorrect pagination field names (`total_pages` vs `last_page`)
7. ❌ Don't mix pagination formats
