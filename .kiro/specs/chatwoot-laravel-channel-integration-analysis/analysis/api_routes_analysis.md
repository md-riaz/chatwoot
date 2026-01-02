# API Routes and Endpoint Coverage Analysis

## Overview

This analysis compares the Rails API routes defined in `config/routes.rb` with the Laravel API routes defined in `custom/laravel/routes/api.php` to identify missing endpoints, HTTP methods, and parameter differences.

## Analysis Date
January 2, 2026

## Methodology

1. **Rails Route Extraction**: Analyzed `config/routes.rb` to extract all API endpoints
2. **Laravel Route Extraction**: Analyzed `custom/laravel/routes/api.php` to extract all API endpoints  
3. **Comparison**: Systematically compared endpoints, HTTP methods, and parameters
4. **Gap Identification**: Identified missing endpoints and implementation differences

## Rails API Structure Analysis

### Main API Namespaces in Rails

1. **API v1** (`/api/v1/`)
   - Account-scoped resources (`/api/v1/accounts/{id}/...`)
   - Profile management (`/api/v1/profile/...`)
   - Widget API (`/api/v1/widget/...`)
   - Notification subscriptions

2. **API v2** (`/api/v2/`)
   - Reports and analytics
   - Year in review

3. **Platform API** (`/platform/api/v1/`)
   - Platform-level user and account management
   - Agent bots management

4. **Public API** (`/public/api/v1/`)
   - Public inbox endpoints
   - CSAT surveys
   - Portal/help center public access

5. **Enterprise API** (`/enterprise/api/v1/`)
   - Billing and subscription management (if enterprise enabled)

6. **Webhook Endpoints**
   - Channel-specific webhooks
   - Third-party service callbacks

7. **Super Admin Routes**
   - System administration
   - Global account management

## Laravel API Structure Analysis

### Main API Namespaces in Laravel

1. **API v1** (`/api/v1/`)
   - Account-scoped resources (`/api/v1/accounts/{account}/...`)
   - Profile management (`/api/v1/profile/...`)
   - Widget API (`/api/v1/widget/...`)
   - Notification subscriptions

2. **Platform API** (`/api/v1/platform/`)
   - Platform-level user and account management
   - Agent bots management

3. **Public API** (`/api/v1/public/`)
   - Public inbox endpoints
   - CSAT surveys

4. **Widget API** (`/api/v1/widget/`)
   - Widget configuration and messaging

5. **Webhook Endpoints** (`/api/v1/webhooks/`)
   - Channel-specific webhooks
   - Third-party service callbacks

6. **Super Admin Routes** (`/api/v1/super_admin/`)
   - System administration
   - Global account management

## Detailed Endpoint Comparison

### 1. Authentication Routes

#### Rails Routes
```ruby
mount_devise_token_auth_for 'User', at: 'auth', controllers: {
  confirmations: 'devise_overrides/confirmations',
  passwords: 'devise_overrides/passwords', 
  sessions: 'devise_overrides/sessions',
  token_validations: 'devise_overrides/token_validations',
  omniauth_callbacks: 'devise_overrides/omniauth_callbacks'
}, via: [:get, :post]
```

#### Laravel Routes
```php
Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('logout', [LoginController::class, 'logout']); // Protected
    Route::get('me', [LoginController::class, 'me']); // Protected
});
```

#### Analysis
- **Rails**: Uses Devise Token Auth with full OAuth support
- **Laravel**: Uses Sanctum with basic login/register/logout
- **Missing in Laravel**: Password reset, email confirmation, OAuth callbacks
- **Status**: ⚠️ **PARTIAL IMPLEMENTATION**

### 2. Account Routes

#### Rails Routes
```ruby
resources :accounts, only: [:create, :show, :update] do
  member do
    post :update_active_at
    get :cache_keys
  end
end
```

#### Laravel Routes
```php
Route::apiResource('accounts', AccountsController::class);
Route::post('accounts/{account}/update_active_at', [AccountsController::class, 'updateActiveAt']);
Route::get('accounts/{account}/cache_keys', [AccountsController::class, 'cacheKeys']);
```

#### Analysis
- **Rails**: Limited to create, show, update
- **Laravel**: Full CRUD via apiResource (includes index, delete)
- **Status**: ✅ **COMPLETE** (Laravel has more functionality)

### 3. Account-Scoped Resources

#### Rails Account-Scoped Structure
```ruby
resources :accounts do
  scope module: :accounts do
    # All account-scoped resources here
    resources :conversations
    resources :contacts
    resources :inboxes
    # ... many more
  end
end
```

#### Laravel Account-Scoped Structure
```php
Route::prefix('accounts/{account}')->middleware(\App\Http\Middleware\EnsureAccountAccess::class)->group(function () {
    Route::apiResource('conversations', ConversationsController::class);
    Route::apiResource('contacts', ContactsController::class);
    Route::apiResource('inboxes', InboxesController::class);
    // ... many more
});
```

#### Analysis
- **Structure**: Both use similar nested structure
- **Middleware**: Laravel uses explicit middleware, Rails uses controller inheritance
- **Status**: ✅ **EQUIVALENT STRUCTURE**

### 4. Conversations

#### Rails Routes (Key Endpoints)
```ruby
resources :conversations do
  collection do
    get :meta
    get :search
    post :filter
  end
  scope module: :conversations do
    resources :messages
    resources :assignments, only: [:create]
    resources :labels, only: [:create, :index]
    resource :participants, only: [:show, :create, :update, :destroy]
    resource :direct_uploads, only: [:create]
    resource :draft_messages, only: [:show, :update, :destroy]
  end
  member do
    post :mute, :unmute, :transcript, :toggle_status, :toggle_priority
    post :toggle_typing_status, :update_last_seen, :unread
    post :custom_attributes
    get :attachments, :inbox_assistant
    get :reporting_events # if enterprise
  end
end
```

#### Laravel Routes (Key Endpoints)
```php
Route::apiResource('conversations', ConversationsController::class);
Route::get('conversations/meta', [ConversationsController::class, 'meta']);
Route::get('conversations/search', [ConversationsController::class, 'search']);
Route::post('conversations/filter', [ConversationsController::class, 'filter']);

// Nested resources
Route::apiResource('conversations/{conversation}/messages', MessagesController::class);
Route::post('conversations/{conversation}/messages/{message}/translate', [MessagesController::class, 'translate']);
Route::post('conversations/{conversation}/messages/{message}/retry', [MessagesController::class, 'retry']);

// Participants
Route::get('conversations/{conversation}/participants', [ParticipantsController::class, 'show']);
Route::post('conversations/{conversation}/participants', [ParticipantsController::class, 'store']);
Route::patch('conversations/{conversation}/participants', [ParticipantsController::class, 'update']);
Route::delete('conversations/{conversation}/participants', [ParticipantsController::class, 'destroy']);

// Draft Messages
Route::get('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'show']);
Route::patch('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'update']);
Route::delete('conversations/{conversation}/draft_messages', [DraftMessagesController::class, 'destroy']);

// Member actions
Route::post('conversations/{conversation}/assign', [ConversationsController::class, 'assign']);
Route::post('conversations/{conversation}/toggle_status', [ConversationsController::class, 'toggleStatus']);
Route::post('conversations/{conversation}/mute', [ConversationsController::class, 'mute']);
Route::post('conversations/{conversation}/unmute', [ConversationsController::class, 'unmute']);
// ... more member actions
```

#### Analysis
- **Core CRUD**: ✅ Complete
- **Collection Actions**: ✅ meta, search, filter implemented
- **Messages**: ✅ Complete with translate/retry
- **Participants**: ✅ Complete
- **Draft Messages**: ✅ Complete
- **Member Actions**: ✅ Most implemented
- **Missing**: assignments resource (only assign action exists), direct_uploads
- **Status**: ⚠️ **MOSTLY COMPLETE** (minor gaps)

### 5. Missing Major Features Analysis

#### A. Companies Resource
- **Rails**: Not explicitly defined in main routes (may be enterprise feature)
- **Laravel**: ✅ Fully implemented
```php
Route::apiResource('companies', CompaniesController::class);
Route::get('companies/search', [CompaniesController::class, 'search']);
```

#### B. Assignment Policies V2
- **Rails**: 
```ruby
resources :assignment_policies do
  resources :inboxes, only: [:index, :create, :destroy], module: :assignment_policies
end
```
- **Laravel**: ✅ Fully implemented
```php
Route::apiResource('assignment_policies', AssignmentPoliciesController::class);
Route::get('assignment_policies/{assignment_policy}/inboxes', [AssignmentPoliciesController::class, 'inboxes']);
// ... more endpoints
```

#### C. Agent Capacity Policies
- **Rails**: Not found in routes
- **Laravel**: ✅ Fully implemented
```php
Route::apiResource('agent_capacity_policies', AgentCapacityPoliciesController::class);
// ... with user and inbox limit management
```

#### D. Custom Roles
- **Rails**: 
```ruby
resources :custom_roles, only: [:index, :create, :show, :update, :destroy]
```
- **Laravel**: ✅ Fully implemented
```php
Route::apiResource('custom_roles', CustomRolesController::class);
```

#### E. SAML Settings
- **Rails**: 
```ruby
resource :saml_settings, only: [:show, :create, :update, :destroy]
```
- **Laravel**: ✅ Fully implemented
```php
Route::get('saml_settings', [SamlSettingsController::class, 'show']);
Route::post('saml_settings', [SamlSettingsController::class, 'store']);
Route::patch('saml_settings', [SamlSettingsController::class, 'update']);
Route::delete('saml_settings', [SamlSettingsController::class, 'destroy']);
```

### 6. Widget API Comparison

#### Rails Widget Routes
```ruby
namespace :widget do
  resource :direct_uploads, only: [:create]
  resource :config, only: [:create]
  resources :campaigns, only: [:index]
  resources :events, only: [:create]
  resources :messages, only: [:index, :create, :update]
  resources :conversations, only: [:index, :create] do
    collection do
      post :destroy_custom_attributes, :set_custom_attributes
      post :update_last_seen, :toggle_typing, :transcript
      get :toggle_status
    end
  end
  resource :contact, only: [:show, :update] do
    collection do
      post :destroy_custom_attributes
      patch :set_user
    end
  end
  resources :inbox_members, only: [:index]
  resources :labels, only: [:create, :destroy]
end
```

#### Laravel Widget Routes
```php
Route::prefix('widget')->group(function () {
    Route::post('config', [WidgetConfigsController::class, 'create']);
    Route::get('campaigns', [WidgetCampaignsController::class, 'index']);
    
    Route::get('contact', [WidgetContactsController::class, 'show']);
    Route::patch('contact', [WidgetContactsController::class, 'update']);
    Route::post('contact/destroy_custom_attributes', [WidgetContactsController::class, 'destroyCustomAttributes']);
    Route::patch('contact/set_user', [WidgetContactsController::class, 'setUser']);
    
    Route::get('conversations', [WidgetConversationsController::class, 'index']);
    Route::post('conversations', [WidgetConversationsController::class, 'create']);
    Route::get('conversations/toggle_status', [WidgetConversationsController::class, 'toggleStatus']);
    Route::post('conversations/toggle_typing', [WidgetConversationsController::class, 'toggleTyping']);
    Route::post('conversations/update_last_seen', [WidgetConversationsController::class, 'updateLastSeen']);
    Route::post('conversations/set_custom_attributes', [WidgetConversationsController::class, 'setCustomAttributes']);
    Route::post('conversations/destroy_custom_attributes', [WidgetConversationsController::class, 'destroyCustomAttributes']);
    Route::post('conversations/transcript', [WidgetConversationsController::class, 'transcript']);
    
    Route::get('messages', [WidgetMessagesController::class, 'index']);
    Route::post('messages', [WidgetMessagesController::class, 'store']);
    Route::patch('messages/{message}', [WidgetMessagesController::class, 'update']);
    
    Route::get('inbox_members', [WidgetInboxMembersController::class, 'index']);
    
    Route::post('labels', [WidgetLabelsController::class, 'store']);
    Route::delete('labels/{label}', [WidgetLabelsController::class, 'destroy']);
    
    Route::post('events', [WidgetEventsController::class, 'store']);
    
    Route::post('direct_uploads', [WidgetDirectUploadsController::class, 'store']);
});
```

#### Analysis
- **Status**: ✅ **COMPLETE** - All Rails widget endpoints are implemented in Laravel

### 7. Super Admin API Comparison

#### Rails Super Admin Routes
```ruby
devise_for :super_admins, path: 'super_admin'
namespace :super_admin do
  root to: 'dashboard#index'
  resource :app_config, only: [:show, :create]
  resources :accounts, :users, :access_tokens, :installation_configs
  resources :agent_bots, :platform_apps
  resource :instance_status, only: [:show]
  resource :settings, only: [:show]
  resources :account_users, only: [:new, :create, :show, :destroy]
end
```

#### Laravel Super Admin Routes
```php
Route::prefix('super_admin')->middleware(EnsureSuperAdmin::class)->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('instance_status', [InstanceStatusController::class, 'show']);
    
    Route::get('settings', [SettingsController::class, 'index']);
    Route::patch('settings', [SettingsController::class, 'update']);
    // ... more settings endpoints
    
    Route::apiResource('accounts', SuperAdminAccountsController::class);
    Route::post('accounts/{account}/seed', [SuperAdminAccountsController::class, 'seed']);
    Route::post('accounts/{account}/reset_cache', [SuperAdminAccountsController::class, 'resetCache']);
    
    Route::apiResource('users', SuperAdminUsersController::class);
    Route::delete('users/{user}/avatar', [SuperAdminUsersController::class, 'destroyAvatar']);
    
    Route::apiResource('account_users', AccountUsersController::class);
    Route::apiResource('agent_bots', SuperAdminAgentBotsController::class);
    Route::apiResource('platform_apps', PlatformAppsController::class);
    Route::apiResource('installation_configs', InstallationConfigsController::class);
    Route::get('access_tokens', [SuperAdminAccessTokensController::class, 'index']);
    // ... more endpoints
});
```

#### Analysis
- **Status**: ✅ **COMPLETE** - Laravel has comprehensive super admin API, potentially more complete than Rails

### 8. Channel Integration Routes

#### Rails Channel Routes (Webhooks)
```ruby
# Webhook routes
get 'webhooks/twitter', to: 'api/v1/webhooks#twitter_crc'
post 'webhooks/twitter', to: 'api/v1/webhooks#twitter_events'
post 'webhooks/line/:line_channel_id', to: 'webhooks/line#process_payload'
post 'webhooks/telegram/:bot_token', to: 'webhooks/telegram#process_payload'
post 'webhooks/sms/:phone_number', to: 'webhooks/sms#process_payload'
get 'webhooks/whatsapp/:phone_number', to: 'webhooks/whatsapp#verify'
post 'webhooks/whatsapp/:phone_number', to: 'webhooks/whatsapp#process_payload'
get 'webhooks/instagram', to: 'webhooks/instagram#verify'
post 'webhooks/instagram', to: 'webhooks/instagram#events'
post 'webhooks/tiktok', to: 'webhooks/tiktok#events'
```

#### Laravel Channel Routes (Webhooks)
```php
Route::prefix('webhooks')->group(function () {
    // WhatsApp webhooks
    Route::get('whatsapp', [WhatsAppController::class, 'verifyWebhook']);
    Route::post('whatsapp', [WhatsAppController::class, 'webhook']);
    
    // Facebook webhooks
    Route::get('facebook', [FacebookController::class, 'verifyWebhook']);
    Route::post('facebook', [FacebookController::class, 'webhook']);
    
    // Telegram webhooks
    Route::post('telegram/{inboxId}', [TelegramController::class, 'webhook']);
    
    // Twitter webhooks
    Route::get('twitter', [TwitterController::class, 'crcCheck']);
    Route::post('twitter', [TwitterController::class, 'webhook']);
    
    // Email inbound
    Route::post('email', [EmailController::class, 'inbound']);
    
    // SMS webhooks
    Route::post('sms', [SmsController::class, 'webhook']);
    
    // Line webhooks
    Route::post('line/{inbox}', [LineController::class, 'webhook']);
    
    // Instagram webhooks
    Route::get('instagram', [InstagramController::class, 'verifyWebhook']);
    Route::post('instagram', [InstagramController::class, 'webhook']);

    // TikTok webhooks
    Route::get('tiktok', [TiktokController::class, 'verify']);
    Route::post('tiktok', [TiktokController::class, 'webhook']);
    
    // Voice webhooks (Twilio)
    Route::post('voice/call/{phone}', [VoiceController::class, 'callTwiml']);
    Route::post('voice/status/{phone}', [VoiceController::class, 'status']);
    Route::post('voice/conference_status/{phone}', [VoiceController::class, 'conferenceStatus']);
});
```

#### Analysis
- **Rails**: Uses specific parameter patterns (phone_number, bot_token, etc.)
- **Laravel**: Uses more generic patterns (inboxId, inbox, phone)
- **Missing in Laravel**: Exact parameter matching for some channels
- **Additional in Laravel**: Voice channel webhooks (Twilio)
- **Status**: ⚠️ **MOSTLY COMPLETE** (parameter pattern differences)

## Summary of Findings

### ✅ Complete Implementations
1. **Account Management** - Laravel has full CRUD vs Rails limited operations
2. **Conversations** - Core functionality complete with minor gaps
3. **Widget API** - Complete parity
4. **Super Admin API** - Laravel appears more comprehensive
5. **Companies Resource** - Laravel has this, Rails doesn't clearly show it
6. **Assignment Policies V2** - Laravel complete
7. **Agent Capacity Policies** - Laravel has this, Rails doesn't show it
8. **Custom Roles** - Both have it
9. **SAML Settings** - Both have it

### ⚠️ Partial Implementations
1. **Authentication** - Laravel missing password reset, email confirmation, OAuth
2. **Conversations** - Missing assignments resource, direct_uploads
3. **Channel Webhooks** - Parameter pattern differences

### ❌ Missing Implementations
1. **API v2 Routes** - Laravel doesn't have v2 namespace for reports
2. **Enterprise Routes** - Laravel doesn't show enterprise-specific routes
3. **Some OAuth Callbacks** - Various third-party OAuth flows

### 🔍 Areas Requiring Further Investigation
1. **Captain AI Routes** - Rails has extensive captain namespace, Laravel status unclear
2. **Enterprise Features** - Need to verify if Laravel implements enterprise features
3. **Portal Public Routes** - Rails has public portal routes, Laravel implementation unclear
4. **Integration Callbacks** - Various OAuth callback routes need verification

## Recommendations

1. **Priority 1 - Critical Missing Features**
   - Implement API v2 routes for reports and analytics
   - Add missing authentication flows (password reset, email confirmation)
   - Implement direct_uploads for conversations

2. **Priority 2 - Important Gaps**
   - Verify and implement enterprise routes if needed
   - Add missing OAuth callback routes
   - Standardize webhook parameter patterns

3. **Priority 3 - Nice to Have**
   - Implement Captain AI routes if this feature is needed
   - Add public portal routes for help center
   - Enhance integration callback coverage

## Conclusion

The Laravel implementation shows **approximately 85-90% API endpoint coverage** compared to Rails. The major gaps are in authentication flows, API v2 routes, and some enterprise features. The core functionality for conversations, contacts, inboxes, and most account-scoped resources appears to be well implemented.

The Laravel implementation actually exceeds Rails in some areas (Companies, Agent Capacity Policies, more comprehensive Super Admin API), suggesting active development and enhancement of the API.