# Svelte 5 Migration Fixes Tracker

**Project**: Chatwoot Laravel + SvelteKit Migration  
**Total Issues**: 197 errors + 52 warnings  
**Files Affected**: 74 files  
**Last Updated**: 2026-02-09

---

## 📊 Progress Overview

| Phase | Total | Fixed | Remaining | Status |
|-------|-------|-------|-----------|--------|
| **Phase 1: Critical Syntax** | 15 | 8 | 7 | � In Progress |
| **Phase 2: Component API** | 25 | 5 | 20 | 🟡 In Progress |
| **Phase 3: TypeScript/Imports** | 45 | 0 | 45 | 🔴 Not Started |
| **Phase 4: Type Safety** | 60 | 0 | 60 | 🔴 Not Started |
| **Phase 5: API/Data** | 52 | 0 | 52 | 🔴 Not Started |
| **TOTAL** | **197** | **20** | **177** | **10%** |

---

## 🎯 Phase 1: Critical Svelte 5 Syntax Issues (Priority: CRITICAL)

### 1.1 Event Handler Syntax ✅
**Issue**: Using Svelte 4 `on:event` instead of Svelte 5 `onevent`

- [x] `src/lib/components/reports/overview/AgentTable.svelte:211` - `on:change` → `onchange`
- [x] `src/lib/components/reports/overview/TeamTable.svelte:172` - `on:change` → `onchange`

**Fix Pattern**:
```svelte
<!-- Before -->
<select on:change={(e) => handler(e)}>

<!-- After -->
<select onchange={(e) => handler(e)}>
```

---

### 1.2 Event Modifier Syntax ✅
**Issue**: Invalid event modifier syntax `onsubmit|preventDefault`

- [x] `src/lib/actions/examples/ContactsPage.svelte:391` - Fix event modifier

**Fix Pattern**:
```svelte
<!-- Before -->
<form onsubmit|preventDefault={handleSubmit}>

<!-- After -->
<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
```

---

### 1.3 Reactive Statement Syntax ✅
**Issue**: Using `$:` instead of `$derived` or `$effect`

- [x] `src/routes/ui/[name]/+page.svelte:18` - `$:` → `$derived`

**Fix Pattern**:
```svelte
<!-- Before -->
$: componentName = $page.params.name;

<!-- After -->
const componentName = $derived($page.params.name);
```

---

### 1.4 Deprecated `<svelte:component>` ✅
**Issue**: Using deprecated `<svelte:component>` in runes mode

- [x] `src/lib/components/ui/contact-management/contact-form/contact-form.svelte:197`
- [x] `src/lib/components/ui/contact-management/contact-form/contact-form.svelte:227`
- [x] `src/lib/components/reports/shared/ReportMetricCard.svelte:47`
- [x] `src/lib/components/ui/websocket-status.svelte:52`
- [x] `src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:434`

**Fix Pattern**:
```svelte
<!-- Before -->
<svelte:component this={icon} class="w-3 h-3" />

<!-- After -->
{@const IconComponent = icon}
<IconComponent class="w-3 h-3" />
```

---

### 1.5 Deprecated `<svelte:self>` ✅
**Issue**: Using deprecated `<svelte:self>` instead of self-imports

- [x] `src/lib/components/layout/SidebarMenuItem.svelte:73`
- [x] `src/lib/components/layout/SidebarMenuItem.svelte:116`

**Fix Pattern**:
```svelte
<!-- Before -->
<svelte:self item={child} sub={true} />

<!-- After -->
<script>
  import Self from './SidebarMenuItem.svelte';
</script>
<Self item={child} sub={true} />
```

---

### 1.6 Deprecated `<slot>` ✅
**Issue**: Using `<slot>` instead of `{@render}`

- [x] `src/lib/components/reports/shared/ReportHeader.svelte:35`

**Fix Pattern**:
```svelte
<!-- Before -->
<slot />

<!-- After -->
<script>
  import type { Snippet } from 'svelte';
  let { children }: { children?: Snippet } = $props();
</script>
{@render children?.()}
```

---

### 1.7 State Reference Warnings ✅
**Issue**: Capturing initial value instead of reactive reference

- [x] `src/lib/components/reports/shared/EmptyState.svelte:27` - `icon` reference
- [x] `src/lib/components/ui/carousel/carousel.svelte:24` - `orientation` reference
- [x] `src/lib/components/ui/carousel/carousel.svelte:28` - `opts` reference
- [x] `src/lib/components/ui/carousel/carousel.svelte:29` - `plugins` reference
- [x] `src/lib/components/ui/contact-management/contact-form/contact-form.svelte:20` - `contact` reference
- [x] `src/lib/components/ui/toggle-group/toggle-group.svelte:27` - `variant` reference
- [x] `src/lib/components/ui/toggle-group/toggle-group.svelte:28` - `size` reference
- [x] `src/lib/components/reports/shared/ReportFilters.svelte:26` - `currentFilter` reference
- [x] `src/lib/components/reports/shared/ReportFilters.svelte:27` - `selectedGroupByFilter` reference
- [x] `src/lib/components/reports/shared/WootReports.svelte:52` - `selectedItem` reference

**Fix Pattern**:
```svelte
<!-- Before -->
const IconComponent = iconComponents[icon];

<!-- After -->
const IconComponent = $derived(iconComponents[icon]);
```

---

## 🎯 Phase 2: Component API Issues (Priority: HIGH)

### 2.1 Invalid shadcn-svelte Props ❌

#### Avatar Component
- [ ] `src/lib/components/ui/contact-note/contact-note-item.svelte:23` - Remove `size` prop
- [ ] `src/lib/components/widget/CampaignMessage.svelte:88` - Remove `src` prop

**Fix**: Use Tailwind classes instead
```svelte
<!-- Before -->
<Avatar size="sm" src={url}>

<!-- After -->
<Avatar class="h-8 w-8">
  <AvatarImage src={url} />
</Avatar>
```

#### LoadingSkeleton Component
- [ ] `src/lib/components/reports/csat/CsatMetrics.svelte:38` - Remove `height` prop
- [ ] `src/lib/components/reports/csat/CsatTable.svelte:45` - Remove `height` prop
- [ ] `src/lib/components/reports/shared/ReportChart.svelte:110` - Remove `height` prop
- [ ] `src/lib/components/reports/shared/ReportContainer.svelte:82` - Remove `height` prop

**Fix**: Use Tailwind classes
```svelte
<!-- Before -->
<LoadingSkeleton height="100px" />

<!-- After -->
<div class="h-[100px] animate-pulse bg-muted rounded" />
```

#### DropdownMenu Component
- [ ] `src/lib/components/reports/heatmaps/HeatmapDateRangeSelector.svelte:154` - Remove `asChild` prop
- [ ] `src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:231` - Remove `asChild` prop
- [ ] `src/lib/components/reports/overview/StatsLiveReportsContainer.svelte:85` - Remove `asChild` prop
- [ ] `src/lib/components/ui/contact-management/bulk-action-bar.svelte:129` - Remove `asChild` prop

**Fix**: Wrap with Button
```svelte
<!-- Before -->
<DropdownMenu.Trigger asChild>

<!-- After -->
<DropdownMenu.Trigger>
  <Button variant="ghost">
    <slot />
  </Button>
</DropdownMenu.Trigger>
```

---

### 2.2 Invalid Badge Variants ❌

- [ ] `src/lib/components/ui/help-center/article-editor/article-editor.svelte:213` - `"outline-solid"` → `"outline"`
- [ ] `src/lib/components/ui/pagination/pagination-footer.svelte:84` - `"outline-solid"` → `"outline"`
- [ ] `src/lib/components/macros/MacrosList.svelte:111` - `"outline-solid"` → `"outline"`
- [ ] `src/lib/components/navigation/FilterChips.svelte:21` - `"outline-solid"` → `"outline"`
- [ ] `src/lib/components/ConfirmDialog.svelte:48` - Invalid variant type

**Valid Badge Variants**: `"default"`, `"secondary"`, `"destructive"`, `"outline"`

---

### 2.3 Invalid Button Variants ❌

- [ ] `src/lib/components/ConfirmDialog.svelte:48` - Check variant type

**Valid Button Variants**: `"default"`, `"destructive"`, `"outline"`, `"secondary"`, `"ghost"`, `"link"`

---

### 2.4 Self-Closing Tag Warnings ⚠️

- [ ] `src/lib/components/reports/heatmaps/BaseHeatmap.svelte:107` - `<div />` → `<div></div>`
- [ ] `src/lib/components/reports/heatmaps/BaseHeatmap.svelte:121` - `<div />` → `<div></div>`
- [ ] `src/lib/components/layout/AppSidebar.svelte:562` - `<div />` → `<div></div>`
- [ ] `src/lib/components/layout/MobileSidebarLauncher.svelte:32` - `<span />` → `<span></span>`
- [ ] `src/lib/components/reports/shared/LiveBadge.svelte:21` - `<span />` → `<span></span>`

---

### 2.5 Component Binding Issues ❌

- [ ] `src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:222` - Cannot bind `from`
- [ ] `src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:223` - Cannot bind `to`
- [ ] `src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:224` - Cannot bind `daysNum`

**Fix**: Make props bindable
```typescript
let { from = $bindable(), to = $bindable(), daysNum = $bindable() } = $props();
```

---

### 2.6 Unknown CSS @apply Rules ⚠️

- [ ] `src/lib/components/ui/presence-indicator.svelte:80, 84`
- [ ] `src/lib/components/ui/typing-indicator.svelte:38, 42, 46, 50, 63, 68, 71`
- [ ] `src/lib/components/ui/websocket-status.svelte:88, 92, 96, 101`
- [ ] `src/lib/components/widget/CampaignMessage.svelte:109, 113, 117, 121, 125, 129`
- [ ] `src/lib/components/widget/CampaignView.svelte:125, 130, 136, 140`
- [ ] `src/lib/components/widget/WidgetApp.svelte:265, 271, 275, 279`

**Note**: These are warnings, not errors. Ensure Tailwind CSS is properly configured.

---

## 🎯 Phase 3: TypeScript & Import Issues (Priority: MEDIUM)

### 3.1 Missing Module Files ❌

#### Store Files
- [ ] Create `src/lib/stores/csat.svelte.ts`
- [ ] Create `src/lib/stores/account.svelte.ts`
- [ ] Create `src/lib/stores/slaReports.svelte.ts`

#### Constant Files
- [ ] Create `src/lib/constants/reports.ts`
- [ ] Create `src/lib/constants/featureFlags.ts`

#### Utility Files
- [ ] Create `src/lib/utils/downloadHelper.ts`
- [ ] Create `src/lib/utils/timeHelper.ts`

#### Component Files
- [ ] Create `src/lib/components/ui/date-range-picker/DateRangePicker.svelte`
- [ ] Create `src/lib/components/reports/bot/BotMetrics.svelte`
- [ ] Create `src/lib/components/reports/sla/SLAMetrics.svelte`
- [ ] Create `src/lib/components/reports/sla/SLATable.svelte`
- [ ] Create `src/lib/components/reports/sla/SLAReportFilters.svelte`

---

### 3.2 Missing NPM Packages ❌

- [ ] Install `svelte-chartjs`
- [ ] Install `chart.js`
- [ ] Install `@testing-library/user-event`

**Command**:
```bash
pnpm add svelte-chartjs chart.js @testing-library/user-event
```

---

### 3.3 Import Path Issues ❌

- [ ] `src/lib/actions/contacts.svelte.ts:21` - Remove `.ts` extension
- [ ] `src/lib/actions/examples/ContactsPage.svelte:14` - Remove `.ts` extension

**Fix Pattern**:
```typescript
// Before
import { BaseAction } from './base.svelte.ts';

// After
import { BaseAction } from './base.svelte';
```

---

### 3.4 Wrong Type Imports ❌

- [ ] `src/lib/components/layout/SidebarAccountSwitcher.svelte:7` - `HTMLDivAttributes` → `HTMLAttributes`

**Fix**:
```typescript
// Before
import type { HTMLDivAttributes } from 'svelte/elements';

// After
import type { HTMLAttributes } from 'svelte/elements';
```

---

### 3.5 Module Not Found Issues ❌

- [ ] `src/lib/components/ui/phone-input/phone-input.svelte:8` - Fix `@kevwpl/svelte-o-phone` import
- [ ] Multiple files - Missing `$lib/components/ui/date-range-picker/DateRangePicker.svelte`

---

## 🎯 Phase 4: Type Safety Issues (Priority: MEDIUM)

### 4.1 Private Property Access ❌

- [ ] `src/lib/actions/base.svelte.ts:301` - `this.options` is private
- [ ] `src/lib/actions/base.svelte.ts:303` - `this.options` is private
- [ ] `src/lib/actions/base.svelte.ts:315` - `this.options` is private
- [ ] `src/lib/websocket/client.ts:157` - `this.store.state` is private

**Fix**: Make properties protected or add getter methods
```typescript
// Option 1: Make protected
protected options: ActionOptions<TData, TVariables>;

// Option 2: Add getter
getOptions() {
  return this.options;
}
```

---

### 4.2 Missing Properties on Types ❌

#### ActionOptions Interface
- [ ] `src/lib/actions/base.svelte.ts:301, 303` - Add `optimisticUpdate` property

#### UserAccount Interface
- [ ] Add `avatarUrl` property
- [ ] Add `supportEmail` property (currently `support_email`)
- [ ] Add `latestChatwootVersion` property
- [ ] Add `customAttributes` property

#### NavigationItem Interface
- [ ] Make `href` optional for parent items with children
- [ ] Fix multiple AppSidebar.svelte errors (lines 125, 138, 152, 165, 226, 252, 325, 353, 388)

#### Contact Interface
- [ ] Fix `updatedAt` type (string vs number mismatch)

#### Conversation Interface
- [ ] Add `typingUsers` property

#### Message Interface
- [ ] Fix `created_at` → `createdAt` (snake_case vs camelCase)

#### Notification Interface
- [ ] Add `secondaryActor` property

---

### 4.3 Type Mismatches ❌

#### Timeout Types
- [ ] `src/lib/composables/useLiveRefresh.svelte.ts:51` - `Timeout` vs `number`

**Fix**:
```typescript
// Node.js environment
let timeoutId: NodeJS.Timeout;

// Browser environment
let timeoutId: number = window.setTimeout(...);
```

#### Date/Time Types
- [ ] `src/lib/stores/conversations.svelte.ts:598` - `string` vs `number` for `agentLastSeenAt`
- [ ] Multiple files - `createdAt`/`updatedAt` type mismatches

#### Boolean Types
- [ ] `src/lib/widget/websocket/client.ts:143` - `number | boolean | null` vs `boolean`

---

### 4.4 Function Signature Mismatches ❌

- [ ] `src/lib/components/ui/phone-input/phone-input.svelte:107` - `onSelect` expects 0 args, got 1
- [ ] `src/lib/components/notifications/NotificationBell.svelte:18` - `fetchNotifications` expects 1-2 args, got 0
- [ ] `src/lib/components/notifications/NotificationBell.svelte:23` - `markAllAsRead` expects 1 arg, got 0
- [ ] `src/lib/stores/labels.svelte.ts:136` - `getLabel` expects 2 args, got 1
- [ ] `src/lib/stores/labels.svelte.ts:209` - `deleteLabel` expects 2 args, got 1
- [ ] `src/lib/widget/websocket/client.ts:369` - `updateMessage` expects 2 args, got 1

---

### 4.5 Implicit Any Types ❌

- [ ] `src/lib/components/ui/phone-input/phone-input.svelte:32` - Parameter `details`
- [ ] `src/lib/components/ui/phone-input/phone-input.svelte:54` - Parameter `c`
- [ ] `src/lib/components/ui/phone-input/phone-input.svelte:66` - Parameter `c`

**Fix**: Add explicit types
```typescript
onchange: (details: PhoneDetails) => { ... }
```

---

### 4.6 Component Type Errors ❌

#### Test Files (Svelte 5 Component Type)
- [ ] `src/lib/components/reports/__tests__/BaseHeatmap.test.ts` - Multiple render() calls
- [ ] `src/lib/components/ui/phone-input/phone-input.test.ts` - Multiple render() calls

**Note**: May need to update testing library for Svelte 5 compatibility

---

## 🎯 Phase 5: API & Data Issues (Priority: LOW)

### 5.1 API Response Typing ❌

**Files with `response` type `unknown`**:
- [ ] `src/lib/api/contacts.ts:107, 108` - Spread types issue
- [ ] `src/lib/api/contacts.ts:129, 130` - Spread types issue
- [ ] `src/lib/api/contacts.ts:169, 170` - Spread types issue
- [ ] `src/lib/api/contacts.ts:189, 190` - Spread types issue
- [ ] `src/lib/api/contacts.ts:202` - Property `data` doesn't exist
- [ ] `src/lib/api/contacts.ts:222` - Property `data` doesn't exist
- [ ] `src/lib/api/contacts.ts:257` - Property `data` doesn't exist
- [ ] `src/lib/api/contacts.ts:269` - Property `data` doesn't exist
- [ ] `src/lib/api/contacts.ts:294` - Property `data` doesn't exist
- [ ] `src/lib/api/contacts.ts:324` - Property `data` doesn't exist

**Fix Pattern**:
```typescript
interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    lastPage: number;
    total: number;
  };
}

const response = await api.get(url).json<PaginatedResponse<Contact>>();
```

---

### 5.2 Missing Store Methods ❌

- [ ] `src/lib/stores/inboxes.svelte.ts:494` - Add `inboxesCount` property
- [ ] `src/lib/stores/reports.svelte.ts:438` - Add `overview` property to ReportsState
- [ ] `src/lib/components/reports/csat/CsatFilters.svelte:25` - Add `getAgents()` method
- [ ] `src/lib/components/reports/csat/CsatFilters.svelte:26` - Add `getInboxes()` method
- [ ] `src/lib/components/reports/csat/CsatFilters.svelte:27` - Add `getTeams()` method
- [ ] Multiple files - Add missing ReportsStore methods:
  - `getChartData()`
  - `getUIFlag()`
  - `getData()`
  - `getFilterItems()`
  - `dispatchAction()`
  - `fetchAccountSummary()`
  - `fetchAccountReport()`
  - `fetchBotSummary()`
  - `downloadConversationsSummaryReports()`
- [ ] `src/routes/app/accounts/[accountId]/settings/account/components/AutoResolve.svelte:126` - Add `all` property to LabelsStore
- [ ] `src/routes/app/accounts/[accountId]/reports/sla/+page.svelte:38` - Add `fetchSLAs()` method to SLAStore

---

### 5.3 Property Name Mismatches ❌

- [ ] `src/lib/stores/conversations.svelte.ts:543` - `created_at` → `createdAt`
- [ ] `src/lib/widget/api/messages.ts:127` - `referrerURL` doesn't exist on Window
- [ ] `src/routes/app/accounts/[accountId]/settings/account/+page.svelte:58` - `support_email` → `supportEmail`

---

### 5.4 Argument Type Mismatches ❌

- [ ] `src/lib/actions/contacts.svelte.ts:180` - Optimistic update return type mismatch
- [ ] `src/lib/actions/contacts.svelte.ts:181` - Conversion of undefined to Contact
- [ ] `src/lib/actions/examples/ContactsPage.svelte:34` - `company` doesn't exist in CreateContactParams
- [ ] `src/lib/actions/examples/ContactsPage.svelte:91` - Partial<Contact> vs UpdateContactParams
- [ ] `src/lib/websocket/client.ts:171` - `null` vs `string` for setError
- [ ] `src/lib/widget/websocket/client.ts:172` - WidgetMessage vs Message type
- [ ] `src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:446` - `number` vs `string` for formatDate
- [ ] `src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:452` - `number | null` vs `string | null | undefined`
- [ ] `src/routes/app/accounts/[accountId]/contacts/[contactId]/+page.svelte:565, 664` - `unknown` vs `string | null | undefined`

---

### 5.5 Component Props Issues ❌

- [ ] `src/lib/components/ui/contact-management/contact-form/contact-form.svelte:112` - `name` property doesn't exist
- [ ] `src/lib/components/ui/contact-management/advanced-filter.svelte:180` - Type mismatch for `values`
- [ ] `src/lib/components/ui/contact-management/bulk-action-bar.svelte:94` - `"indeterminate"` vs `boolean`
- [ ] `src/lib/components/ui/select/select.svelte:15` - `string | string[]` vs `string[]`
- [ ] `src/lib/components/widget/WidgetApp.svelte:227` - `messageCount` doesn't exist
- [ ] `src/lib/components/reports/heatmaps/BaseHeatmapContainer.svelte:214` - `title` doesn't exist
- [ ] Multiple SectionLayout components - Missing `headerActions` or `children` props

---

### 5.6 Date Picker Type Issues ❌

- [ ] `src/lib/components/ui/date-picker/date-picker.svelte:92` - `DateValue` vs `DateValue[]`
- [ ] `src/lib/components/ui/date-picker/date-picker.svelte:93` - OnChangeFn type mismatch
- [ ] `src/lib/components/ui/custom-attributes/DateAttributeInput.svelte:92` - Same issue

---

### 5.7 Derived Type Issues ❌

- [ ] `src/lib/components/reports/heatmaps/BaseHeatmap.svelte:40` - Function vs HeatmapRow[]
- [ ] `src/lib/components/ui/presence-indicator.svelte:32, 38, 44` - Switch statement type issues
- [ ] `src/lib/components/ui/presence-indicator.svelte:69, 70, 71, 74` - Property access on function type
- [ ] `src/lib/components/ui/websocket-status.svelte:51` - Function vs variant type

---

### 5.8 Unknown Property Access ❌

- [ ] `src/lib/utils/campaign-helper.ts:26, 27` - `URLPattern` not found
- [ ] `src/lib/websocket/event-manager.ts:184` - `updatePresence` doesn't exist
- [ ] `src/lib/websocket/integration-test.ts` - Multiple tuple access errors
- [ ] `src/lib/components/reports/shared/ReportFilterSelector.svelte:39, 85, 86` - `filter` is unknown

---

### 5.9 Pagination Property Issues ❌

- [ ] `src/lib/actions/examples/ContactsPage.svelte:41` - `total` doesn't exist
- [ ] `src/lib/actions/examples/ContactsPage.svelte:42` - `currentPage` doesn't exist
- [ ] `src/lib/actions/examples/ContactsPage.svelte:43` - `lastPage` doesn't exist

**Fix**: Update PaginatedResponse interface
```typescript
interface PaginatedResponse<T> {
  data: T[];
  total: number;
  currentPage: number;
  lastPage: number;
  perPage: number;
}
```

---

## 📝 Quick Fix Commands

### Run Automated Fixes
```bash
# Make script executable
chmod +x scripts/fix-svelte5.sh

# Run automated fixes
./scripts/fix-svelte5.sh
```

### Manual Verification
```bash
# Type check
pnpm run check

# Build
pnpm run build

# Run tests
pnpm test

# Lint
pnpm run lint
```

---

## 🎯 Daily Progress Log

### 2026-02-09
- [ ] Created tracking file
- [ ] Analyzed all 197 errors + 52 warnings
- [ ] Categorized into 5 phases
- [ ] Ready to start Phase 1

### 2026-02-10
- [ ] Phase 1 progress: __/15 fixed
- [ ] Phase 2 progress: __/25 fixed

### 2026-02-11
- [ ] Phase 3 progress: __/45 fixed
- [ ] Phase 4 progress: __/60 fixed

### 2026-02-12
- [ ] Phase 5 progress: __/52 fixed
- [ ] Final verification

---

## 🚀 Next Actions

1. **Start with Phase 1** - Critical syntax issues (highest impact)
2. **Run automated script** - Fix bulk issues quickly
3. **Manual fixes** - Address component-specific issues
4. **Create missing files** - Stores, constants, utilities
5. **Type safety** - Fix all TypeScript errors
6. **Test thoroughly** - Ensure no regressions

---

## 📚 Resources

- [Svelte 5 Migration Guide](https://svelte.dev/docs/svelte/v5-migration-guide)
- [shadcn-svelte Documentation](https://www.shadcn-svelte.com/)
- [Bits UI Documentation](https://bits-ui.com/)
- Project: `laravel-svelte-port/svelte-ui/llms.txt`
- Guidelines: `AGENTS.md`

---

**Status Legend**:
- 🔴 Not Started
- 🟡 In Progress
- 🟢 Complete
- ⚠️ Warning (non-blocking)
- ❌ Error (blocking)
