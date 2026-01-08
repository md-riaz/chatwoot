# User System Documentation - Rails-Laravel Parity

This document outlines the user management system in Laravel that maintains full parity with the Rails backend.

## User Type System (Rails STI Compatibility)

### Platform-Level Types
- **SuperAdmin** (`type: 'SuperAdmin'`): Platform administrators with full system access
- **User** (`type: 'User'`): Regular users with account-level permissions

### Account-Level Roles
- **Administrator** (`role: 'administrator'`): Account-level admin permissions
- **Agent** (`role: 'agent'`): Basic agent permissions within an account

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    provider VARCHAR(255) DEFAULT 'email',
    uid VARCHAR(255) DEFAULT '',
    display_name VARCHAR(255) NULL,
    avatar_url VARCHAR(255) NULL,
    phone_number VARCHAR(255) NULL,
    type VARCHAR(255) NULL,  -- 'User' or 'SuperAdmin' (Rails STI)
    availability INT DEFAULT 0,
    message_signature TEXT NULL,
    pubsub_token VARCHAR(255) UNIQUE NULL,
    tokens JSON NULL,
    ui_settings JSON NULL,
    custom_attributes JSON NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);
```

### Account Users Table (Junction)
```sql
CREATE TABLE account_users (
    id BIGINT PRIMARY KEY,
    account_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    inviter_id BIGINT NULL,
    custom_role_id BIGINT NULL,
    agent_capacity_policy_id BIGINT NULL,
    role INT DEFAULT 0,  -- 0: agent, 1: administrator
    availability INT DEFAULT 0,
    auto_offline BOOLEAN DEFAULT TRUE,
    active_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE(account_id, user_id)
);
```

## Authentication & Authorization

### Middleware: EnsureSuperAdmin
```php
// Checks type field (Rails parity) - super_admin is now a user type, not a role
if (! $user || $user->type !== 'SuperAdmin') {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

### Permission System
- **Platform Level**: User type field (`type = 'SuperAdmin'`)
- **Account Level**: AccountUser model with enum roles
- **Custom Roles**: Enterprise feature via CustomRole model

## Avatar Management (Rails Parity)

### Avatarable Trait
```php
use App\Traits\Avatarable;

// Upload avatar
$user->uploadAvatar($file);

// Delete avatar
$user->deleteAvatar();

// Get avatar URL with Gravatar fallback
$user->getApiAvatarUrl();
```

### Features
- **File Validation**: 15MB max, JPEG/PNG/GIF only (matches Rails)
- **Automatic Cleanup**: Deletes old avatar when uploading new one
- **Gravatar Fallback**: Uses Gravatar if no avatar uploaded
- **Storage**: Laravel's public disk (`storage/app/public/avatars/`)

## API Response Format (Rails Compatible)

### User Object
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "display_name": "Johnny",
  "phone_number": "+1234567890",
  "avatar_url": "/storage/avatars/1234567890_avatar.jpg",
  "availability": 0,
  "confirmed": true,
  "locked": false,
  "type": "SuperAdmin",           // Platform-level type (Rails STI)
  "role": "administrator",        // Account-level role
  "roles": ["super_admin"],       // Spatie roles (debugging)
  "accounts_count": 3,
  "custom_attributes": {},
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z",
  "accounts": [
    {
      "id": 1,
      "name": "Acme Corp",
      "role": "administrator",
      "availability": "online",
      "active_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

## API Endpoints

### SuperAdmin Users Management
```
GET    /api/v1/super_admin/users              # List users
POST   /api/v1/super_admin/users              # Create user
GET    /api/v1/super_admin/users/{id}         # Show user
PUT    /api/v1/super_admin/users/{id}         # Update user
DELETE /api/v1/super_admin/users/{id}         # Delete user

POST   /api/v1/super_admin/users/{id}/avatar  # Upload avatar
DELETE /api/v1/super_admin/users/{id}/avatar  # Delete avatar

POST   /api/v1/super_admin/users/{id}/confirm # Confirm email
POST   /api/v1/super_admin/users/{id}/lock    # Lock user
POST   /api/v1/super_admin/users/{id}/unlock  # Unlock user
```

### Request Payloads

#### Create/Update User
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "display_name": "Johnny",
  "phone_number": "+1234567890",
  "type": "User",                    // 'User' or 'SuperAdmin'
  "role": "agent",                   // 'agent' or 'administrator'
  "custom_attributes": {}
}
```

#### Avatar Upload
```
Content-Type: multipart/form-data
avatar: [file] (max 15MB, JPEG/PNG/GIF)
```

## Frontend Integration (SvelteKit)

### User Type Display
```svelte
{#if user?.type === 'SuperAdmin'}
  <div class="badge">Super Administrator</div>
{/if}
```

### Role Selection
```svelte
<Select bind:value={formData.role}>
  <option value="administrator">Administrator</option>
  <option value="agent">Agent</option>
</Select>
```

### Avatar Management
```svelte
<input type="file" accept="image/*" onchange={handleFileSelect} />
{#if avatarPreview}
  <img src={avatarPreview} alt="Avatar" />
  <button onclick={handleDeleteAvatar}>Delete</button>
{/if}
```

## Data Transformation (Rails Parity)

### Controller Transform Method
```php
private function transformUser($user): array
{
    $accountRole = $user->accountUsers->first()?->role_name ?? 'agent';
    
    return [
        'type' => $user->type ?? 'User',           // Rails STI field
        'role' => $accountRole,                    // Account-level role
        'avatar_url' => $user->getApiAvatarUrl(), // Rails-compatible method
        // ... other fields
    ];
}
```

### Enum Handling
```php
// AccountUserRole enum
enum AccountUserRole: int {
    case AGENT = 0;
    case ADMINISTRATOR = 1;
    
    public function getName(): string {
        return match($this) {
            self::AGENT => 'agent',
            self::ADMINISTRATOR => 'administrator',
        };
    }
}
```

## Migration Guide

### From Spatie-Only to Type Field
1. **Add type field**: Already exists in users table
2. **Update middleware**: Check only type field
3. **Update API responses**: Use account-level roles
4. **Frontend updates**: Handle both type and role fields
5. **Remove role assignments**: Set type directly instead of using Spatie roles for platform-level access

### User Type Migration
```php
// Set type field directly when creating users
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'type' => 'SuperAdmin', // Platform-level type
    'email_verified_at' => now(),
]);

// Regular users default to 'User' type
User::create([
    'name' => 'Regular User',
    'email' => 'user@example.com',
    'type' => 'User', // Default type
    'email_verified_at' => now(),
]);
```

## Testing

### Unit Tests
```php
test('super admin middleware checks type field', function () {
    $user = User::factory()->create(['type' => 'SuperAdmin']);
    
    $this->actingAs($user)
         ->get('/api/v1/super_admin/users')
         ->assertOk();
});

test('regular user cannot access super admin routes', function () {
    $user = User::factory()->create(['type' => 'User']);
    
    $this->actingAs($user)
         ->get('/api/v1/super_admin/users')
         ->assertForbidden();
});

test('avatar upload validates file size', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg')->size(20000); // 20MB
    
    expect(fn() => $user->uploadAvatar($file))
        ->toThrow(InvalidArgumentException::class);
});
```

### API Tests
```php
test('user API returns Rails-compatible format', function () {
    $user = User::factory()->create(['type' => 'SuperAdmin']);
    
    $response = $this->getJson("/api/v1/super_admin/users/{$user->id}");
    
    $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'type', 'role', 
                    'avatar_url', 'confirmed', 'locked',
                    'created_at', 'updated_at', 'accounts'
                ]
            ]);
});
```

## Configuration

### Environment Variables
```env
# Avatar settings
FILESYSTEM_DISK=public
APP_DISABLE_GRAVATAR=false

# File upload limits
UPLOAD_MAX_FILESIZE=15M
POST_MAX_SIZE=16M
```

### Storage Configuration
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

## Security Considerations

1. **File Validation**: Strict MIME type and size checking
2. **Path Traversal**: Secure filename generation
3. **Access Control**: Proper middleware on all endpoints
4. **Data Sanitization**: Clean user inputs before storage
5. **Rate Limiting**: Consider implementing for avatar uploads

## Monitoring & Logging

### Key Metrics
- Avatar upload success/failure rates
- File size distribution
- User type distribution
- Authentication failures

### Log Events
- User type changes
- Avatar uploads/deletions
- Permission escalations
- Authentication attempts

This documentation ensures full Rails-Laravel parity while maintaining Laravel best practices and security standards.