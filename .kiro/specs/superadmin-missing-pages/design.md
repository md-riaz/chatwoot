# Design Document

## Overview

This design implements the missing SuperAdmin pages in SvelteKit to achieve functional parity with the Rails + Vue SuperAdmin system. The design focuses on simplicity and Rails compatibility, implementing only the essential functionality found in the Rails system without over-engineering.

## Architecture

### Component Structure
```
laravel-svelte-port/svelte-ui/src/routes/app/super_admin/
├── settings/
│   └── +page.svelte                    # Settings with expandable config categories
├── access-tokens/
│   └── +page.svelte                    # System-wide access token management (utility page)
├── users/[id]/
│   └── +page.svelte                    # Enhanced with access token display
├── agent-bots/[id]/
│   └── +page.svelte                    # Enhanced with access token display  
├── platform-apps/[id]/
│   └── +page.svelte                    # Enhanced with access token display
├── accounts/[id]/
│   └── +page.svelte                    # Enhanced with account-user form
└── +layout@.svelte                     # Updated navigation (remove missing items)
```

### API Integration
```
laravel-svelte-port/laravel/app/Http/Controllers/Api/V1/SuperAdmin/
├── InstallationConfigsController.php   # ✅ Exists - config management
├── AccessTokensController.php          # ✅ Exists - token management
├── AccountUsersController.php          # ✅ Exists - relationship management
├── AuditController.php                 # ✅ Exists - audit logs (not used)
└── CacheController.php                 # ✅ Exists - cache management (not used)
```

## Components and Interfaces

### 1. Settings Page with Configuration Categories

**Component**: `settings/+page.svelte`
**Purpose**: Expandable settings menu with configuration categories matching Rails

```typescript
interface ConfigCategory {
  key: string;
  name: string;
  icon: string;
  enabled: boolean;
  configs: ConfigField[];
}

interface ConfigField {
  name: string;
  displayTitle: string;
  description: string;
  type: 'boolean' | 'text' | 'secret' | 'code' | 'select';
  value: any;
  locked: boolean;
  options?: Record<string, string>; // For select type
}
```

**Features**:
- Expandable categories (general, facebook, email, slack, google, microsoft, etc.)
- Dynamic form fields based on config type
- Locked config indicators
- Validation and error handling
- URL pattern: `/app/super_admin/settings?config=category`

### 2. Access Tokens Integration

**Enhanced Components**: 
- `users/[id]/+page.svelte`
- `agent-bots/[id]/+page.svelte` 
- `platform-apps/[id]/+page.svelte`

**Utility Component**: `access-tokens/+page.svelte`

```typescript
interface AccessToken {
  id: number;
  name: string;
  ownerType: 'User' | 'AgentBot' | 'PlatformApp';
  ownerId: number;
  abilities: string[];
  lastUsedAt: string | null;
  expiresAt: string | null;
  createdAt: string;
  owner: {
    id: number;
    type: string;
    name: string;
    email?: string;
  };
}
```

**Features**:
- Token display in owner entity pages (primary access method)
- Masked token values for security
- Revocation functionality
- System-wide token management page (utility access)
- Owner type filtering

### 3. Account Users Embedded Forms

**Enhanced Components**:
- `accounts/[id]/+page.svelte`
- `users/[id]/+page.svelte`

```typescript
interface AccountUser {
  id: number;
  userId: number;
  accountId: number;
  role: 'agent' | 'administrator';
  availability: number;
  createdAt: string;
  user: {
    id: number;
    name: string;
    email: string;
    displayName: string;
  };
  account: {
    id: number;
    name: string;
    domain: string;
  };
}

interface AccountUserForm {
  userId?: number;
  accountId?: number;
  role: 'agent' | 'administrator';
  availability: number;
}
```

**Features**:
- Embedded forms in account/user show pages
- Role selection (agent/administrator)
- Duplicate relationship prevention
- Last administrator protection
- Existing relationship management

### 4. Updated Navigation

**Component**: `+layout@.svelte`

**Removed Items**:
- Access Tokens (accessed via owner pages)
- Installation Configs (accessed via Settings)
- Account Users (embedded forms only)
- Audit Logs (doesn't exist in Rails)
- Cache (doesn't exist in Rails)

**Navigation Structure**:
```typescript
const navItems: NavItem[] = [
  { label: 'Dashboard', href: '/app/super_admin/dashboard', icon: LayoutDashboard },
  { label: 'Accounts', href: '/app/super_admin/accounts', icon: Building2 },
  { label: 'Users', href: '/app/super_admin/users', icon: Users },
  { label: 'Settings', href: '/app/super_admin/settings', icon: Settings }, // Expandable
  { label: 'Agent Bots', href: '/app/super_admin/agent-bots', icon: Bot },
  { label: 'Platform Apps', href: '/app/super_admin/platform-apps', icon: AppWindow },
];
```

## Data Models

### Configuration Management

**Laravel Model**: `InstallationConfig`
```php
class InstallationConfig extends Model {
  protected $fillable = ['name', 'value', 'locked'];
  protected $casts = [
    'value' => 'array', // JSON casting for serialized values
    'locked' => 'boolean',
  ];
  
  // Scopes
  public function scopeEditable($query) {
    return $query->where('locked', false);
  }
  
  // Methods
  public static function getConfigGroups(): array {
    // Returns category mappings from config
  }
  
  public static function setConfig(string $name, $value, bool $locked = true) {
    // Creates or updates configuration
  }
}
```

**SvelteKit Interface**:
```typescript
interface InstallationConfig {
  id: number;
  name: string;
  value: any;
  locked: boolean;
  createdAt: string;
  updatedAt: string;
}
```

### Access Token Management

**Laravel Model**: `PersonalAccessToken` (Sanctum)
```php
use Laravel\Sanctum\PersonalAccessToken;

class PersonalAccessToken extends Model {
  protected $fillable = ['name', 'tokenable_type', 'tokenable_id', 'abilities'];
  protected $casts = [
    'abilities' => 'array',
    'last_used_at' => 'datetime',
    'expires_at' => 'datetime',
  ];
  
  // Relationships
  public function tokenable() {
    return $this->morphTo();
  }
}
```

### Account User Relationships

**Laravel Model**: `AccountUser`
```php
class AccountUser extends Model {
  protected $fillable = ['user_id', 'account_id', 'role', 'availability', 'settings'];
  protected $casts = [
    'role' => AccountUserRole::class, // Enum casting
    'availability' => UserAvailability::class, // Enum casting
    'settings' => 'array',
    'active_at' => 'datetime',
  ];
  
  // Relationships
  public function user() {
    return $this->belongsTo(User::class);
  }
  
  public function account() {
    return $this->belongsTo(Account::class);
  }
  
  // Methods
  public function getRoleNameAttribute(): string {
    return $this->role->name;
  }
}
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system-essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property-Based Testing Overview

Property-based testing (PBT) validates software correctness by testing universal properties across many generated inputs. Each property is a formal specification that should hold for all valid inputs.

### Core Principles

1. **Universal Quantification**: Every property must contain an explicit "for all" statement
2. **Requirements Traceability**: Each property must reference the requirements it validates
3. **Executable Specifications**: Properties must be implementable as automated tests
4. **Comprehensive Coverage**: Properties should cover all testable acceptance criteria

### Common Property Patterns

1. **Invariants**: Properties that remain constant despite changes to structure or order
2. **Round Trip Properties**: Combining an operation with its inverse to return to original value
3. **Idempotence**: Operations where doing it twice equals doing it once
4. **Metamorphic Properties**: Relationships that must hold between components
5. **Model Based Testing**: Optimized implementation vs standard implementation
6. **Error Conditions**: Generate bad inputs and ensure they properly signal errors

Now I need to use the prework tool to analyze the acceptance criteria before writing the correctness properties:

### Converting EARS to Properties

Based on the prework analysis, I'll convert the testable acceptance criteria into properties while eliminating redundancy:

**Property 1: Configuration categorization and display**
*For any* configuration field, it should appear under the correct category and display all required metadata (description, type, locked status)
**Validates: Requirements 1.2, 1.5, 1.7**

**Property 2: Configuration validation by type**
*For any* configuration field, validation should enforce the correct rules based on its type (boolean, text, secret, code, select)
**Validates: Requirements 1.3, 5.1**

**Property 3: Locked configuration protection**
*For any* locked configuration, attempts to modify it should be rejected and the locked status should be clearly indicated
**Validates: Requirements 1.4, 1.6**

**Property 4: URL pattern consistency**
*For any* configuration category, the URL should follow the pattern `/app/super_admin/settings?config=category`
**Validates: Requirements 1.11**

**Property 5: Access token display on owner pages**
*For any* entity with access tokens (User, AgentBot, PlatformApp), viewing the entity's page should display its token information with required fields
**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**

**Property 6: Token security masking**
*For any* access token display, the token value should be masked for security (partial display only)
**Validates: Requirements 2.7**

**Property 7: Token revocation workflow**
*For any* access token, revoking it should delete the token and provide confirmation feedback
**Validates: Requirements 2.6**

**Property 8: Account-user relationship validation**
*For any* account-user relationship creation, the system should prevent duplicates and enforce business rules (last admin protection)
**Validates: Requirements 3.3, 3.5, 5.2, 5.3**

**Property 9: Embedded form presence**
*For any* account or user detail page, it should contain the appropriate embedded form for managing relationships
**Validates: Requirements 3.6**

**Property 10: Error handling consistency**
*For any* invalid operation (missing resources, network errors, validation failures), the system should provide appropriate user-friendly error messages
**Validates: Requirements 5.4, 5.6**

**Property 11: Frontend-backend validation parity**
*For any* validation rule, the frontend and backend should enforce the same constraints consistently
**Validates: Requirements 5.5**

**Property 12: Rails functional equivalence**
*For any* configuration or access token operation, the behavior should match the Rails system's functionality
**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5, 6.6**

## Error Handling

### Configuration Management Errors
- **Invalid config type**: Display type-specific validation messages
- **Locked config modification**: Show locked status and prevent changes
- **Network failures**: Retry mechanism with user feedback
- **Validation errors**: Field-level error display with descriptions

### Access Token Errors
- **Token not found**: 404 error with navigation back to owner page
- **Revocation failures**: Error message with retry option
- **Permission errors**: Clear messaging about access restrictions

### Account-User Relationship Errors
- **Duplicate relationships**: Prevent creation with clear error message
- **Last admin removal**: Block action with explanation of business rule
- **Invalid user/account**: Validation with helpful suggestions

### Network and System Errors
- **API timeouts**: Retry mechanism with progress indication
- **Server errors**: User-friendly messages with support contact
- **Validation failures**: Inline field validation with immediate feedback

## Testing Strategy

### Dual Testing Approach
The system will use both unit tests and property-based tests for comprehensive coverage:

**Unit Tests**:
- Specific examples and edge cases
- UI component rendering and interaction
- Error condition handling
- Integration points between components

**Property-Based Tests**:
- Universal properties across all inputs
- Configuration validation across all types
- Access token behavior across all owner types
- Relationship management across all scenarios

### Property Test Configuration
- **Minimum 100 iterations** per property test
- **Test tags**: `Feature: superadmin-missing-pages, Property {number}: {property_text}`
- **Framework**: Vitest with property testing library (fast-check or similar)
- **Coverage**: Each correctness property implemented as single property-based test

### Testing Framework Integration
```typescript
// Example property test structure
describe('SuperAdmin Missing Pages', () => {
  test('Property 1: Configuration categorization and display', async () => {
    // Feature: superadmin-missing-pages, Property 1: Configuration categorization and display
    await fc.assert(fc.asyncProperty(
      configurationGenerator(),
      async (config) => {
        const category = getCategoryForConfig(config.name);
        const display = await renderConfigInCategory(category, config);
        
        expect(display.category).toBe(expectedCategory);
        expect(display.description).toBe(config.description);
        expect(display.locked).toBe(config.locked);
      }
    ), { numRuns: 100 });
  });
});
```

### Unit Test Balance
- **Focus on specific examples**: Configuration type validation, token masking formats
- **Integration testing**: Form submissions, navigation flows, API interactions
- **Edge cases**: Empty states, error conditions, boundary values
- **Property tests handle**: Comprehensive input coverage, universal behavior validation

This dual approach ensures both concrete functionality (unit tests) and universal correctness (property tests) while maintaining efficient test execution and clear failure diagnosis.