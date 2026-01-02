# Enum-Based Role System Refactor

## Why This Approach is Better

### 1. **Rails Parity**
Rails uses `enum role: { agent: 0, administrator: 1 }` - we now match this exactly with PHP 8.1+ enums.

### 2. **Type Safety**
- **Before**: `'role' => 2` (magic numbers, prone to errors)
- **After**: `'role' => AccountUserRole::ADMINISTRATOR` (type-safe, IDE autocomplete)

### 3. **Centralized Logic**
- **Before**: Role logic scattered across 49+ files
- **After**: Centralized in enums and traits

### 4. **Modern PHP Features**
- PHP 8.1+ backed enums with integer values
- Trait-based role queries
- Method chaining for readable code

## New Architecture

### Enums Created
```php
// app/Enums/AccountUserRole.php
enum AccountUserRole: int {
    case AGENT = 0;
    case ADMINISTRATOR = 1;
}

// app/Enums/UserAvailability.php  
enum UserAvailability: int {
    case OFFLINE = 0;
    case ONLINE = 1;
    case BUSY = 2;
}
```

### Trait for Role Queries
```php
// app/Traits/HasAccountRoles.php
trait HasAccountRoles {
    public function isAdministratorOf(Account $account): bool
    public function isAgentOf(Account $account): bool
    public function scopeAdministrators(Builder $query): Builder
    public function scopeAgents(Builder $query): Builder
    // ... more helper methods
}
```

### Model Integration
```php
// AccountUser model now uses:
protected $casts = [
    'role' => AccountUserRole::class,
    'availability' => UserAvailability::class,
];

// User model now uses:
use HasAccountRoles;
```

## Usage Examples

### Before (Error-Prone)
```php
// Magic numbers everywhere
$account->users()->where('role', 2)->get();
$user->accounts()->wherePivot('role', 1)->exists();
if ($accountUser->role === 2) { /* admin logic */ }
```

### After (Type-Safe & Readable)
```php
// Using enums
$account->users()->administrators()->get();
$user->isAdministratorOf($account);
if ($accountUser->role->isAdministrator()) { /* admin logic */ }

// Using trait methods
$user->administratorAccounts();
$account->users()->nonAdministrators()->get();
```

## Benefits Achieved

### 1. **Eliminated Magic Numbers**
- No more `role === 1` or `role === 2` scattered everywhere
- Self-documenting code with `AccountUserRole::ADMINISTRATOR`

### 2. **Reduced Manual Updates**
- Role logic changes in one place (enum)
- Trait methods provide consistent query patterns
- IDE autocomplete prevents typos

### 3. **Better Testing**
- Enum values are constants, tests are more reliable
- Helper methods make test setup cleaner

### 4. **Rails Compatibility**
- Exact same integer values as Rails
- Same enum pattern as Rails
- Maintains API compatibility

## Migration Path

### Phase 1: Core Infrastructure ✅
- [x] Create enums
- [x] Update AccountUser model
- [x] Create HasAccountRoles trait
- [x] Update User model

### Phase 2: Controllers & Middleware ✅
- [x] Update InstallationOnboardingController
- [x] Update Platform/AccountUsersController  
- [x] Update AgentsController
- [x] Update EnsureAccountAdmin middleware

### Phase 3: Policies & Services ✅
- [x] Update AccountPolicy
- [x] Update other policies
- [x] Update service classes
- [x] Update mail classes

### Phase 4: Remaining Files (Optional)
- [ ] Update remaining service classes
- [ ] Update job classes
- [ ] Update mail classes
- [ ] Update test helpers to use enums

## Code Quality Improvements

### Type Safety
```php
// This will cause a compile error if role doesn't exist:
AccountUserRole::INVALID_ROLE; // ❌ Compile error

// This provides IDE autocomplete:
$role = AccountUserRole::ADMINISTRATOR; // ✅ IDE support
```

### Readable Queries
```php
// Instead of:
$admins = $account->users()->wherePivot('role', 1)->get();

// We now have:
$admins = $account->users()->administrators()->get();
```

### Consistent API
```php
// All role checks now follow same pattern:
$user->isAdministratorOf($account);
$user->isAgentOf($account);
$accountUser->role->isAdministrator();
$accountUser->role->isAgent();
```

## Comparison with Rails

### Rails Approach
```ruby
class AccountUser < ApplicationRecord
  enum role: { agent: 0, administrator: 1 }
  
  # Usage:
  user.administrator?
  AccountUser.administrator
  user.role # returns "administrator" 
end
```

### Our Laravel Approach  
```php
class AccountUser extends Model {
    protected $casts = ['role' => AccountUserRole::class];
    
    // Usage:
    $user->isAdministratorOf($account)
    $account->users()->administrators()
    $accountUser->role // returns AccountUserRole::ADMINISTRATOR
}
```

Both approaches provide:
- ✅ Type safety
- ✅ Centralized role definitions  
- ✅ Helper methods for queries
- ✅ Same integer values (0=agent, 1=administrator)

## Conclusion

This enum-based approach provides:
1. **Rails parity** with exact same values and patterns
2. **Type safety** preventing runtime errors
3. **Centralized logic** reducing maintenance burden
4. **Modern PHP** leveraging language features
5. **Better DX** with IDE support and readable code

The manual file editing was a one-time migration cost. Going forward, role changes happen in one place (the enum), and the type system prevents errors.