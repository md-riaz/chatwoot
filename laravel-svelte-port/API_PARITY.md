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

### ✅ Facebook Channel OAuth And Inbox Creation Parity

The Laravel and Svelte implementations now share a concrete Facebook channel contract for the inbox creation flow.

**Implemented endpoints:**
- `GET /api/v1/accounts/{account}/callbacks/facebook/initiateAuthorization`
- `GET /auth/facebook/callback`
- `GET /api/v1/accounts/{account}/callbacks/facebook/token?token_key=...`
- `GET /api/v1/accounts/{account}/channels/facebook/pages?user_access_token=...`
- `POST /api/v1/accounts/{account}/channels/facebook`

**Flow:**
1. Svelte requests `initiateAuthorization`
2. Laravel stores short-lived OAuth state and returns `authorization_url`
3. Browser is redirected to Facebook
4. Facebook redirects back to Laravel `GET /auth/facebook/callback`
5. Laravel exchanges the code server-side and redirects to the Svelte inbox page with a one-time `token_key`
6. Svelte redeems `token_key` through the authenticated API
7. Svelte requests page discovery with the returned `user_access_token`
8. Svelte creates the Facebook inbox through the dedicated channel endpoint

**Active flow only:**
- There is no manual token-entry fallback
- There is no callback-side inbox creation endpoint
- Facebook inbox creation is only supported through:
  - OAuth start
  - one-time token redemption
  - page discovery
  - `POST /api/v1/accounts/{account}/channels/facebook`

**Page listing response shape:**
```json
{
  "data": [
    {
      "id": "123456789",
      "name": "Acme Support",
      "page_access_token": "page_token",
      "user_access_token": "user_token",
      "instagram_id": "ig_123",
      "exists": false
    }
  ]
}
```

**Create inbox request shape:**
```json
{
  "name": "Facebook Page",
  "page_id": "123456789",
  "page_access_token": "page_token",
  "user_access_token": "user_token"
}
```

**Create inbox response shape:**
- Uses standard `InboxResource`
- `channel_type` is `Channel::FacebookPage`

**Parity notes:**
- OAuth code exchange is server-side in Laravel
- The SPA never needs the raw Facebook code
- Token handoff from Laravel to Svelte is one-time and short-lived
- Existing pages are flagged with `exists: true` so the UI can avoid duplicate inbox creation
- Facebook channel records remain stored in `channel_facebook_pages`
- `user_access_token` is required for inbox creation to keep the Facebook flow consistent with Rails/Vue channel setup

### ✅ Instagram OAuth Callback Inbox Creation Parity

Instagram now follows a single callback-driven flow closer to Rails:

**Implemented endpoints:**
- `GET /api/v1/accounts/{account}/channels/instagram/initiateAuthorization`
- `GET /auth/instagram/callback`
- `PATCH /api/v1/accounts/{account}/channels/instagram/{inbox}`

**Flow:**
1. Svelte requests `channels/instagram/initiateAuthorization`
2. Laravel returns a provider authorization URL
3. Browser redirects to Instagram
4. Instagram redirects back to Laravel callback
5. Laravel exchanges the code, fetches user details, and either creates or refreshes the Instagram channel/inbox
6. Laravel redirects directly to:
   - add-agents for new inboxes
   - inbox configuration for already-connected inboxes

**Parity notes:**
- There is no separate API create step from the SPA
- Inbox creation happens in the callback, matching the Rails callback-driven behavior
- `channel_type` is stored as `Channel::Instagram`

### ✅ WhatsApp Cloud Inbox Creation Alignment

The Laravel and Svelte apps now use a dedicated channel endpoint for WhatsApp Cloud inbox creation instead of the generic inbox wizard payload.

**Implemented endpoint:**
- `POST /api/v1/accounts/{account}/channels/whatsapp`

**Active supported flow:**
1. Svelte routes WhatsApp selection to a dedicated page
2. User submits WhatsApp Cloud credentials
3. Laravel creates `channel_whatsapp`
4. Laravel creates the inbox with `channel_type` = `Channel::Whatsapp`
5. Svelte continues to the add-agents step

**Request shape:**
```json
{
  "name": "WhatsApp Support",
  "phone_number": "+15551234567",
  "provider": "whatsapp_cloud",
  "provider_config": {
    "phone_number_id": "106540352242922",
    "business_account_id": "192837465564738",
    "api_key": "EAAG..."
  }
}
```

**Parity notes:**
- The active WhatsApp path is currently WhatsApp Cloud only
- Legacy mixed-provider wizard behavior is not part of the supported contract
- The backend response now uses standard inbox resource semantics rather than nested custom payloads

### ✅ Email Channel Route Surface Alignment

Email inboxes now use the dedicated Laravel email channel endpoint and expose route-backed IMAP and SMTP settings pages in Svelte.

**Implemented endpoints:**
- `POST /api/v1/accounts/{account}/channels/email`
- `PATCH /api/v1/accounts/{account}/channels/email/{inbox}`

**Implemented Svelte routes:**
- `/settings/inboxes/new/email`
- `/settings/inboxes/{id}/imap`
- `/settings/inboxes/{id}/smtp`

**Parity notes:**
- Email inbox creation no longer depends on the generic inbox endpoint
- IMAP and SMTP now have distinct route-backed settings pages similar to the Vue route surface
- `channel_type` is stored as `Channel::Email`

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
6. **Document OAuth channel handoff flows** - Redirect endpoints and SPA token redemption must be captured in parity docs, not only in code

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

## Web Widget Inbox Route Surface Alignment

The website live-chat inbox now uses the dedicated Laravel channel endpoint instead of the generic inbox create path.

Implemented Laravel endpoints:

```text
POST  /api/v1/accounts/:account_id/channels/web_widget
PATCH /api/v1/accounts/:account_id/channels/web_widget/:inbox_id
GET   /api/v1/accounts/:account_id/channels/web_widget/:inbox_id/script
PATCH /api/v1/accounts/:account_id/inboxes/:inbox_id/working_hours
```

Implemented Svelte routes:

```text
/app/accounts/:accountId/settings/inboxes/new/website
/app/accounts/:accountId/settings/inboxes/:id/business-hours
/app/accounts/:accountId/settings/inboxes/:id/csat
/app/accounts/:accountId/settings/inboxes/:id/pre-chat-form
/app/accounts/:accountId/settings/inboxes/:id/widget-builder
```

Current parity notes:

- Website inbox creation now creates a real `Channel::WebWidget` record plus the inbox in one Laravel request.
- Inbox `show` and generic `update` responses now load the polymorphic `channel` relation so Svelte settings pages can render channel-specific values without fallback assumptions.
- Widget builder uses the dedicated web-widget channel update endpoint for widget-specific fields such as `website_url`, `widget_color`, `welcome_title`, `welcome_tagline`, `allowed_domains`, `hmac_mandatory`, and `pre_chat_form_*`.
- Business hours and CSAT remain inbox-level settings and continue through the inbox/working-hours endpoints.
- The embed script shown in Svelte is sourced from the Laravel `script` endpoint, not constructed in the frontend.

Focused backend coverage:

```text
tests/Feature/Api/Channels/WebWidgetChannelParityTest.php
tests/Feature/Api/Channels/EmailChannelParityTest.php
```

## Channel Onboarding Alignment

Current onboarding paths that are fully route-backed in Svelte and supported by Laravel:

```text
Website live chat  -> create website inbox -> add agents -> finish with widget script + widget builder entry
Facebook Page      -> server-side OAuth -> page selection -> add agents -> finish with connected page details
WhatsApp Cloud     -> create cloud inbox -> add agents -> finish with webhook/token details + template sync
```

Implemented contract notes:

- Website live chat onboarding is now single-path and uses `POST /api/v1/accounts/:account_id/channels/web_widget`.
- Facebook onboarding is now single-path and uses the Laravel callback/token handoff plus `GET /channels/facebook/pages` and `POST /channels/facebook`.
- WhatsApp onboarding is now explicitly Cloud-only and uses `POST /api/v1/accounts/:account_id/channels/whatsapp`.
- The finish step in Svelte is channel-aware and surfaces the immediate setup artifacts the operator needs next instead of a generic success state.
