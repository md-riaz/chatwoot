# Onboarding API Response Fix

## Issue Summary
The API response contains two arrays:
- `features`: All possible features for the account (e.g., "macros", "labels", "webhooks", etc.)
- `selected_feature_flags`: The features currently enabled for the account (same values as above).

In the UI:
- "Product Features" checkboxes (e.g., Macros, Labels, Webhooks, Campaigns) are checked correctly.
- "Other Features" checkboxes (e.g., ShopifyIntegration, CustomRoles, SlaPolicies, etc.) are all unchecked, even though some of these features are present in `selected_feature_flags`.

## Root Cause
The checkbox values in the Svelte code do not match the feature names in the API response.

- **API uses snake_case** (e.g., "custom_attributes", "automation_rules", "canned_responses")
- **Checkbox values in Svelte use camelCase** (e.g., CustomRoles, ShopifyIntegration, AutomationRules, CannedResponses)

Because of this mismatch, the logic that checks if a feature is enabled (e.g., `selected_feature_flags.includes(featureName)`) always returns false for "Other Features".

## Solution

### Backend Changes (Laravel)

1. **Updated AccountsController** (`laravel-svelte-port/laravel/app/Http/Controllers/Api/V1/SuperAdmin/AccountsController.php`):
   - Fixed `featureNameMap` to properly map Feature enum values (snake_case) to frontend expected format
   - Updated `updateAccountFeatureFlags` method to handle reverse transformation
   - Ensured consistency with Feature enum values

2. **Feature Flow**:
   - Backend Feature enum uses snake_case: `canned_responses`, `automation_rules`, `custom_roles`
   - Backend sends snake_case in API response
   - Frontend API client automatically transforms snake_case → camelCase: `cannedResponses`, `automationRules`, `customRoles`

### Frontend Changes (SvelteKit)

1. **Updated FeatureFlagManager component** (`laravel-svelte-port/svelte-ui/src/lib/components/FeatureFlagManager.svelte`):
   - Updated `featureCategories` to use camelCase feature names that match API client transformation
   - Updated `premiumFeatures` array to use camelCase
   - Fixed `formatFeatureName` function to handle camelCase properly (converts `cannedResponses` → `Canned Responses`)

2. **Feature Categories Updated**:
   ```javascript
   const featureCategories = {
     'Communication Channels': [
       'websiteWidget', 'emailIntegration', 'whatsappIntegration', 'facebookIntegration', 
       'instagramIntegration', 'twitterIntegration'
     ],
     'Product Features': [
       'macros', 'labels', 'cannedResponses', 'teamManagement',
       'automationRules', 'webhooks', 'campaigns', 'contactManagement',
       // ... etc
     ],
     'Enterprise Features': [
       'customRoles', 'slaPolicies', 'auditLogs',
       'advancedReporting', 'openaiIntegration', 'csatSurveys'
     ]
   };
   ```

## Data Flow

1. **Backend → Frontend (Response)**:
   ```
   Backend Feature Enum: custom_roles, automation_rules, canned_responses
   ↓ (AccountsController transformation)
   API Response: { selected_feature_flags: ["custom_roles", "automation_rules", "canned_responses"] }
   ↓ (Frontend API client transformation)
   Frontend Component: { selectedFeatureFlags: ["customRoles", "automationRules", "cannedResponses"] }
   ```

2. **Frontend → Backend (Request)**:
   ```
   Frontend Component: ["customRoles", "automationRules", "cannedResponses"]
   ↓ (Frontend API client transformation)
   API Request: { selected_feature_flags: ["custom_roles", "automation_rules", "canned_responses"] }
   ↓ (AccountsController reverse transformation)
   Backend Feature Methods: enableFeature("custom_roles"), enableFeature("automation_rules")
   ```

## Key Changes Made

### 1. Backend AccountsController
- Fixed feature name mapping to match Feature enum values
- Updated both response and request transformation logic
- Ensured consistency between `transformAccount` and `updateAccountFeatureFlags` methods

### 2. Frontend FeatureFlagManager
- Updated feature categories to use camelCase (post-transformation format)
- Fixed `formatFeatureName` to handle camelCase → readable format
- Maintained automatic API transformation (no manual case conversion needed)

### 3. Maintained API Transformation Layer
- Kept automatic snake_case ↔ camelCase transformation in API client
- No manual case conversion in components (follows project guidelines)
- Consistent with existing codebase patterns

## Testing

To verify the fix:

1. **Check API Response**: Ensure backend sends snake_case feature names
2. **Check Frontend Transformation**: Verify API client converts to camelCase
3. **Check Component Matching**: Confirm FeatureFlagManager uses correct camelCase names
4. **Test Feature Toggle**: Verify enabling/disabling features works correctly
5. **Test "Other Features"**: Confirm uncategorized features appear and work properly

## Files Modified

- `laravel-svelte-port/laravel/app/Http/Controllers/Api/V1/SuperAdmin/AccountsController.php`
- `laravel-svelte-port/svelte-ui/src/lib/components/FeatureFlagManager.svelte`

The fix ensures that feature flags work correctly across all categories while maintaining the existing API transformation patterns and Laravel/SvelteKit conventions.