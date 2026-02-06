# Vue to SvelteKit Reports Overview Page - Parity Analysis Report

**Date**: February 5, 2026  
**Page**: `/app/accounts/[accountId]/reports/overview`  
**Status**: ⚠️ **SIGNIFICANT GAPS IDENTIFIED**

---

## Executive Summary

The SvelteKit reports page currently implements **~30% of the Vue reports overview functionality**. Major missing components include:
- ❌ Live refresh functionality (60-second auto-refresh)
- ❌ Conversation & Resolution heatmaps (24-hour × 7-day grid visualization)
- ❌ Agent performance table with pagination
- ❌ Team performance table with pagination
- ❌ Live indicator badges
- ❌ Inbox filtering for heatmaps
- ❌ CSV export functionality
- ❌ Advanced date range selectors (month view, presets)
- ❌ Team filtering for account metrics

---

## 1. Feature Comparison Matrix

| Feature | Vue Implementation | SvelteKit Implementation | Status | Priority |
|---------|-------------------|-------------------------|--------|----------|
| **Account Metrics** | ✅ Full metrics with team filter | ✅ Basic metrics only | 🟡 Partial | HIGH |
| **Agent Status Metrics** | ✅ Online/Busy/Offline counts | ❌ Not implemented | 🔴 Missing | HIGH |
| **Live Refresh** | ✅ 60-second auto-refresh | ❌ Manual refresh only | 🔴 Missing | HIGH |
| **Conversation Heatmap** | ✅ 24h×7d grid with colors | ❌ Not implemented | 🔴 Missing | CRITICAL |
| **Resolution Heatmap** | ✅ 24h×7d grid with colors | ❌ Not implemented | 🔴 Missing | CRITICAL |
| **Agent Performance Table** | ✅ Paginated with sorting | ✅ Simple list (top 5) | 🟡 Partial | HIGH |
| **Team Performance Table** | ✅ Paginated with sorting | ✅ Simple list (top 5) | 🟡 Partial | HIGH |
| **Date Range Selector** | ✅ Advanced (presets, month) | ✅ Basic date inputs | 🟡 Partial | MEDIUM |
| **Inbox Filtering** | ✅ Dropdown filter | ❌ Not implemented | 🔴 Missing | MEDIUM |
| **CSV Export** | ✅ Download heatmap data | ❌ Not implemented | 🔴 Missing | MEDIUM |
| **Live Indicator Badge** | ✅ "LIVE" badge on cards | ❌ Not implemented | 🔴 Missing | LOW |
| **Loading States** | ✅ Skeleton loaders | ✅ Basic pulse animation | 🟢 Complete | - |
| **Empty States** | ✅ Custom messages | ❌ Not implemented | 🔴 Missing | LOW |
| **UI Persistence** | ✅ Page size saved | ❌ Not implemented | 🔴 Missing | LOW |

**Legend**: 🟢 Complete | 🟡 Partial | 🔴 Missing

---

## 2. Component Architecture Comparison

### Vue Architecture (Current)
```
LiveReports.vue (Main Page)
├── ReportHeader.vue
├── StatsLiveReportsContainer.vue
│   ├── MetricCard.vue
│   │   └── Team filter dropdown
│   └── Account + Agent Status metrics
├── ConversationHeatmapContainer.vue
│   ├── BaseHeatmapContainer.vue
│   │   ├── MetricCard.vue
│   │   ├── HeatmapDateRangeSelector.vue
│   │   ├── Inbox filter dropdown
│   │   ├── Download button
│   │   └── BaseHeatmap.vue (24h×7d grid)
├── ResolutionHeatmapContainer.vue (similar structure)
├── AgentLiveReportContainer.vue
│   ├── MetricCard.vue
│   └── AgentTable.vue (TanStack Vue Table)
│       ├── AgentCell.vue (Avatar + Name + Email)
│       └── Pagination.vue
└── TeamLiveReportContainer.vue
    ├── MetricCard.vue
    └── TeamTable.vue (TanStack Vue Table)
        └── Pagination.vue
```

### SvelteKit Architecture (Current)
```
+page.svelte (Main Page)
├── Header (inline)
│   ├── Date inputs (basic)
│   └── Refresh button
├── MetricsCards.svelte
│   └── 4 metric cards (basic)
├── Top Agents (inline)
│   └── Simple list (top 5)
└── Top Teams (inline)
    └── Simple list (top 5)
```

**Gap**: SvelteKit lacks modular component structure and advanced visualizations.

---

## 3. State Management Comparison

### Vue (Vuex Store)
```javascript
// store/modules/reports.js
state: {
  overview: {
    uiFlags: {
      isFetchingAccountConversationMetric: false,
      isFetchingAccountConversationsHeatmap: false,
      isFetchingAccountResolutionsHeatmap: false,
      isFetchingAgentConversationMetric: false,
      isFetchingTeamConversationMetric: false,
    },
    accountConversationMetric: {},
    accountConversationHeatmap: [],
    accountResolutionHeatmap: [],
    agentConversationMetric: [],
    teamConversationMetric: [],
  }
}

actions: {
  fetchAccountConversationMetric,
  fetchAccountConversationHeatmap,
  fetchAccountResolutionHeatmap,
  fetchAgentConversationMetric,
  fetchTeamConversationMetric,
  downloadAccountConversationHeatmap
}
```

### SvelteKit (Runes Store)
```typescript
// stores/reports.svelte.ts
class ReportsStore {
  state = $state({
    conversationMetrics: null,
    agentMetrics: [],
    teamMetrics: [],
    filters: { since, until },
    isLoading: false,
    error: null
  });
  
  // Missing:
  // - accountConversationHeatmap
  // - accountResolutionHeatmap
  // - agentConversationMetric (live)
  // - teamConversationMetric (live)
  // - Individual loading flags per metric
}
```

**Gap**: SvelteKit store missing heatmap data, live metrics, and granular loading states.

---

## 4. API Integration Comparison

### Vue API Endpoints
```javascript
// liveReports.js (v2 API)
getConversationMetric(params)           // Account metrics
getGroupedConversations({ groupBy })    // Agent/Team metrics

// reports.js (v2 API)
getReports({ metric, from, to, groupBy: 'hour' })  // Heatmap data
getConversationTrafficCSV({ daysBefore })          // CSV export
```

### SvelteKit API Endpoints
```typescript
// reports.ts
getAccountReports(accountId, filters)   // ✅ Implemented
getAgentReports(accountId, filters)     // ✅ Implemented
getTeamReports(accountId, filters)      // ✅ Implemented

// Missing:
// - Live conversation metrics endpoint
// - Heatmap data endpoint (hourly grouped)
// - CSV export endpoint
// - Agent status metrics endpoint
```

**Gap**: SvelteKit missing live metrics and heatmap endpoints.

---

## 5. Missing Components Deep Dive

### 5.1 Heatmap Visualization (CRITICAL)

**Vue Implementation**:
- 24-hour columns (0-23) × 7-day rows
- Color intensity based on quantile intervals (6 levels)
- Two color schemes: blue (conversations), green (resolutions)
- Tooltip on hover showing exact values
- Date range selector with presets (Last 7 days, This month, Custom)
- Inbox filtering dropdown
- CSV download functionality
- Memoized rendering with `v-memo` for performance
- `content-visibility: auto` for optimization

**SvelteKit Status**: ❌ **Not implemented**

**Required Components**:
```
BaseHeatmap.svelte
├── HeatmapGrid.svelte (24×7 grid)
├── HeatmapTooltip.svelte
├── HeatmapLegend.svelte (color scale)
└── HeatmapDateRangeSelector.svelte

BaseHeatmapContainer.svelte
├── MetricCard wrapper
├── Date range controls
├── Inbox filter dropdown
└── Download button
```

**Data Structure**:
```typescript
interface HeatmapData {
  timestamp: number;  // Unix timestamp
  value: number;      // Metric value
}

// Grouped by day, then by hour (0-23)
type HeatmapDataset = HeatmapData[];
```

---

### 5.2 Live Refresh System (HIGH)

**Vue Implementation**:
```javascript
// composables/useLiveRefresh.js
export const useLiveRefresh = (callback, interval = 60000) => {
  const timeoutId = ref(null);
  
  const startRefetching = () => {
    timeoutId.value = setTimeout(async () => {
      await callback();
      startRefetching();  // Recursive
    }, interval);
  };
  
  onBeforeUnmount(() => clearTimeout(timeoutId.value));
  
  return { startRefetching, stopRefetching };
};

// Usage in components
const { startRefetching } = useLiveRefresh(fetchData);
onMounted(() => {
  fetchData();
  startRefetching();
});
```

**SvelteKit Status**: ❌ **Not implemented**

**Required Implementation**:
```typescript
// lib/composables/useLiveRefresh.svelte.ts
export function useLiveRefresh(
  callback: () => Promise<void>,
  interval: number = 60000
) {
  let timeoutId = $state<number | null>(null);
  
  function startRefetching() {
    timeoutId = setTimeout(async () => {
      await callback();
      startRefetching();
    }, interval);
  }
  
  function stopRefetching() {
    if (timeoutId) clearTimeout(timeoutId);
  }
  
  $effect(() => {
    return () => stopRefetching();  // Cleanup
  });
  
  return { startRefetching, stopRefetching };
}
```

---

### 5.3 Agent/Team Performance Tables (HIGH)

**Vue Implementation**:
- TanStack Vue Table with pagination
- Columns: Agent/Team, Open, Unattended
- Sorting: By open conversations (desc), then by name (asc)
- Page size selector (10, 25, 50, 100)
- Page size persisted to UI settings
- Avatar display for agents
- Loading states with spinner
- Empty states with custom messages

**SvelteKit Current**:
- Simple card list (top 5 only)
- No pagination
- No sorting
- No avatar display
- No empty states

**Required Components**:
```
AgentTable.svelte
├── DataTable.svelte (reuse existing)
├── AgentCell.svelte (Avatar + Name + Email)
├── Pagination controls
└── Page size selector

TeamTable.svelte
├── DataTable.svelte (reuse existing)
├── Pagination controls
└── Page size selector
```

**Data Structure**:
```typescript
interface AgentMetric {
  assignee_id: number;
  open: number;
  unattended: number;
}

interface Agent {
  id: number;
  name: string;
  available_name: string;
  email: string;
  thumbnail: string;
  availability_status: 'online' | 'busy' | 'offline';
}
```

---

### 5.4 Advanced Date Range Selector (MEDIUM)

**Vue Implementation**:
- Preset options: Last 7 days, This month, Custom
- Month navigation (previous/next month)
- Keeps relative presets aligned during live refresh
- Resolves active range dynamically

**SvelteKit Current**:
- Basic date inputs only

**Required Component**:
```svelte
<!-- HeatmapDateRangeSelector.svelte -->
<script>
  let rangeType = $state<'preset' | 'month' | 'custom'>('preset');
  let selectedPreset = $state<'last_7_days' | 'this_month'>('last_7_days');
  let monthOffset = $state(0);  // 0 = current, -1 = previous, etc.
  
  // Emit: from, to, daysNum, rangeTypeChange, monthOffsetChange
</script>
```

---

### 5.5 Metric Card Enhancements (MEDIUM)

**Vue Implementation**:
- Live indicator badge (green dot + "LIVE" text)
- Control slot for filters/buttons
- Loading state with custom message
- Flexible header slot

**SvelteKit Current**:
- Basic card wrapper
- No live indicator
- No control slot

**Required Updates**:
```svelte
<!-- MetricCard.svelte -->
<script>
  let { 
    header, 
    isLive = false,
    isLoading = false,
    loadingMessage = '',
    children,
    control  // Slot for filters/buttons
  } = $props();
</script>

<Card.Root>
  <Card.Header>
    <div class="flex items-center gap-2">
      <h5>{header}</h5>
      {#if isLive}
        <span class="live-badge">
          <span class="live-dot"></span>
          LIVE
        </span>
      {/if}
    </div>
    {#if control}
      {@render control()}
    {/if}
  </Card.Header>
  <Card.Content>
    {#if isLoading}
      <Spinner />
      <span>{loadingMessage}</span>
    {:else}
      {@render children()}
    {/if}
  </Card.Content>
</Card.Root>
```

---

## 6. Data Flow Comparison

### Vue Data Flow
```
Component Mount
  ↓
Dispatch Vuex Actions
  ↓
API Calls (liveReports.js, reports.js)
  ↓
Commit Mutations
  ↓
Update Store State
  ↓
Getters Return Reactive Data
  ↓
Components Re-render
  ↓
Live Refresh (60s) → Repeat
```

### SvelteKit Data Flow (Current)
```
Component Mount
  ↓
Call Store Methods
  ↓
API Calls (reports.ts)
  ↓
Update Store State ($state)
  ↓
Derived Values Update
  ↓
Components Re-render
  ↓
Manual Refresh Only
```

**Gap**: No automatic live refresh loop.

---

## 7. Styling & Design Comparison

### Vue Styling
- **Framework**: Tailwind CSS with custom design tokens
- **Color Palette**: `n-slate`, `n-blue`, `n-teal` (custom scale)
- **Dark Mode**: Full support with `dark:` variants
- **Layout**: Flexbox + CSS Grid
- **Responsive**: Mobile-first with `md:` breakpoints
- **Animations**: `animate-loader-pulse` for skeletons

### SvelteKit Styling
- **Framework**: Tailwind CSS (standard palette)
- **Color Palette**: Standard Tailwind colors
- **Dark Mode**: Partial support
- **Layout**: Flexbox
- **Responsive**: Basic responsive design
- **Animations**: `animate-pulse` for loading

**Gap**: SvelteKit missing custom design tokens and advanced animations.

---

## 8. Performance Optimizations Comparison

### Vue Optimizations
- `v-memo` directives on heatmap rows (memoization)
- `content-visibility: auto` for heatmap rendering
- Pagination to limit table rows
- Memoized quantile calculation (`useMemoize`)
- Lazy loading with spinners

### SvelteKit Optimizations
- Basic loading states
- No memoization
- No virtual scrolling
- No lazy loading

**Gap**: SvelteKit missing advanced performance optimizations.

---

## 9. Migration Roadmap

### Phase 1: Core Infrastructure (Week 1-2)
**Priority**: CRITICAL

1. **Live Refresh System**
   - Create `useLiveRefresh.svelte.ts` composable
   - Implement 60-second auto-refresh
   - Add start/stop controls
   - Test cleanup on unmount

2. **Store Enhancements**
   - Add heatmap data state
   - Add live metrics state
   - Add granular loading flags
   - Add agent status metrics

3. **API Endpoints**
   - Implement live metrics endpoint
   - Implement heatmap data endpoint (hourly grouped)
   - Implement agent status endpoint
   - Add CSV export endpoint

**Deliverables**:
- ✅ Live refresh working
- ✅ Store structure matches Vue
- ✅ API parity with Vue

---

### Phase 2: Heatmap Visualization (Week 3-4)
**Priority**: CRITICAL

1. **BaseHeatmap Component**
   - Create 24×7 grid layout
   - Implement quantile-based color intensity
   - Add tooltip on hover
   - Support blue/green color schemes
   - Add loading skeleton

2. **HeatmapDateRangeSelector Component**
   - Preset options (Last 7 days, This month)
   - Month navigation
   - Custom date range
   - Dynamic range resolution

3. **BaseHeatmapContainer Component**
   - Wrap heatmap with MetricCard
   - Add date range controls
   - Add inbox filter dropdown
   - Add download button
   - Implement CSV export

4. **Heatmap Instances**
   - ConversationHeatmapContainer (blue)
   - ResolutionHeatmapContainer (green)

**Deliverables**:
- ✅ Conversation heatmap working
- ✅ Resolution heatmap working
- ✅ Date range filtering
- ✅ Inbox filtering
- ✅ CSV export

---

### Phase 3: Performance Tables (Week 5)
**Priority**: HIGH

1. **AgentTable Component**
   - Integrate with existing DataTable
   - Create AgentCell component (Avatar + Name + Email)
   - Add pagination controls
   - Add page size selector
   - Implement sorting (open desc, name asc)
   - Add loading states
   - Add empty states

2. **TeamTable Component**
   - Similar to AgentTable
   - Simpler cell (name only)
   - Same pagination/sorting

3. **UI Persistence**
   - Save page size to localStorage
   - Restore on mount

**Deliverables**:
- ✅ Agent table with pagination
- ✅ Team table with pagination
- ✅ Sorting working
- ✅ UI persistence

---

### Phase 4: Enhanced Metrics & Filters (Week 6)
**Priority**: MEDIUM

1. **StatsLiveReportsContainer Enhancements**
   - Add agent status metrics section
   - Add team filter dropdown
   - Update MetricCard with live badge
   - Add control slot for filters

2. **MetricCard Component Updates**
   - Add live indicator badge
   - Add control slot
   - Enhance loading states
   - Add custom loading messages

3. **Inbox Filtering**
   - Create inbox dropdown component
   - Integrate with heatmaps
   - Add "All Inboxes" option

**Deliverables**:
- ✅ Agent status metrics
- ✅ Team filtering
- ✅ Live badges
- ✅ Inbox filtering

---

### Phase 5: Polish & Testing (Week 7)
**Priority**: LOW

1. **Empty States**
   - Add custom empty state messages
   - Add illustrations/icons

2. **Error Handling**
   - Add error boundaries
   - Add retry mechanisms
   - Add error messages

3. **Accessibility**
   - Add ARIA labels
   - Test keyboard navigation
   - Test screen readers

4. **Testing**
   - Unit tests for components
   - Integration tests for data flow
   - E2E tests for user flows

**Deliverables**:
- ✅ Empty states
- ✅ Error handling
- ✅ Accessibility compliance
- ✅ Test coverage

---

## 10. Recommended Component Structure

```
src/routes/app/accounts/[accountId]/reports/overview/
└── +page.svelte (Main page)

src/lib/components/reports/
├── overview/
│   ├── StatsLiveReportsContainer.svelte
│   ├── AgentLiveReportContainer.svelte
│   ├── TeamLiveReportContainer.svelte
│   ├── AgentTable.svelte
│   ├── AgentCell.svelte
│   ├── TeamTable.svelte
│   └── MetricCard.svelte (enhanced)
├── heatmaps/
│   ├── BaseHeatmap.svelte
│   ├── BaseHeatmapContainer.svelte
│   ├── ConversationHeatmapContainer.svelte
│   ├── ResolutionHeatmapContainer.svelte
│   ├── HeatmapTooltip.svelte
│   ├── HeatmapLegend.svelte
│   └── HeatmapDateRangeSelector.svelte
└── shared/
    ├── ReportHeader.svelte
    ├── LiveBadge.svelte
    └── EmptyState.svelte

src/lib/composables/
└── useLiveRefresh.svelte.ts

src/lib/stores/
└── reports.svelte.ts (enhanced)

src/lib/api/
└── reports.ts (enhanced)
```

---

## 11. API Endpoint Requirements

### Required Laravel API Endpoints

1. **Live Conversation Metrics**
   ```
   GET /api/v2/accounts/{accountId}/live_reports/conversation_metrics
   Query: ?team_id={teamId}
   Response: {
     open: number,
     unattended: number,
     unassigned: number,
     pending: number
   }
   ```

2. **Live Agent Metrics**
   ```
   GET /api/v2/accounts/{accountId}/live_reports/grouped_conversation_metrics
   Query: ?group_by=assignee_id
   Response: [
     { assignee_id: number, open: number, unattended: number }
   ]
   ```

3. **Live Team Metrics**
   ```
   GET /api/v2/accounts/{accountId}/live_reports/grouped_conversation_metrics
   Query: ?group_by=team_id
   Response: [
     { team_id: number, open: number, unattended: number }
   ]
   ```

4. **Agent Status Metrics**
   ```
   GET /api/v2/accounts/{accountId}/agents/status
   Response: {
     online: number,
     busy: number,
     offline: number
   }
   ```

5. **Heatmap Data (Hourly)**
   ```
   GET /api/v2/accounts/{accountId}/reports
   Query: ?metric=conversations_count&group_by=hour&since={unix}&until={unix}&type=inbox&id={inboxId}
   Response: {
     data: [
       { timestamp: number, value: number }
     ]
   }
   ```

6. **CSV Export**
   ```
   GET /api/v2/accounts/{accountId}/reports/conversation_traffic
   Query: ?days_before=6&timezone_offset=-5
   Response: CSV file
   ```

---

## 12. Best Practices for Migration

### 12.1 Use Existing SvelteKit Patterns

✅ **DO**:
- Use Svelte 5 runes (`$state`, `$derived`, `$effect`)
- Use existing `DataTable.svelte` component
- Use shadcn-svelte UI components
- Follow camelCase naming in frontend (API transformer handles snake_case)
- Use `$lib/api/client.ts` for API calls (automatic case conversion)

❌ **DON'T**:
- Don't manually convert camelCase/snake_case
- Don't create custom table components (reuse DataTable)
- Don't bypass API client (transformations won't work)
- Don't use Vuex patterns (use Svelte runes)

### 12.2 Component Modularity

✅ **DO**:
- Create small, reusable components
- Use slots for flexibility
- Separate container and presentational components
- Keep business logic in stores

❌ **DON'T**:
- Don't create monolithic components
- Don't mix data fetching with presentation
- Don't duplicate code across components

### 12.3 Performance Optimization

✅ **DO**:
- Use `$derived` for computed values
- Implement pagination for large datasets
- Use loading skeletons
- Lazy load heavy components

❌ **DON'T**:
- Don't render all data at once
- Don't skip loading states
- Don't ignore performance metrics

### 12.4 State Management

✅ **DO**:
- Use class-based stores with runes
- Keep store methods simple and focused
- Use derived getters for computed values
- Handle errors gracefully

❌ **DON'T**:
- Don't mutate state directly outside store
- Don't create circular dependencies
- Don't ignore error states

---

## 13. Testing Strategy

### Unit Tests
```typescript
// BaseHeatmap.test.ts
describe('BaseHeatmap', () => {
  it('renders 24 columns', () => {});
  it('renders correct number of rows', () => {});
  it('applies correct color intensity', () => {});
  it('shows tooltip on hover', () => {});
});

// useLiveRefresh.test.ts
describe('useLiveRefresh', () => {
  it('calls callback after interval', () => {});
  it('stops on cleanup', () => {});
  it('handles errors gracefully', () => {});
});
```

### Integration Tests
```typescript
// reports-overview.test.ts
describe('Reports Overview Page', () => {
  it('fetches all data on mount', () => {});
  it('refreshes data every 60 seconds', () => {});
  it('filters by team', () => {});
  it('exports CSV', () => {});
});
```

### E2E Tests
```typescript
// reports-overview.e2e.ts
test('user can view live reports', async ({ page }) => {
  await page.goto('/app/accounts/1/reports/overview');
  await expect(page.locator('.live-badge')).toBeVisible();
  await expect(page.locator('.heatmap-grid')).toBeVisible();
});
```

---

## 14. Estimated Effort

| Phase | Components | Effort | Dependencies |
|-------|-----------|--------|--------------|
| Phase 1: Infrastructure | 3 files | 2 weeks | Laravel API |
| Phase 2: Heatmaps | 7 components | 2 weeks | Phase 1 |
| Phase 3: Tables | 4 components | 1 week | Phase 1 |
| Phase 4: Enhancements | 5 components | 1 week | Phase 1-3 |
| Phase 5: Polish | Testing/QA | 1 week | Phase 1-4 |
| **Total** | **19 components** | **7 weeks** | - |

**Team Size**: 1-2 developers  
**Complexity**: Medium-High  
**Risk**: Low (Vue implementation is reference)

---

## 15. Success Criteria

### Functional Parity
- ✅ All Vue features implemented
- ✅ Live refresh working (60s interval)
- ✅ Heatmaps rendering correctly
- ✅ Tables with pagination/sorting
- ✅ Filters working (team, inbox, date)
- ✅ CSV export functional

### Performance
- ✅ Page load < 2s
- ✅ Heatmap render < 500ms
- ✅ Live refresh < 1s
- ✅ No memory leaks

### User Experience
- ✅ Loading states for all async operations
- ✅ Empty states with helpful messages
- ✅ Error handling with retry
- ✅ Responsive design (mobile/tablet/desktop)
- ✅ Accessibility compliant (WCAG 2.1 AA)

### Code Quality
- ✅ TypeScript strict mode
- ✅ 80%+ test coverage
- ✅ No console errors/warnings
- ✅ Follows SvelteKit patterns
- ✅ Documented components

---

## 16. Conclusion

The SvelteKit reports overview page requires **significant development** to achieve parity with the Vue implementation. The most critical gaps are:

1. **Heatmap visualization** - Core feature, completely missing
2. **Live refresh system** - Essential for "live" reports
3. **Performance tables** - Need pagination and full agent/team data

**Recommendation**: Follow the 7-week migration roadmap, prioritizing Phases 1-2 (infrastructure + heatmaps) as they provide the most user value. Leverage existing SvelteKit patterns and components where possible to accelerate development.

**Next Steps**:
1. Review and approve this analysis
2. Set up Laravel API endpoints (Phase 1 dependency)
3. Begin Phase 1 implementation
4. Conduct weekly progress reviews

---

**Document Version**: 1.0  
**Last Updated**: February 5, 2026  
**Author**: AI Assistant (Kiro)
