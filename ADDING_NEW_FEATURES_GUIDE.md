# Adding New Features to Laravel/Svelte Feature Flag System

This guide explains how to properly add new features to the Laravel/Svelte feature flag system to maintain parity with Rails and ensure consistent functionality.

## Overview

The feature flag system consists of several components that work together:

1. **Laravel Feature Enum** - Defines available features and metadata
2. **Account Model** - Handles feature flag storage and checking via bit flags
3. **SuperAdmin API Controller** - Exposes features to frontend
4. **Svelte FeatureFlagManager** - Displays and manages features in UI

## Step-by-Step Guide

### Step 1: Add Feature to Laravel Feature Enum

**File**: `laravel-svelte-port/laravel/app/Enums/Feature.php`

```php
enum Feature: string
{
    // Add your new feature case
    case NEW_FEATURE_NAME = 'new_feature_name';
    
    // Existing features...
    case CUSTOM_BRANDING = 'custom_branding';
    // ...
}
```

**Rules:**
- Use `SCREAMING_SNAKE_CASE` for the case name
- Use `snake_case` for the string value
- Add to appropriate section (Premium Features or Standard Features)

### Step 2: Add Feature Metadata

In the same file, add metadata in the `metadata()` method:

```php
public function metadata(): array
{
    return match ($this) {
        self::NEW_FEATURE_NAME => [
            'display_name' => 'New Feature Display Name',
            'description' => 'Description of what this feature does',
            'enabled' => false,  // true = enabled by default, false = disabled
            'premium' => true,   // true = enterprise feature, false = standard
            'chatwoot_internal' => false,  // true = internal only, false = public
            'help_url' => 'https://docs.chatwoot.com/features/new-feature',
        ],
        // Existing features...
    };
}
```

**Metadata Fields:**
- `display_name`: Human-readable name shown in UI
- `description`: Feature description for tooltips/help
- `enabled`: Whether enabled by default for new accounts
- `premium`: Whether this is an enterprise/premium feature
- `chatwoot_internal`: Whether feature is internal to Chatwoot only
- `help_url`: Link to documentation

### Step 3: Add Feature to Account Model Bit Flags

**File**: `laravel-svelte-port/laravel/app/Models/Account.php`

#### For Standard Features (with bit flags):

```php
public function feature_enabled(string $feature): bool
{
    $flagMap = [
        // Add your new feature with next available bit position
        'new_feature_name' => 4294967296, // Next available bit (2^32)
        
        // Existing features...
        'custom_branding' => 536870912,
        'disable_branding' => 1073741824,
        'agent_capacity' => 2147483648,
        // ...
    ];
    
    // Rest of method...
}
```

**Update all three methods:**
- `feature_enabled()` - Add to `$flagMap`
- `enableFeature()` - Add to `$flagMap` 
- `disableFeature()` - Add to `$flagMap`
- `getEnabledFeatures()` - Add to `$flagMap`

#### For Enterprise Features (default handling):

```php
public function feature_enabled(string $feature): bool
{
    // ... existing flagMap code ...
    
    // Default feature availability for unknown features (Rails compatibility)
    $defaultFeatures = [
        // Add your enterprise feature here
        'new_enterprise_feature' => false, // Enterprise features default to false
        
        // Existing enterprise features...
        'saml' => false,
        'sla' => false,
        // ...
    ];
    
    return $defaultFeatures[$feature] ?? false;
}
```

**Bit Position Guidelines:**
- Use powers of 2: 1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, etc.
- Current highest bit: `2147483648` (2^31)
- Next available: `4294967296` (2^32)
- For 64-bit support: Continue with `8589934592` (2^33), etc.

### Step 4: Add Feature to SuperAdmin API Controller

**File**: `laravel-svelte-port/laravel/app/Http/Controllers/Api/V1/SuperAdmin/AccountsController.php`

#### Add to transformAccount method:

```php
private function transformAccount($account): array
{
    // Map Laravel feature names to frontend expected names
    $featureNameMap = [
        // Add your new feature
        'new_feature_name' => 'new_feature_name',
        
        // Existing features...
        'custom_branding' => 'custom_branding',
        'disable_branding' => 'disable_branding',
        // ...
    ];
    
    // Rest of method...
}
```

#### Add to updateAccountFeatureFlags method:

```php
private function updateAccountFeatureFlags(Account $account, array $selectedFeatures): void
{
    $featureNameMap = [
        // Add your new feature
        'new_feature_name' => 'new_feature_name',
        
        // Existing features...
        'custom_branding' => 'custom_branding',
        'disable_branding' => 'disable_branding',
        // ...
    ];
    
    // Rest of method...
}
```

### Step 5: Add Feature to Svelte UI Categories

**File**: `laravel-svelte-port/svelte-ui/src/lib/components/FeatureFlagManager.svelte`

#### Add to premium features list (if premium):

```typescript
// Premium features that require special handling
const premiumFeatures = [
    'newFeatureName', // Add in camelCase (API client transforms snake_case to camelCase)
    
    // Existing premium features...
    'customBranding', 'disableBranding', 'agentCapacity',
    // ...
];
```

#### Add to appropriate feature category:

```typescript
const featureCategories = {
    'Enterprise Features': [
        'newFeatureName', // Add in camelCase
        
        // Existing enterprise features...
        'customBranding', 'disableBranding', 'agentCapacity',
        // ...
    ],
    'Product Features': [
        'newStandardFeature', // Add standard features here
        
        // Existing product features...
        'macros', 'labels', 'teamManagement',
        // ...
    ],
    // Other categories...
};
```

**Category Guidelines:**
- **Communication Channels**: Channel integrations (email, WhatsApp, etc.)
- **Product Features**: Core functionality (macros, labels, automation, etc.)
- **Integrations**: Third-party service connections (Slack, Linear, etc.)
- **Enterprise Features**: Premium/enterprise capabilities

## Feature Naming Conventions

### Backend (Laravel)
- **Enum Case**: `SCREAMING_SNAKE_CASE` (e.g., `NEW_FEATURE_NAME`)
- **String Value**: `snake_case` (e.g., `'new_feature_name'`)
- **Database**: `snake_case` (stored as bit flags or defaults)

### Frontend (Svelte)
- **Variable Names**: `camelCase` (e.g., `newFeatureName`)
- **API Communication**: `snake_case` (API client auto-transforms)

### Rails Compatibility
- **Feature Names**: Use same `snake_case` names as Rails YAML config
- **Mappings**: Add Rails aliases if different naming needed

## Testing Your New Feature

### 1. Backend Testing

```php
// Test in Laravel Tinker or create a test
$account = Account::first();

// Test feature checking
$account->feature_enabled('new_feature_name'); // Should return false initially

// Test enabling feature
$account->enableFeature('new_feature_name');
$account->feature_enabled('new_feature_name'); // Should return true

// Test disabling feature
$account->disableFeature('new_feature_name');
$account->feature_enabled('new_feature_name'); // Should return false
```

### 2. API Testing

```bash
# Test API response includes new feature
curl -X GET "http://localhost:8000/api/v1/super_admin/accounts/1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Should include in response:
# "all_features": {
#   "new_feature_name": true,
#   ...
# }
```

### 3. Frontend Testing

1. Navigate to SuperAdmin → Accounts → Edit Account
2. Verify new feature appears in appropriate category
3. Test toggling feature on/off
4. Verify changes persist after save

## Common Pitfalls to Avoid

### ❌ Don't Do This:

1. **Skipping bit flag updates**: Adding to enum but not Account model
2. **Wrong bit positions**: Using same bit as existing feature
3. **Inconsistent naming**: Different names in different files
4. **Missing API mappings**: Feature won't appear in frontend
5. **Wrong category**: Premium features in standard category

### ✅ Do This:

1. **Follow all 5 steps**: Enum → Account → API → Frontend → Testing
2. **Use next available bit**: Check existing bits, use next power of 2
3. **Consistent naming**: Same `snake_case` name everywhere
4. **Proper categorization**: Premium features marked as premium
5. **Test thoroughly**: Backend, API, and frontend functionality

## Example: Adding "Advanced Analytics" Feature

Here's a complete example of adding a new premium feature:

### 1. Feature Enum
```php
case ADVANCED_ANALYTICS = 'advanced_analytics';

self::ADVANCED_ANALYTICS => [
    'display_name' => 'Advanced Analytics',
    'description' => 'Detailed analytics with custom reports and insights',
    'enabled' => false,
    'premium' => true,
    'chatwoot_internal' => false,
    'help_url' => 'https://docs.chatwoot.com/features/advanced-analytics',
],
```

### 2. Account Model
```php
'advanced_analytics' => 8589934592, // 2^33 (next available bit)
```

### 3. API Controller
```php
'advanced_analytics' => 'advanced_analytics',
```

### 4. Svelte UI
```typescript
// Add to premiumFeatures
'advancedAnalytics',

// Add to Enterprise Features category
'Enterprise Features': [
    'advancedAnalytics',
    // ... existing features
],
```

## Maintenance Notes

- **Bit Limit**: Current system supports up to 64 features with bit flags
- **Enterprise Features**: Use default handling for unlimited enterprise features
- **Rails Sync**: Keep feature names synchronized with Rails YAML config
- **Documentation**: Update help URLs when features are documented

This guide ensures new features are added consistently and maintain full compatibility between Laravel backend and Svelte frontend while preserving Rails parity.