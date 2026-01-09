# Feature Flags Update Fix

## Problem

The Super Admin account update feature was not correctly updating feature flags from the Svelte frontend to the Laravel API backend. Features selected in the UI were not being persisted to the database.

## Root Causes

### 1. Multiple Database Saves (Race Condition)
The original `updateAccountFeatureFlags()` method in `AccountsController` called `enableFeature()` and `disableFeature()` for each feature individually. These methods each performed their own `save()` operation, resulting in:
- 20+ separate database saves for a typical feature update
- Race conditions where intermediate saves could overwrite each other
- Bitwise operations being lost between saves due to model state management

### 2. Incorrect Feature Name Mapping
The `transformAccount()` method had the feature name mapping backwards:
- It was mapping frontend names to frontend names (identity mapping)
- Should have been mapping internal names (email, messenger, liveChat) to frontend names (email_integration, facebook_integration, website_widget)
- This caused the API response to not include the correct feature flags after save

## Solution

### 1. Batched Feature Flag Updates
Modified `AccountsController::updateAccountFeatureFlags()` to:
```php
// Reset all bit flags to 0
$account->feature_flags = 0;

// Enable selected bit flag features (all at once)
foreach ($bitFlagFeatures as $feature) {
    if (isset($flagMap[$feature])) {
        $account->feature_flags |= $flagMap[$feature];
    }
}

// Update enterprise features in custom_attributes
$customAttributes = $account->custom_attributes ?? [];
$customAttributes['enabled_enterprise_features'] = $selectedEnterpriseFeatures;
$account->custom_attributes = $customAttributes;

// Save once with all changes
$account->save();
```

**Benefits:**
- Single database save instead of 20+
- No race conditions
- Atomic operation
- More efficient

### 2. Fixed Feature Name Mapping
Corrected the mapping in `transformAccount()` to properly convert:

**Internal → Frontend:**
- `email` → `email_integration`
- `messenger` → `facebook_integration`
- `liveChat` → `website_widget`
- `teams` → `team_management`
- etc.

**Synthetic Features:**
Added support for "synthetic" features that map to the same underlying bit:
- `api_access`, `real_time_notifications` → based on `webhooks` bit
- `file_attachments` → based on `liveChat` bit
- `conversation_notes` → based on `customAttributes` bit
- etc.

## Architecture

### Feature Flag Storage

Features are stored in two places:

1. **Bit Flags** (`feature_flags` integer column):
   - Core communication channels (email, whatsapp, facebook, etc.)
   - Product features (macros, labels, campaigns, etc.)
   - Integrations (linear, slack, shopify, etc.)
   - Some premium features (custom_branding, disable_branding, etc.)

2. **Custom Attributes** (`custom_attributes` JSON column):
   - Enterprise features (saml, sla_policies, custom_roles, audit_logs)
   - Stored in `custom_attributes['enabled_enterprise_features']` array

### Data Flow

```
Frontend (Svelte)
    ↓ (camelCase → snake_case via API client)
Controller.update()
    ↓ (validates request)
Controller.updateAccountFeatureFlags()
    ↓ (batches operations)
Account.feature_flags = 0 | bit1 | bit2 | ...
Account.custom_attributes['enabled_enterprise_features'] = [...]
    ↓ (single save)
Database
    ↓ (retrieve)
Account.getEnabledFeatures()
    ↓ (returns internal names: email, messenger, macros, ...)
Controller.transformAccount()
    ↓ (maps to frontend names)
Frontend (Svelte)
    ↓ (snake_case → camelCase via API client)
UI displays correct features ✓
```

## Testing

### Integration Test
A complete integration test verifies the round-trip:
```bash
php /tmp/integration_test.php
```

Expected output:
```
✅ SUCCESS: All features preserved correctly!
```

### Manual Testing

1. **Update features via API:**
```bash
curl -X PUT http://localhost:8000/api/v1/super_admin/accounts/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "selected_feature_flags": [
      "macros", "labels", "campaigns", "webhooks",
      "email_integration", "facebook_integration",
      "custom_branding", "saml", "custom_roles"
    ]
  }'
```

2. **Verify in database:**
```sql
SELECT id, name, feature_flags, custom_attributes 
FROM accounts 
WHERE id = 1;
```

3. **Check response:**
```bash
curl http://localhost:8000/api/v1/super_admin/accounts/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Should return the same features in `selected_feature_flags` that were sent.

## Files Modified

1. **`laravel-svelte-port/laravel/app/Http/Controllers/Api/V1/SuperAdmin/AccountsController.php`**
   - `updateAccountFeatureFlags()` - Batched operations, single save
   - `transformAccount()` - Fixed feature name mapping

## Related Tests

- `tests/Feature/SuperAdmin/AccountFeatureFlagsTest.php`
  - `it_can_enable_bit_flag_features()`
  - `it_can_enable_enterprise_features()`
  - `superadmin_api_can_update_account_features()`
  - `it_clears_existing_features_before_setting_new_ones()`

## Feature Name Mappings

### Bit Flag Features (stored in `feature_flags`)

| Internal Name | Frontend Name | Bit Value |
|--------------|---------------|-----------|
| email | email_integration | 1 |
| sms | twitter_integration | 2 |
| messenger | facebook_integration | 4 |
| whatsapp | whatsapp_integration | 16 |
| instagram | instagram_integration | 64 |
| macros | macros | 256 |
| labels | labels | 512 |
| teams | team_management | 1024 |
| reports | conversation_search, csat_surveys | 2048 |
| campaigns | campaigns | 4096 |
| webhooks | webhooks, api_access, real_time_notifications | 8192 |
| cannedResponses | canned_responses | 524288 |
| automationRules | automation_rules, conversation_status | 2097152 |
| customAttributes | contact_management, conversation_notes | 4194304 |
| liveChat | website_widget, file_attachments | 8388608 |
| assignment_v2 | conversation_assignment | 16777216 |
| inbox_assistant | openai_integration | 33554432 |
| advanced_reporting | advanced_reporting | 67108864 |
| custom_branding | custom_branding | 536870912 |
| disable_branding | disable_branding | 1073741824 |
| agent_capacity | agent_capacity | 2147483648 |

### Enterprise Features (stored in `custom_attributes`)

| Internal Name | Frontend Name |
|--------------|---------------|
| saml | saml |
| sla_policies | sla_policies |
| custom_roles | custom_roles |
| audit_logs | audit_logs |

## Notes

- Bit flags use bitwise OR operations to combine multiple features into a single integer
- Each feature has a unique power of 2 value (1, 2, 4, 8, 16, 32, 64, ...)
- Maximum 32 features can be stored in a 32-bit integer (or 63 in a 64-bit integer)
- Enterprise features are stored separately in JSON to allow unlimited feature additions
- "Synthetic" features share the same bit as their base feature to maintain compatibility
