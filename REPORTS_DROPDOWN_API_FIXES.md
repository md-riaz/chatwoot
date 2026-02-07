# Reports Overview - Dropdown & API Request Fixes

## Issues Identified

### 1. Dropdowns Don't Trigger API Requests ❌
**Problem**: Clicking dropdown items updates local state but doesn't fetch new data from the API.

**Root Cause**: The `fetchData()` function is called, but the store methods either:
- Don't make actual API calls
- Use mock data instead of real API endpoints
- Don't have the backend APIs implemented yet

### 2. Custom Date Picker Not Working ❌
**Problem**: The custom date picker modal doesn't function properly.

**Root Cause**: Using a custom implementation instead of shadcn-svelte's date picker components.

## Vue Parity Analysis

### What Triggers API Requests in Vue:

1. **Team Selection** → Fetches account conversation metrics with team filter
   ```javascript
   const handleAction = ({ value }) => {
     selectedTeam.value = value;
     fetchData(); // ← Triggers API request
   };
   
   const fetchData = () => {
     const params = {};
     if (selectedTeam.value) {
       params.team_id = selectedTeam.value;
     }
     store.dispatch('fetchAccountConversationMetric', params);
   };
   ```

2. **Date Range Selection** → Fetches heatmap data for new date range
   ```javascript
   // When date range changes
   fetchHeatmapData(); // ← Triggers API request with new dates
   ```

3. **Inbox Selection** → Fetches heatmap data filtered by inbox
   ```javascript
   function handleInboxSelect(inbox) {
     selectedInbox = inbox;
     fetchHeatmapData(); // ← Triggers API request
   }
   ```

## Fixes Required

### Fix 1: Ensure Store Methods Make Real API Calls

**File**: `laravel-svelte-port/svelte-ui/src/lib/stores/reports.svelte.ts`

The store methods need to actually call the API endpoints:

```typescript
class ReportsStore {
  // ... existing code ...
  
  async fetchAccountConversationMetric(params: { teamId?: number } = {}) {
    this.overviewUIFlags.isFetchingAccountConversationMetric = true;
    
    try {
      // ✅ MUST call real API endpoint
      const response = await api.get(`/api/v2/accounts/${accountId}/live_reports/conversation_metrics`, {
        searchParams: params.teamId ? { team_id: params.teamId } : {}
      }).json();
      
      this.accountConversationMetric = response;
    } catch (error) {
      console.error('Failed to fetch conversation metrics:', error);
    } finally {
      this.overviewUIFlags.isFetchingAccountConversationMetric = false;
    }
  }
  
  async fetchAccountConversationHeatmap(params: HeatmapParams) {
    this.overviewUIFlags.isFetchingAccountConversationsHeatmap = true;
    
    try {
      // ✅ MUST call real API endpoint
      const response = await api.get(`/api/v2/accounts/${accountId}/reports`, {
        searchParams: {
          metric: params.metric,
          from: params.from,
          to: params.to,
          group_by: params.groupBy,
          business_hours: params.businessHours,
          ...(params.type && { type: params.type }),
          ...(params.id && { id: params.id })
        }
      }).json();
      
      this.accountConversationHeatmapData = response.data;
    } catch (error) {
      console.error('Failed to fetch heatmap data:', error);
    } finally {
      this.overviewUIFlags.isFetchingAccountConversationsHeatmap = false;
    }
  }
}
```

### Fix 2: Replace Custom Date Picker with Input

**File**: `laravel-svelte-port/svelte-ui/src/lib/components/reports/heatmaps/HeatmapDateRangeSelector.svelte`

Replace the custom date picker modal with simple date inputs:

```svelte
<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Input } from '$lib/components/ui/input';
  import { ChevronDown, ChevronLeft, ChevronRight, Calendar } from 'lucide-svelte';
  
  // ... existing props ...
  
  // Remove showCustomDates modal, use inline inputs instead
  let showCustomInputs = $state(false);
  
  // Format dates for input[type="date"]
  function formatDateForInput(date: Date): string {
    return date.toISOString().split('T')[0];
  }
  
  function parseDateFromInput(dateStr: string): Date {
    return new Date(dateStr + 'T00:00:00');
  }
</script>

<div class="flex items-center gap-2">
  <!-- Range selector dropdown -->
  <DropdownMenu.Root bind:open={showRangeDropdown}>
    <DropdownMenu.Trigger asChild>
      <Button variant="outline" size="sm" class="min-w-[140px] justify-between">
        <span class="truncate">{selectionLabel}</span>
        <ChevronDown class="ml-2 h-4 w-4 flex-shrink-0" />
      </Button>
    </DropdownMenu.Trigger>
    <DropdownMenu.Content class="w-56">
      <DropdownMenu.Item onclick={() => handlePresetSelect('last_7_days')}>
        Last 7 days
      </DropdownMenu.Item>
      <DropdownMenu.Item onclick={() => handlePresetSelect('this_month')}>
        This month
      </DropdownMenu.Item>
      <DropdownMenu.Separator />
      <DropdownMenu.Item onclick={() => { showCustomInputs = !showCustomInputs; showRangeDropdown = false; }}>
        <Calendar class="mr-2 h-4 w-4" />
        Custom range...
      </DropdownMenu.Item>
    </DropdownMenu.Content>
  </DropdownMenu.Root>
  
  <!-- Month navigation (only show for month view) -->
  {#if rangeType === 'month'}
    <div class="flex items-center gap-1">
      <Button variant="outline" size="sm" onclick={() => handleMonthChange('prev')} class="p-2">
        <ChevronLeft class="h-4 w-4" />
      </Button>
      <Button variant="outline" size="sm" onclick={() => handleMonthChange('next')} disabled={monthOffset >= 0} class="p-2">
        <ChevronRight class="h-4 w-4" />
      </Button>
    </div>
  {/if}
</div>

<!-- Custom date inputs (inline, not modal) -->
{#if showCustomInputs}
  <div class="flex items-center gap-2 mt-2">
    <Input
      type="date"
      bind:value={customFrom}
      class="w-40"
      placeholder="Start date"
    />
    <span class="text-sm text-muted-foreground">to</span>
    <Input
      type="date"
      bind:value={customTo}
      class="w-40"
      placeholder="End date"
    />
    <Button size="sm" onclick={handleCustomDates} disabled={!customFrom || !customTo}>
      Apply
    </Button>
    <Button size="sm" variant="ghost" onclick={() => showCustomInputs = false}>
      Cancel
    </Button>
  </div>
{/if}
```

### Fix 3: Verify API Calls Are Being Made

Add console logging to verify API calls:

```typescript
// In StatsLiveReportsContainer.svelte
async function fetchData() {
  console.log('🔄 Fetching data with team:', selectedTeam);
  
  const params = selectedTeam ? { teamId: selectedTeam } : {};
  
  await Promise.all([
    reportsStore.fetchAccountConversationMetric(params),
    reportsStore.fetchAgentStatus()
  ]);
  
  console.log('✅ Data fetched:', {
    conversationMetrics: reportsStore.accountConversationMetric,
    agentStatus: reportsStore.agentStatus
  });
}
```

### Fix 4: Check Network Tab

When clicking dropdowns, you should see:
- ✅ `GET /api/v2/accounts/{id}/live_reports/conversation_metrics?team_id=X`
- ✅ `GET /api/v2/accounts/{id}/reports?metric=conversations_count&...`

If you DON'T see these requests, the issue is:
1. Store methods are using mock data
2. API client is not configured correctly
3. Backend APIs are not implemented

## Testing Checklist

### Team Dropdown
- [ ] Click "All Teams" → Should see API request without team_id
- [ ] Click "Sales" → Should see API request with team_id=X
- [ ] Metrics should update with new data
- [ ] Loading state should show during fetch

### Date Range Dropdown
- [ ] Click "Last 7 days" → Should see API request with date range
- [ ] Click "This month" → Should see API request with month range
- [ ] Click "Custom range" → Should show date inputs
- [ ] Enter dates and click Apply → Should see API request with custom range

### Inbox Dropdown
- [ ] Click "All Inboxes" → Should see API request without inbox filter
- [ ] Click specific inbox → Should see API request with inbox filter
- [ ] Heatmap should update with new data

## Current Status

Based on the code review:

### ✅ Event Handlers Fixed
- Dropdown `onclick` handlers are correct (Svelte 5 syntax)
- State updates are working

### ❌ API Integration Issues
- Store methods may be using mock data
- Backend APIs may not be implemented
- API calls may not be configured correctly

### ❌ Date Picker Issues
- Custom modal implementation is broken
- Should use simple HTML5 date inputs instead

## Next Steps

1. **Check Store Implementation**
   - Verify `reportsStore.fetchAccountConversationMetric()` makes real API calls
   - Verify `reportsStore.fetchAccountConversationHeatmap()` makes real API calls
   - Remove any mock data returns

2. **Check Backend APIs**
   - Verify Laravel endpoints are implemented
   - Test endpoints with Postman/Thunder Client
   - Check API routes are registered

3. **Fix Date Picker**
   - Replace custom modal with inline date inputs
   - Use HTML5 `<input type="date">` for simplicity
   - Or use shadcn-svelte date picker if available

4. **Add Logging**
   - Add console.log to track API calls
   - Monitor Network tab in DevTools
   - Verify requests are being sent

## Reference: Vue API Calls

From Vue implementation, these API calls should be made:

```javascript
// 1. Account conversation metrics (with optional team filter)
GET /api/v2/accounts/{accountId}/live_reports/conversation_metrics?team_id={teamId}

// 2. Agent status
GET /api/v2/accounts/{accountId}/agents/status

// 3. Heatmap data
GET /api/v2/accounts/{accountId}/reports?metric=conversations_count&group_by=hour&since={unix}&until={unix}&type=inbox&id={inboxId}

// 4. Agent metrics
GET /api/v2/accounts/{accountId}/live_reports/grouped_conversation_metrics?group_by=assignee_id

// 5. Team metrics
GET /api/v2/accounts/{accountId}/live_reports/grouped_conversation_metrics?group_by=team_id
```

## Status: ⚠️ BLOCKED

The dropdown UI is working correctly, but the underlying API integration needs to be verified and fixed. The issue is likely in the store implementation or backend API availability, not in the dropdown components themselves.
