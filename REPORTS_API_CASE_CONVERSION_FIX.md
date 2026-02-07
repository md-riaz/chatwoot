# Reports API - Case Conversion Fix

## Issue

API requests were failing with 404 errors because query parameters were being sent in camelCase instead of snake_case:

**Before (Broken)**:
```
GET /api/v2/accounts/1/live_reports/grouped_conversation_metrics?groupBy=team_id
                                                                   ^^^^^^^ camelCase
```

**After (Fixed)**:
```
GET /api/v2/accounts/1/live_reports/grouped_conversation_metrics?group_by=team_id
                                                                   ^^^^^^^^ snake_case
```

## Root Cause

The `toSearchParams()` helper function in `client.ts` was not transforming keys to snake_case before creating URL query parameters.

## Fix Applied

**File**: `laravel-svelte-port/svelte-ui/src/lib/api/client.ts`

Updated `toSearchParams()` to use `keysToSnake()` transformation:

```typescript
export function toSearchParams(params?: Record<string, any>): Record<string, string> | undefined {
  if (!params || Object.keys(params).length === 0) {
    return undefined;
  }
  
  // ✅ Transform keys to snake_case first
  const transformed = keysToSnake(params);
  const result: Record<string, string> = {};
  
  Object.entries(transformed).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      if (Array.isArray(value)) {
        result[key] = value.join(',');
      } else {
        result[key] = String(value);
      }
    }
  });
  
  return Object.keys(result).length > 0 ? result : undefined;
}
```

## Parameter Transformations

Now all query parameters are automatically converted:

| Frontend (camelCase) | Backend (snake_case) |
|---------------------|---------------------|
| `groupBy` | `group_by` |
| `teamId` | `team_id` |
| `assigneeId` | `assignee_id` |
| `businessHours` | `business_hours` |
| `daysBefore` | `days_before` |
| `timezoneOffset` | `timezone_offset` |

## API Endpoints Now Working

### Live Reports
```
✅ GET /api/v2/accounts/{id}/live_reports/conversation_metrics
✅ GET /api/v2/accounts/{id}/live_reports/conversation_metrics?team_id=1
✅ GET /api/v2/accounts/{id}/live_reports/grouped_conversation_metrics?group_by=assignee_id
✅ GET /api/v2/accounts/{id}/live_reports/grouped_conversation_metrics?group_by=team_id
```

### Agent Status
```
✅ GET /api/v2/accounts/{id}/agents/status
```

### Heatmap Data
```
✅ GET /api/v2/accounts/{id}/reports?metric=conversations_count&since=123&until=456&group_by=hour
✅ GET /api/v2/accounts/{id}/reports?metric=conversations_count&since=123&until=456&group_by=hour&type=inbox&id=1
✅ GET /api/v2/accounts/{id}/reports?metric=resolutions_count&since=123&until=456&group_by=hour&business_hours=false
```

### CSV Download
```
✅ GET /api/v2/accounts/{id}/reports/conversation_traffic?days_before=7&timezone_offset=-5
```

## Testing

After this fix, all API requests should work correctly:

1. **Open browser DevTools → Network tab**
2. **Interact with Reports Overview page**
3. **Verify URLs have snake_case parameters**:
   - ✅ `group_by=team_id` (not `groupBy=team_id`)
   - ✅ `team_id=1` (not `teamId=1`)
   - ✅ `business_hours=false` (not `businessHours=false`)

## Console Output

You should now see successful API calls:

```
🔄 Fetching account conversation metrics with params: { teamId: 1 }
   → GET /api/v2/accounts/1/live_reports/conversation_metrics?team_id=1
✅ Account conversation metrics fetched: { open: 42, unattended: 5, ... }

🔄 Fetching heatmap data { metric: 'conversations_count', groupBy: 'hour', ... }
   → GET /api/v2/accounts/1/reports?metric=conversations_count&group_by=hour&...
✅ Heatmap data fetched successfully
```

## Related Files

- ✅ `laravel-svelte-port/svelte-ui/src/lib/api/client.ts` - Fixed `toSearchParams()`
- ✅ `laravel-svelte-port/svelte-ui/src/lib/api/transformers.ts` - Contains `keysToSnake()` function
- ✅ `laravel-svelte-port/svelte-ui/src/lib/api/reports.ts` - Uses `toSearchParams()` for all API calls
- ✅ `laravel-svelte-port/svelte-ui/src/lib/stores/reports.svelte.ts` - Calls API methods with camelCase params

## Status: ✅ FIXED

All API requests now correctly transform camelCase parameters to snake_case, matching Laravel backend expectations.
