# Reports API - Route Path Fix

## Issue

All reports API endpoints were returning 404 errors because the frontend was using incorrect URL paths.

**Frontend was calling**:
```
GET /api/v2/accounts/{id}/live_reports/conversation_metrics
GET /api/v2/accounts/{id}/agents/status
GET /api/v2/accounts/{id}/reports
```

**But Laravel routes are registered as**:
```
GET /api/v1/accounts/{id}/v2/live_reports/conversation_metrics
GET /api/v1/accounts/{id}/v2/agents/status
GET /api/v1/accounts/{id}/v2/reports
```

## Root Cause

The Laravel routes are nested inside the `accounts/{account}` scope in `routes/api.php`, and the v2 prefix is applied **after** the account scope, not before.

**Laravel Route Structure**:
```php
// routes/api.php
Route::prefix('accounts/{account}')->group(function () {
    // ... other routes ...
    
    // Reports V2 routes are INSIDE the account scope
    Route::prefix('v2/reports')->group(function () {
        Route::get('/', [ReportsController::class, 'index']);
        Route::get('conversation_traffic', [ReportsController::class, 'conversationTraffic']);
        // ...
    });
    
    Route::prefix('v2/live_reports')->group(function () {
        Route::get('conversation_metrics', [LiveReportsController::class, 'conversationMetrics']);
        Route::get('grouped_conversation_metrics', [LiveReportsController::class, 'groupedConversationMetrics']);
    });
    
    Route::prefix('v2/agents')->group(function () {
        Route::get('status', [AgentsController::class, 'status']);
    });
});
```

This creates paths like: `/api/v1/accounts/{account}/v2/reports/...`

## Fix Applied

**File**: `laravel-svelte-port/svelte-ui/src/lib/api/reports.ts`

Updated all API endpoint paths to match Laravel's route structure:

### Before (Broken) ❌
```typescript
// Live Reports
api.get(`api/v2/accounts/${accountId}/live_reports/conversation_metrics`)
api.get(`api/v2/accounts/${accountId}/live_reports/grouped_conversation_metrics`)
api.get(`api/v2/accounts/${accountId}/agents/status`)

// Heatmap Data
api.get(`api/v2/accounts/${accountId}/reports`)
api.get(`api/v2/accounts/${accountId}/reports/conversation_traffic`)

// Summary Reports
api.get(`api/v2/accounts/${accountId}/reports/summary`)
api.get(`api/v2/accounts/${accountId}/reports/agents`)
api.get(`api/v2/accounts/${accountId}/reports/teams`)
```

### After (Fixed) ✅
```typescript
// Live Reports
api.get(`api/v1/accounts/${accountId}/v2/live_reports/conversation_metrics`)
api.get(`api/v1/accounts/${accountId}/v2/live_reports/grouped_conversation_metrics`)
api.get(`api/v1/accounts/${accountId}/v2/agents/status`)

// Heatmap Data
api.get(`api/v1/accounts/${accountId}/v2/reports`)
api.get(`api/v1/accounts/${accountId}/v2/reports/conversation_traffic`)

// Summary Reports
api.get(`api/v1/accounts/${accountId}/v2/reports/summary`)
api.get(`api/v1/accounts/${accountId}/v2/reports/agents`)
api.get(`api/v1/accounts/${accountId}/v2/reports/teams`)
```

## Correct API Endpoints

### Live Reports (Real-time Metrics)
```
✅ GET /api/v1/accounts/{id}/v2/live_reports/conversation_metrics
✅ GET /api/v1/accounts/{id}/v2/live_reports/conversation_metrics?team_id=1
✅ GET /api/v1/accounts/{id}/v2/live_reports/grouped_conversation_metrics?group_by=assignee_id
✅ GET /api/v1/accounts/{id}/v2/live_reports/grouped_conversation_metrics?group_by=team_id
```

### Agent Status
```
✅ GET /api/v1/accounts/{id}/v2/agents/status
```

### Heatmap Data (Historical Metrics)
```
✅ GET /api/v1/accounts/{id}/v2/reports?metric=conversations_count&since={unix}&until={unix}&group_by=hour
✅ GET /api/v1/accounts/{id}/v2/reports?metric=conversations_count&since={unix}&until={unix}&group_by=hour&type=inbox&id=1
✅ GET /api/v1/accounts/{id}/v2/reports?metric=resolutions_count&since={unix}&until={unix}&group_by=hour
```

### CSV Download
```
✅ GET /api/v1/accounts/{id}/v2/reports/conversation_traffic?days_before=7&timezone_offset=-5
```

### Summary Reports
```
✅ GET /api/v1/accounts/{id}/v2/reports/summary
✅ GET /api/v1/accounts/{id}/v2/reports/agents
✅ GET /api/v1/accounts/{id}/v2/reports/teams
✅ GET /api/v1/accounts/{id}/v2/reports/labels
✅ GET /api/v1/accounts/{id}/v2/reports/inboxes
```

## Testing

After this fix, all API requests should work:

1. **Open browser DevTools → Network tab**
2. **Interact with Reports Overview page**
3. **Verify URLs match the pattern**: `/api/v1/accounts/{id}/v2/...`

**Expected Console Output**:
```
🔄 Fetching account conversation metrics with params: {}
   → GET /api/v1/accounts/1/v2/live_reports/conversation_metrics
✅ Account conversation metrics fetched: { open: 42, unattended: 5, ... }

🔄 Fetching agent status
   → GET /api/v1/accounts/1/v2/agents/status
✅ Agent status fetched: { online: 10, busy: 3, offline: 2 }

🔄 Fetching heatmap data
   → GET /api/v1/accounts/1/v2/reports?metric=conversations_count&...
✅ Heatmap data fetched successfully
```

## Why This Route Structure?

Laravel's route structure follows this pattern:
1. **API Version Prefix**: `/api/v1/` (set in bootstrap/app.php)
2. **Account Scope**: `accounts/{account}/` (with middleware)
3. **Feature Version**: `v2/` (for new reporting features)
4. **Resource**: `reports/`, `live_reports/`, `agents/`

This allows:
- ✅ Account-level access control via middleware
- ✅ Versioning of specific features (v2 reports vs v1 reports)
- ✅ Clear separation between real-time (live_reports) and historical (reports) data
- ✅ Consistent with Rails API structure

## Related Files

- ✅ `laravel-svelte-port/svelte-ui/src/lib/api/reports.ts` - Fixed all endpoint paths
- ✅ `laravel-svelte-port/laravel/routes/api.php` - Route definitions (lines 535-572)
- ✅ `laravel-svelte-port/svelte-ui/src/lib/api/client.ts` - API client with case conversion

## Status: ✅ FIXED

All API endpoint paths now correctly match Laravel's route structure. The Reports Overview feature should now successfully fetch data from the backend.
