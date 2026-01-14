# Svelte 5 Migration - Phased Resolution Plan

**Created**: 2026-01-14  
**Current State**: 517 errors, 101 warnings, 179 files  
**Goal**: 0 errors, 0 warnings, full Svelte 5 compliance

This plan ensures all components follow official **shadcn-svelte** UI component library/framework and use correct **Svelte 5 syntax** from llms.txt.

---

## 📋 Core Principles

### 1. shadcn-svelte Component Standards
- ✅ **USE**: shadcn-svelte components (Input, Select, Card, Button, etc.)
- ✅ **EXTEND**: Add missing props to shadcn components via TypeScript interfaces
- ✅ **MAINTAIN**: bits-ui primitives as the foundation
- ❌ **AVOID**: Native HTML elements as replacements
- ❌ **AVOID**: Custom wrappers around shadcn components

### 2. Svelte 5 Syntax (from llms.txt)
- ✅ **USE**: `$props()` rune for component props
- ✅ **USE**: `$state()` rune for reactive state
- ✅ **USE**: `$derived()` rune for computed values
- ✅ **USE**: `$effect()` rune for side effects
- ✅ **USE**: `$bindable()` rune for two-way binding
- ✅ **USE**: Event attributes (`onclick`, `oninput`, etc.)
- ❌ **AVOID**: `export let` (Svelte 4 pattern)
- ❌ **AVOID**: Event directives (`on:click`, `on:input`, etc.)

### 3. TypeScript Type Safety
- ✅ **ENFORCE**: Explicit types for all event handlers
- ✅ **USE**: Proper TypeScript interfaces for component props
- ✅ **VALIDATE**: All API response types match frontend expectations
- ❌ **AVOID**: `any` type unless absolutely necessary

---

## 🎯 Phase 1: Foundation & Critical Fixes (Days 1-2)

**Goal**: Fix critical blocking errors that affect multiple files  
**Target**: 618 → 550 issues (68 fixes, 11% reduction)

### 1.1 Fix shadcn-svelte UI Component Extensions (Priority: CRITICAL)

**Issue**: Core UI components missing props causing ~100+ errors

#### Input Component (`src/lib/components/ui/input/`)
**Errors Fixed**: ~20
```typescript
// Current: Limited type support
type Props = {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search';
}

// Fix: Extend to support all HTML5 input types
type Props = HTMLInputAttributes & {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search'
    | 'date' | 'time' | 'datetime-local' | 'month' | 'week' | 'color'
    | 'file' | 'range' | 'hidden';
  min?: string | number;
  max?: string | number;
  step?: string | number;
  accept?: string;
}
```

**Files to Update**:
- `src/lib/components/ui/input/input.svelte`
- `src/lib/components/ui/input/index.ts`

**Testing**:
```bash
# Verify fixes in these files
pnpm run check | grep "type.*date.*color"
```

---

#### Card Component (`src/lib/components/ui/card/`)
**Errors Fixed**: ~30
```typescript
// Current: No HTML attributes support
type Props = {
  class?: string;
  children?: Snippet;
}

// Fix: Accept all div HTML attributes
import type { HTMLAttributes } from 'svelte/elements';

type Props = HTMLAttributes<HTMLDivElement> & {
  class?: string;
  children?: Snippet;
}
```

**Files to Update**:
- `src/lib/components/ui/card/card.svelte`

**Testing**:
```bash
# Verify onclick works on cards
pnpm run check | grep "Card.Root.*onclick"
```

---

#### DropdownMenuItem Component (`src/lib/components/ui/dropdown-menu/`)
**Errors Fixed**: ~15
```typescript
// Issue: bits-ui uses 'onselect', users expect 'onclick'
type Props = {
  class?: string;
  inset?: boolean;
  children?: Snippet;
  onclick?: () => void;  // Add onclick support
  disabled?: boolean;
}

// Implementation: Map onclick to bits-ui's onselect
<DropdownMenuPrimitive.Item
  onselect={onclick}  // Map onclick to onselect
  {...restProps}
>
```

**Files to Update**:
- `src/lib/components/ui/dropdown-menu/dropdown-menu-item.svelte`

---

#### Switch Component (`src/lib/components/ui/switch/`)
**Errors Fixed**: ~10
```typescript
// Current: No id prop support
type Props = {
  checked?: boolean;
}

// Fix: Accept id and other label attributes
type Props = {
  id?: string;
  checked?: boolean;
  disabled?: boolean;
  name?: string;
  'aria-label'?: string;
  'aria-describedby'?: string;
}
```

**Files to Update**:
- `src/lib/components/ui/switch/switch.svelte`

---

#### Select Component (`src/lib/components/ui/select/`)
**Errors Fixed**: ~10
```typescript
// Issue: binding to 'selected' instead of 'value'
// Fix: Ensure value prop is bindable

type Props = {
  value?: string | string[];  // Make bindable
  multiple?: boolean;
  // ... other props
}
```

**Files to Update**:
- `src/lib/components/ui/select/select.svelte`
- `src/lib/components/ui/select/select-trigger.svelte`

---

### 1.2 Fix API Data Transformation (Priority: CRITICAL)

**Issue**: Snake_case properties from API not transformed to camelCase  
**Errors Fixed**: ~10

**Root Cause**: API transformation layer should auto-convert but some properties slip through

**Files to Check**:
```typescript
// src/lib/api/transformers.ts
export function transformKeys(obj: any): any {
  // Ensure all snake_case → camelCase
  // phone_number → phoneNumber
  // avatar_url → avatarUrl
  // company_name → companyName
}
```

**Files to Fix**:
- `src/lib/components/contacts/ContactPanel.svelte` (phone_number, availability_status, etc.)
- `src/lib/components/conversations/ConversationItem.svelte` (channel_type, unread_count, etc.)
- `src/lib/components/layout/AppHeader.svelte` (avatar_url)
- `src/lib/components/messages/MessageList.svelte` (created_at, message_type)

**Fix Pattern**:
```svelte
<!-- ❌ Wrong -->
<p>{contact.phone_number}</p>

<!-- ✅ Correct -->
<p>{contact.phoneNumber}</p>
```

---

### 1.3 Fix Type Mismatches (Priority: CRITICAL)

**Issue**: Missing undefined checks for route params  
**Errors Fixed**: ~10

**Files to Fix**:
- `src/routes/app/accounts/[accountId]/conversations/[id]/+page.svelte`

**Fix Pattern**:
```typescript
// ❌ Wrong
let accountId = $state<number>(parseInt($page.params.accountId));

// ✅ Correct
let accountId = $state<number>(parseInt($page.params.accountId ?? '0'));
```

---

## 🎯 Phase 2: Event Handler Type Annotations (Days 3-4)

**Goal**: Add TypeScript types to all event handlers  
**Target**: 550 → 350 issues (200 fixes, 36% reduction)

### 2.1 Fix Event Handler Types (Priority: HIGH)

**Errors Fixed**: ~40

**Pattern**:
```typescript
// ❌ Wrong: Implicit any
<Input oninput={(e) => handleChange(e.currentTarget.value)} />

// ✅ Correct: Explicit type
<Input oninput={(e: Event & { currentTarget: HTMLInputElement }) => handleChange(e.currentTarget.value)} />
```

**Common Event Types**:
```typescript
// Input events
(e: Event & { currentTarget: HTMLInputElement }) => ...

// Textarea events
(e: Event & { currentTarget: HTMLTextAreaElement }) => ...

// Click events
(e: MouseEvent) => ...

// Keyboard events
(e: KeyboardEvent) => ...

// Form submit
(e: SubmitEvent) => ...
```

**High-Priority Files** (~40 errors):
1. `src/lib/components/whatsapp/WhatsAppTemplateParser.svelte` (4 errors)
2. `src/routes/app/accounts/[accountId]/companies/+page.svelte` (2 errors)
3. `src/routes/app/accounts/[accountId]/settings/agents/+page.svelte` (1 error)
4. `src/routes/app/accounts/[accountId]/settings/attributes/+page.svelte` (2 errors)
5. `src/routes/app/super_admin/agent-bots/[id]/edit/+page.svelte` (3 errors)
6. `src/routes/app/super_admin/settings/+page.svelte` (2 errors)
7. `src/lib/components/search/GlobalSearch.svelte` (1 error)
8. `src/lib/components/notifications/NotificationItem.svelte` (1 error)

### 2.2 Fix Component Event Props (Priority: HIGH)

**Errors Fixed**: ~60

**Issue**: shadcn components need event handler props defined

**Files to Update**:
1. **Textarea component** - Add `onkeydown` support
2. **Input component** - Add `oninput` support (if not in Phase 1)
3. **Combobox component** - Add `onfocus` support

---

### 2.3 Fix Non-Bindable Properties (Priority: HIGH)

**Errors Fixed**: ~15

**Issue**: Components missing `$bindable()` rune

**Pattern**:
```svelte
<!-- Component definition -->
<script lang="ts">
  // ❌ Wrong
  let { open } = $props<{ open?: boolean }>();
  
  // ✅ Correct
  let { open = $bindable(false) } = $props<{ open?: boolean }>();
</script>
```

**Files to Fix**:
1. `src/lib/components/ui/dialog/dialog.svelte` - `open` prop
2. `src/lib/components/companies/CompanyDialog.svelte` - `open` prop
3. `src/lib/components/survey/SurveyForm.svelte` - `value` prop
4. `src/routes/app/super_admin/platform-apps/[id]/+page.svelte` - Dialog `open`

---

### 2.4 Fix Deprecated Event Directives (Priority: HIGH)

**Errors Fixed**: ~5

**Pattern**:
```svelte
<!-- ❌ Old: Event directive -->
<img on:error={handleError} />

<!-- ✅ New: Event attribute -->
<img onerror={handleError} />
```

**Files to Fix**:
1. `src/lib/components/ui/avatar/avatar-image.svelte`

---

## 🎯 Phase 3: Accessibility & Component Bindings (Days 5-6)

**Goal**: Fix accessibility warnings and binding issues  
**Target**: 350 → 100 issues (250 fixes, 71% reduction)

### 3.1 Fix Accessibility Warnings (Priority: MEDIUM)

**Errors Fixed**: ~60

#### Issue 1: Click Events Without Keyboard Handlers
```svelte
<!-- ❌ Wrong -->
<div onclick={() => handleClick()}>Click me</div>

<!-- ✅ Fix 1: Use button -->
<button type="button" onclick={() => handleClick()}>Click me</button>

<!-- ✅ Fix 2: Add keyboard support -->
<div
  role="button"
  tabindex="0"
  onclick={() => handleClick()}
  onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && handleClick()}
>
  Click me
</div>
```

**High-Impact Files**:
1. `src/lib/components/layout/AppSidebar.svelte`
2. `src/lib/components/notifications/NotificationItem.svelte`
3. `src/routes/app/accounts/[accountId]/+page.svelte`
4. `src/routes/app/accounts/[accountId]/campaigns/+page.svelte` (multiple)
5. `src/routes/app/accounts/[accountId]/companies/+page.svelte` (multiple)

#### Issue 2: Labels Without Associated Controls
```svelte
<!-- ❌ Wrong -->
<label>Name</label>
<p>{value}</p>

<!-- ✅ Correct: Use semantic HTML -->
<div>
  <span class="text-sm font-medium">Name</span>
  <p>{value}</p>
</div>
```

---

### 3.2 Fix Select Component Bindings (Priority: MEDIUM)

**Errors Fixed**: ~20

**Issue**: Incorrect binding property names

```svelte
<!-- ❌ Wrong -->
<Select.Root bind:selected={value}>

<!-- ✅ Correct -->
<Select.Root bind:value={value}>
```

**Files to Fix**:
1. `src/routes/app/accounts/[accountId]/settings/account/+page.svelte` (2 instances)
2. `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte` (1 instance)
3. `src/lib/components/conversations/ConversationFilters.svelte` (1 instance)

---

### 3.3 Fix Missing Component Props (Priority: MEDIUM)

**Errors Fixed**: ~40

**Issue**: Components don't export certain props

#### Dropdown Menu
```typescript
// Add 'align' prop to DropdownMenuContent
type Props = {
  align?: 'start' | 'center' | 'end';
  // ... other props
}
```

**Files to Update**:
- `src/lib/components/ui/dropdown-menu/dropdown-menu-content.svelte`

#### Table Components
```typescript
// Fix HTML attribute imports
import type { HTMLTableAttributes } from 'svelte/elements';

// NOT HTMLTableSectionAttributes (doesn't exist)
```

**Files to Update**:
- `src/lib/components/ui/table/table-header.svelte`
- `src/lib/components/ui/table/table-body.svelte`
- `src/lib/components/ui/table/table-row.svelte`
- `src/lib/components/ui/table/table-cell.svelte`
- `src/lib/components/ui/table/table-head.svelte`

---

### 3.4 Fix Custom Component Props (Priority: MEDIUM)

**Errors Fixed**: ~30

**Issue**: Custom components missing exported props

**Components to Fix**:
1. **EmptyState** - Export Root, Icon, Title, Description, Actions
2. **CustomAttributes** - Export Root component
3. **ConversationCard** - Export Preview component with children support

---

## 🎯 Phase 4: API Integration & Type Safety (Day 7)

**Goal**: Fix API client type issues and store methods  
**Target**: 100 → 50 issues (50 fixes, 84% reduction)

### 4.1 Fix API Client Type Issues (Priority: MEDIUM)

**Errors Fixed**: ~10

**Files to Fix**:
1. `src/lib/api/client.ts` - BeforeErrorHook type, ReadableStream conversion
2. `src/lib/api/auditLogs.ts` - SearchParams type
3. `src/lib/api/auth.ts` - uploadFile generic type
4. `src/lib/api/messages.ts` - Argument count
5. `src/lib/api/search.ts` - SearchParams type

---

### 4.2 Fix Store Method Calls (Priority: MEDIUM)

**Errors Fixed**: ~5

**Issue**: Store methods renamed or don't exist

```typescript
// ❌ Wrong
conversationsStore.selectConversation(id);

// ✅ Check store interface
conversationsStore.setSelectedConversation(id);
```

**Files to Fix**:
1. `src/lib/components/conversations/ConversationList.svelte`
2. `src/routes/app/accounts/[accountId]/conversations/+page.svelte`

---

### 4.3 Fix Test Type Issues (Priority: LOW)

**Errors Fixed**: ~10

**Files to Fix**:
1. `src/lib/test-utils/render.ts`
2. `src/lib/test-utils/matchers.ts`
3. `src/lib/components/contacts/__tests__/ContactInfo.test.ts`
4. `src/lib/components/messages/__tests__/MessageBubble.test.ts`

---

## 🎯 Phase 5: Polish & Final Fixes (Day 8)

**Goal**: Fix remaining warnings and edge cases  
**Target**: 50 → 0 issues (50 fixes, 100% complete)

### 5.1 Fix CSS Compatibility Warnings (Priority: LOW)

**Errors Fixed**: ~5

**Issue**: Missing standard property alongside webkit

```css
/* ❌ Missing standard property */
.truncated {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

/* ✅ With standard property */
.truncated {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;  /* Add standard */
  -webkit-box-orient: vertical;
}
```

**Files to Fix**:
1. `src/lib/components/portal/CategoryCard.svelte`
2. `src/lib/components/widget/features/ArticleCard.svelte` (2 instances)
3. `src/routes/app/accounts/[accountId]/campaigns/+page.svelte`
4. `src/routes/app/accounts/[accountId]/companies/+page.svelte`

---

### 5.2 Fix Unused CSS Selectors (Priority: LOW)

**Errors Fixed**: ~10

**Action**: Remove or use the selectors

**Files to Fix**:
1. `src/lib/components/portal/PortalHeader.svelte`
2. `src/lib/components/widget/features/ArticleSearch.svelte`
3. `src/lib/components/widget/layout/WidgetBubble.svelte` (2 instances)
4. `src/lib/components/widget/features/PreChatForm.svelte`

---

### 5.3 Fix Self-Closing Tags (Priority: LOW)

**Errors Fixed**: ~3

**Issue**: Non-void elements with self-closing tags

```svelte
<!-- ❌ Wrong -->
<textarea id="feedback" bind:value={feedback} />

<!-- ✅ Correct -->
<textarea id="feedback" bind:value={feedback}></textarea>
```

**Files to Fix**:
1. `src/lib/components/survey/SurveyForm.svelte`
2. `src/lib/components/widget/features/PreChatForm.svelte`
3. `src/lib/components/widget/input/MessageInput.svelte`

---

### 5.4 Fix Miscellaneous Errors (Priority: LOW)

**Errors Fixed**: ~20

**Issues to Address**:
1. `{:elseif}` should be `{:else if}` syntax
2. Icon component `title` prop
3. Label `htmlFor` prop
4. i18n `initI18n()` parameter count
5. Toast error null handling

---

## 📊 Implementation Strategy

### Daily Breakdown

#### Day 1-2: Foundation
- Morning: Input component extensions
- Afternoon: Card, Switch, Select components
- Evening: Snake_case API fixes

#### Day 3-4: Event Handlers
- Morning: WhatsApp, companies, settings pages
- Afternoon: Super admin pages
- Evening: Component event props

#### Day 5-6: Accessibility
- Morning: Button/div conversions
- Afternoon: Keyboard handlers
- Evening: Component bindings

#### Day 7: API & Types
- Morning: API client fixes
- Afternoon: Store methods
- Evening: Test fixes

#### Day 8: Polish
- Morning: CSS warnings
- Afternoon: Unused selectors
- Evening: Final validation

---

## ✅ Validation Checklist

After each phase:
```bash
# 1. Run type check
pnpm run check

# 2. Verify error count reduction
# Phase 1: Should be ~550 errors
# Phase 2: Should be ~350 errors
# Phase 3: Should be ~100 errors
# Phase 4: Should be ~50 errors
# Phase 5: Should be 0 errors

# 3. Test in browser
pnpm run dev

# 4. Check key pages
# - Dashboard
# - Conversations
# - Contacts
# - Settings
# - Super Admin

# 5. Verify shadcn components work
# - Forms submit correctly
# - Dialogs open/close
# - Dropdowns work
# - Inputs accept all types
```

---

## 🎯 Success Metrics

### Technical Goals
- ✅ 0 TypeScript errors
- ✅ 0 Svelte check warnings
- ✅ All shadcn-svelte components properly extended
- ✅ All event handlers typed
- ✅ All API transformations working
- ✅ Full accessibility compliance

### Code Quality Goals
- ✅ 100% Svelte 5 runes usage
- ✅ No `export let` patterns
- ✅ No `on:*` event directives
- ✅ Consistent TypeScript types
- ✅ shadcn-svelte component compliance

### User Experience Goals
- ✅ All pages render without errors
- ✅ All forms work correctly
- ✅ All interactions feel smooth
- ✅ No console errors
- ✅ Proper keyboard navigation

---

## 📚 Reference Documentation

### Primary Resources
1. **Svelte 5 Documentation**: `/laravel-svelte-port/svelte-ui/llms.txt`
2. **shadcn-svelte Components**: https://www.shadcn-svelte.com/docs/components
3. **bits-ui Primitives**: https://www.bits-ui.com/docs/components
4. **Project Config**: `/laravel-svelte-port/svelte-ui/components.json`

### Internal Documentation
1. **SVELTE5_QUICK_FIX_GUIDE.md** - Common patterns
2. **SVELTE5_ERROR_ANALYSIS.md** - Error categories
3. **SVELTE5_FILE_BREAKDOWN.md** - File-specific issues
4. **SVELTE_CHECK_ANALYSIS.md** - Current state

---

## 🔄 Progress Tracking

Create tracking issues for each phase:

```markdown
## Phase 1: Foundation & Critical Fixes
- [ ] 1.1 Input component extensions
- [ ] 1.2 Card component extensions
- [ ] 1.3 DropdownMenuItem extensions
- [ ] 1.4 Switch component extensions
- [ ] 1.5 Select component fixes
- [ ] 1.6 API data transformation
- [ ] 1.7 Type mismatch fixes

## Phase 2: Event Handler Types
- [ ] 2.1 WhatsApp component handlers
- [ ] 2.2 Companies page handlers
- [ ] 2.3 Settings page handlers
- [ ] 2.4 Super admin handlers
- [ ] 2.5 Component event props
- [ ] 2.6 Non-bindable properties
- [ ] 2.7 Deprecated directives

## Phase 3: Accessibility
- [ ] 3.1 Click event keyboard handlers
- [ ] 3.2 Label associations
- [ ] 3.3 Select component bindings
- [ ] 3.4 Missing component props

## Phase 4: API Integration
- [ ] 4.1 API client type fixes
- [ ] 4.2 Store method calls
- [ ] 4.3 Test type issues

## Phase 5: Polish
- [ ] 5.1 CSS compatibility
- [ ] 5.2 Unused selectors
- [ ] 5.3 Self-closing tags
- [ ] 5.4 Miscellaneous fixes
```

---

**Last Updated**: 2026-01-14  
**Status**: Ready for Implementation  
**Estimated Duration**: 8 days  
**Team Size**: 1-2 developers
