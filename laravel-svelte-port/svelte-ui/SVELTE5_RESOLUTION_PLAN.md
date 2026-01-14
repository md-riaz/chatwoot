# Svelte 5 Migration - Phased Resolution Plan

**Created**: 2026-01-14  
**Updated**: 2026-01-14 (Revised to respect shadcn-svelte components)  
**Current State**: 517 errors, 101 warnings, 179 files  
**Goal**: 0 errors, 0 warnings, full Svelte 5 compliance

This plan ensures correct **usage** of official **shadcn-svelte** UI components and proper **Svelte 5 syntax** from llms.txt.

---

## 📋 Core Principles

### 1. shadcn-svelte Component Standards (RESPECT THE FRAMEWORK)
- ✅ **USE**: shadcn-svelte components AS-IS - they are professionally built
- ✅ **NEVER MODIFY**: Do not extend or change shadcn-svelte component files directly
- ✅ **CREATE WRAPPERS**: Build custom wrapper components for project-specific needs
- ✅ **USE NATIVE HTML**: When shadcn doesn't provide a component (e.g., date inputs, file inputs)
- ✅ **CHECK DOCS**: Always verify correct usage patterns from shadcn-svelte.com
- ✅ **USE DEDICATED COMPONENTS**: Use specialized components when available (e.g., file upload has its own component)
- ❌ **NEVER EXTEND**: Don't modify shadcn component files themselves
- ❌ **DON'T MISUSE**: Use components for their intended purpose

**Wrapper Pattern Example**:
```svelte
<!-- ✅ CORRECT: Create wrapper in our own components folder -->
<!-- src/lib/components/custom/DateInput.svelte -->
<script lang="ts">
  let { value = $bindable(''), class: className = '', ...rest } = $props();
</script>

<input
  type="date"
  bind:value
  class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {className}"
  {...rest}
/>
```

```svelte
<!-- ✅ CORRECT: Wrapper around Card for clickable cards -->
<!-- src/lib/components/custom/ClickableCard.svelte -->
<script lang="ts">
  import { Card } from '$lib/components/ui/card';
  
  let { onclick, children, class: className = '' } = $props();
</script>

<button
  type="button"
  {onclick}
  class="block text-left transition-shadow hover:shadow-md {className}"
>
  <Card.Root>
    {@render children?.()}
  </Card.Root>
</button>
```

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

**Goal**: Fix critical issues by using components correctly, not by modifying them  
**Target**: 618 → 550 issues (68 fixes, 11% reduction)

### 1.1 Create Custom Wrapper Components (Priority: CRITICAL)

**Goal**: Build reusable wrapper components for common patterns  
**Errors Fixed**: ~50

#### DateInput Wrapper Component
Create a custom DateInput component that uses native HTML with shadcn styling:

```svelte
<!-- src/lib/components/custom/DateInput.svelte -->
<script lang="ts">
  import { cn } from '$lib/utils';
  
  type Props = {
    value?: string;
    class?: string;
    placeholder?: string;
    disabled?: boolean;
    required?: boolean;
    min?: string;
    max?: string;
    name?: string;
    id?: string;
    oninput?: (e: Event & { currentTarget: HTMLInputElement }) => void;
    onchange?: (e: Event & { currentTarget: HTMLInputElement }) => void;
  };
  
  let {
    value = $bindable(''),
    class: className,
    ...restProps
  }: Props = $props();
</script>

<input
  type="date"
  bind:value
  class={cn(
    'border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
  {...restProps}
/>
```

**Usage**:
```svelte
<DateInput bind:value={since} class="w-40" />
```

**Files to Update**:
- Create: `src/lib/components/custom/DateInput.svelte`
- Update: `src/routes/app/accounts/[accountId]/reports/+page.svelte` (2 uses)
- Update: `src/routes/app/accounts/[accountId]/settings/audit-logs/+page.svelte` (2 uses)

---

#### ColorInput Wrapper Component
```svelte
<!-- src/lib/components/custom/ColorInput.svelte -->
<script lang="ts">
  import { cn } from '$lib/utils';
  
  let {
    value = $bindable('#000000'),
    class: className,
    ...restProps
  } = $props<{
    value?: string;
    class?: string;
    disabled?: boolean;
    name?: string;
  }>();
</script>

<input
  type="color"
  bind:value
  class={cn(
    'border-input bg-background h-10 w-full rounded-md border px-1 py-1 disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
  {...restProps}
/>
```

**Files to Update**:
- Create: `src/lib/components/custom/ColorInput.svelte`
- Update: `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte`

---

#### ClickableCard Wrapper Component
Create a wrapper that makes Card clickable without modifying shadcn Card:

```svelte
<!-- src/lib/components/custom/ClickableCard.svelte -->
<script lang="ts">
  import { Card } from '$lib/components/ui/card';
  import type { Snippet } from 'svelte';
  
  let {
    onclick,
    children,
    class: className = '',
    ...restProps
  } = $props<{
    onclick: () => void;
    children?: Snippet;
    class?: string;
    [key: string]: any;
  }>();
</script>

<button
  type="button"
  {onclick}
  class="block w-full text-left transition-shadow hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring rounded-lg {className}"
  {...restProps}
>
  <Card.Root>
    {#if children}
      {@render children()}
    {/if}
  </Card.Root>
</button>
```

**Usage**:
```svelte
<ClickableCard onclick={() => navigate('/somewhere')}>
  <Card.Header>
    <Card.Title>Title</Card.Title>
  </Card.Header>
  <Card.Content>Content</Card.Content>
</ClickableCard>
```

**Files to Update**:
- Create: `src/lib/components/custom/ClickableCard.svelte`
- Update: `src/routes/app/accounts/[accountId]/+page.svelte`
- Update: `src/routes/app/accounts/[accountId]/settings/+page.svelte`
- Update: `src/routes/app/accounts/[accountId]/settings/agents/+page.svelte`
- Update: `src/routes/app/accounts/[accountId]/settings/inboxes/+page.svelte`
- Update: `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte`

---

#### NumberInput Wrapper Component
For number inputs with min/max:

```svelte
<!-- src/lib/components/custom/NumberInput.svelte -->
<script lang="ts">
  import { cn } from '$lib/utils';
  
  let {
    value = $bindable(0),
    class: className,
    min,
    max,
    step = 1,
    ...restProps
  } = $props<{
    value?: number;
    class?: string;
    min?: number;
    max?: number;
    step?: number;
    placeholder?: string;
    disabled?: boolean;
  }>();
</script>

<input
  type="number"
  bind:value
  {min}
  {max}
  {step}
  class={cn(
    'border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
  {...restProps}
/>
```

**Files to Update**:
- Create: `src/lib/components/custom/NumberInput.svelte`
- Update: `src/routes/app/super_admin/accounts/[id]/edit/+page.svelte` (2 uses)
- Update: `src/routes/app/super_admin/accounts/new/+page.svelte` (2 uses)

---

#### Custom Index File
```typescript
// src/lib/components/custom/index.ts
export { default as DateInput } from './DateInput.svelte';
export { default as ColorInput } from './ColorInput.svelte';
export { default as ClickableCard } from './ClickableCard.svelte';
export { default as NumberInput } from './NumberInput.svelte';
```

---

### 1.2 Fix DropdownMenuItem Usage (Priority: CRITICAL)


**Issue**: bits-ui (the foundation) uses `onselect`, not `onclick`  
**Errors Fixed**: ~15

```svelte
<!-- ❌ WRONG: Using onclick on DropdownMenuItem -->
<DropdownMenuItem onclick={handleAction}>
  Action
</DropdownMenuItem>

<!-- ✅ CORRECT: Use onselect as documented in bits-ui -->
<DropdownMenuItem onselect={handleAction}>
  Action
</DropdownMenuItem>
```

**Files to Update**:
- `src/routes/app/accounts/[accountId]/campaigns/+page.svelte` (6 instances)
- `src/lib/components/layout/AppHeader.svelte` (2 instances)

---

### 1.3 Fix Switch/Checkbox Label Association (Priority: CRITICAL)

**Issue**: Proper HTML label association without adding props to components  
**Errors Fixed**: ~20

```svelte
<!-- ❌ WRONG: Trying to add id to Switch -->
<Switch id="my-switch" bind:checked={value} />
<Label for="my-switch">Label</Label>

<!-- ✅ CORRECT: Wrap with Label -->
<Label class="flex items-center space-x-2 cursor-pointer">
  <Switch bind:checked={value} />
  <span>Enable feature</span>
</Label>

<!-- ✅ ALTERNATIVE: Use aria-labelledby -->
<div class="flex items-center space-x-2">
  <Switch bind:checked={value} aria-labelledby="switch-label" />
  <Label id="switch-label">Enable feature</Label>
</div>
```

**Files to Update**:
- `src/lib/components/messages/MessageComposer.svelte`
- `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte` (4 instances)
- `src/routes/app/accounts/[accountId]/settings/notifications/+page.svelte` (7 instances)
- `src/routes/ui/[name]/+page.svelte` (1 instance for Checkbox)

---

### 1.4 Fix Select Component Binding (Priority: CRITICAL)

**Issue**: Using wrong property names with Select  
**Errors Fixed**: ~15

```svelte
<!-- ❌ WRONG: binding to 'selected' -->
<Select.Root bind:selected={value}>
  <Select.Trigger>...</Select.Trigger>
</Select.Root>

<!-- ✅ CORRECT: Use 'value' prop as per bits-ui docs -->
<Select.Root bind:value={value}>
  <Select.Trigger>...</Select.Trigger>
</Select.Root>
```

**Files to Update**:
- `src/routes/app/accounts/[accountId]/settings/account/+page.svelte` (2 instances)
- `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte` (1 instance)
- `src/lib/components/conversations/ConversationFilters.svelte` (1 instance)

---

### 1.5 Fix Dialog Component Bindings (Priority: CRITICAL)

**Issue**: Dialog `open` prop needs `$bindable()` in custom wrapper components  
**Errors Fixed**: ~5

```svelte
<!-- In custom dialog component wrapper -->
<script lang="ts">
  import { Dialog } from '$lib/components/ui/dialog';
  
  // ❌ WRONG: Non-bindable prop
  let { open = false } = $props<{ open?: boolean }>();
  
  // ✅ CORRECT: Make it bindable
  let { open = $bindable(false) } = $props<{ open?: boolean }>();
</script>

<Dialog.Root bind:open>
  <!-- content -->
</Dialog.Root>
```

**Files to Update**:
- `src/lib/components/companies/CompanyDialog.svelte` (if it's a wrapper)
- `src/routes/app/super_admin/platform-apps/[id]/+page.svelte` (check if using wrapper)

---

### 1.6 Fix API Data Transformation (Priority: CRITICAL)

**Issue**: Snake_case properties from API not transformed to camelCase  
**Errors Fixed**: ~10

**Root Cause**: API transformation layer should auto-convert but some properties slip through

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

### 1.7 Fix Type Mismatches (Priority: CRITICAL)

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
<input oninput={(e) => handleChange(e.currentTarget.value)} />

// ✅ Correct: Explicit type
<input oninput={(e: Event & { currentTarget: HTMLInputElement }) => handleChange(e.currentTarget.value)} />
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

---

### 2.2 Fix Textarea Event Handlers (Priority: HIGH)

**Errors Fixed**: ~10

**Issue**: Using Textarea component but it doesn't support certain event props

```svelte
<!-- ❌ WRONG: Textarea component doesn't have onkeydown -->
<Textarea
  bind:value={message}
  onkeydown={handleKeyDown}
/>

<!-- ✅ CORRECT: Use native textarea with shadcn classes -->
<textarea
  bind:value={message}
  onkeydown={(e: KeyboardEvent) => handleKeyDown(e)}
  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
/>
```

**Files to Fix**:
1. `src/lib/components/messages/MessageComposer.svelte`

---

### 2.3 Fix Input Event Handlers (Priority: HIGH)

**Errors Fixed**: ~30

**Issue**: Native inputs need proper event handler types

```svelte
<!-- For native inputs (date, color, etc.) -->
<input
  type="date"
  bind:value={date}
  oninput={(e: Event & { currentTarget: HTMLInputElement }) => handleInput(e)}
  class="..."
/>

<!-- For shadcn Input component -->
<Input
  type="text"
  bind:value={text}
  oninput={(e: Event & { currentTarget: HTMLInputElement }) => handleInput(e)}
/>
```

**Files to Fix**:
- Various files using native inputs after Phase 1 conversions

---

### 2.4 Fix Non-Bindable Properties (Priority: HIGH)

**Errors Fixed**: ~15

**Issue**: Custom components missing `$bindable()` rune

**Pattern**:
```svelte
<!-- Component definition -->
<script lang="ts">
  // ❌ Wrong
  let { value } = $props<{ value?: number }>();
  
  // ✅ Correct: Make it bindable if parent needs to bind
  let { value = $bindable(0) } = $props<{ value?: number }>();
</script>
```

**Files to Fix**:
1. `src/lib/components/survey/SurveyForm.svelte` - `value` prop
2. Any custom components that wrap shadcn components

---

### 2.5 Fix Deprecated Event Directives (Priority: HIGH)

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

## 🎯 Phase 3: Accessibility & Proper HTML Structure (Days 5-6)

**Goal**: Fix accessibility warnings using proper HTML, not by modifying components  
**Target**: 350 → 100 issues (250 fixes, 71% reduction)

### 3.1 Fix Accessibility Warnings (Priority: MEDIUM)

**Errors Fixed**: ~60

#### Issue 1: Click Events Without Keyboard Handlers
Use proper interactive elements:

```svelte
<!-- ❌ WRONG: Non-interactive element with click -->
<div onclick={() => handleClick()}>Click me</div>

<!-- ✅ FIX 1: Use button (preferred) -->
<button type="button" onclick={() => handleClick()}>
  Click me
</button>

<!-- ✅ FIX 2: Add full accessibility support -->
<div
  role="button"
  tabindex="0"
  onclick={() => handleClick()}
  onkeydown={(e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      handleClick();
    }
  }}
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

---

#### Issue 2: Labels Without Associated Controls
Use semantic HTML properly:

```svelte
<!-- ❌ WRONG: Label without control -->
<label>Name</label>
<p>{value}</p>

<!-- ✅ CORRECT: Use descriptive span -->
<div>
  <span class="text-sm font-medium text-muted-foreground">Name</span>
  <p class="text-lg">{value}</p>
</div>
```

**Files to Fix**:
- `src/routes/app/super_admin/agent-bots/[id]/+page.svelte` (multiple labels)
- `src/routes/app/super_admin/platform-apps/[id]/+page.svelte` (multiple labels)

---

### 3.2 Fix Checkbox/Switch Label Association (Priority: MEDIUM)

**Errors Fixed**: ~15

**Use Proper HTML Label Patterns**:

```svelte
<!-- ✅ Method 1: Wrap in Label component -->
<Label class="flex items-center space-x-2 cursor-pointer">
  <Checkbox bind:checked={agreed} />
  <span>I agree to the terms</span>
</Label>

<!-- ✅ Method 2: Use aria-labelledby -->
<div class="flex items-center space-x-2">
  <Checkbox bind:checked={agreed} aria-labelledby="checkbox-label" />
  <Label id="checkbox-label">I agree to the terms</Label>
</div>

<!-- ✅ For Switch: Same patterns -->
<Label class="flex items-center space-x-2">
  <Switch bind:checked={enabled} />
  <span>Enable feature</span>
</Label>
```

**Files to Fix**:
- All files trying to add `id` prop to Checkbox/Switch

---

### 3.3 Fix DropdownMenuContent Usage (Priority: MEDIUM)

**Errors Fixed**: ~10

**Issue**: Using props that don't exist

```svelte
<!-- ❌ WRONG: Trying to use 'align' prop that doesn't exist -->
<DropdownMenuContent align="end">

<!-- ✅ CORRECT: Check bits-ui docs for actual props -->
<DropdownMenuContent side="bottom" align="end">
  <!-- or use sideOffset, alignOffset as per bits-ui docs -->
</DropdownMenuContent>
```

**Files to Fix**:
- `src/lib/components/layout/AppHeader.svelte` (2 instances)
- `src/routes/app/accounts/[accountId]/campaigns/+page.svelte` (2 instances)
- `src/lib/components/notifications/NotificationBell.svelte`

---

### 3.4 Fix Table Component Usage (Priority: MEDIUM)

**Errors Fixed**: ~15

**Issue**: Table components work fine, errors are likely from misuse

```svelte
<!-- ✅ Use Table components as designed -->
<Table.Root>
  <Table.Header>
    <Table.Row>
      <Table.Head>Name</Table.Head>
    </Table.Row>
  </Table.Header>
  <Table.Body>
    <Table.Row>
      <Table.Cell>Value</Table.Cell>
    </Table.Row>
  </Table.Body>
</Table.Root>
```

**Check**: Verify we're not trying to add unsupported props to table components

---

### 3.5 Fix Custom Component Props (Priority: MEDIUM)

**Errors Fixed**: ~30

**Issue**: Custom components may need exported types

**For Custom Components** (not shadcn):
- EmptyState - Ensure proper exports
- CustomAttributes - Ensure proper exports  
- ConversationCard - Check if Preview is properly exported

**Note**: Only modify our own custom components, never shadcn components

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
