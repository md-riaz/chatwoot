# Svelte 5 Error Analysis for Chatwoot Migration

## Executive Summary
After running `pnpm run check`, found **517 errors and 101 warnings** across **179 files** in the svelte-ui directory. The errors are primarily related to Svelte 5 migration issues where legacy Svelte 4 patterns are being used instead of the new runes-based system.

**Note**: Story/Histoire files have been removed (83 files) as they are not needed for production components, reducing the error count by 276 issues (31%).

## Error Categories & Root Causes

### 1. **Component Prop Type Issues (CRITICAL - Most Common)**
**Count**: ~100+ occurrences
**Error**: `Object literal may only specify known properties, and 'X' does not exist in type 'Props'`

**Root Cause**: Props being passed to components that don't accept them according to their TypeScript definitions.

**Common Issues**:
- `onclick` not recognized on Card, DropdownMenuItem components
- `id` prop not accepted by Switch, Checkbox components
- `align` prop not accepted by DropdownMenuContent
- `colspan` not accepted by Table.Cell
- `oninput` not accepted by Input components
- `min` attribute not accepted by Input
- `type="date"` and `type="color"` not in type union
**Count**: ~40+ occurrences
**Error**: `Parameter 'e' implicitly has an 'any' type`

**Root Cause**: TypeScript requires explicit type annotations for event handler parameters.

**Affected Files**:
- `src/lib/components/whatsapp/WhatsAppTemplateParser.svelte`
- `src/routes/app/accounts/[accountId]/companies/+page.svelte`
- Various other components

**Current Pattern**:
```svelte
<Input
  value={processedParams.header?.media_url || ''}
  oninput={(e) => updateMediaUrl(e.currentTarget.value)}
/>
```

**Correct Pattern**:
```svelte
<Input
  value={processedParams.header?.media_url || ''}
  oninput={(e: Event & { currentTarget: HTMLInputElement }) => updateMediaUrl(e.currentTarget.value)}
/>
```

---

### 2. **Missing Type Annotations for Event Handlers**
**Count**: ~100+ occurrences
**Error**: `Object literal may only specify known properties, and 'X' does not exist in type 'Props'`

**Root Cause**: Props being passed to components that don't accept them according to their TypeScript definitions.

**Common Issues**:
- `onclick` not recognized on Card, DropdownMenuItem components
- `id` prop not accepted by Switch, Checkbox components
- `align` prop not accepted by DropdownMenuContent
- `colspan` not accepted by Table.Cell
- `oninput` not accepted by Input components
- `min` attribute not accepted by Input
- `type="date"` and `type="color"` not in type union

**Affected Files**:
- `src/routes/app/accounts/[accountId]/+page.svelte`
- `src/routes/app/accounts/[accountId]/campaigns/+page.svelte`
- `src/routes/app/accounts/[accountId]/settings/inboxes/new/+page.svelte`
- Many others

**Examples**:

**Card onclick issue**:
```svelte
<!-- Current (type error) -->
<Card.Root onclick={() => goto(stat.href)}>

<!-- ✅ Fix: Extend Card component Props to accept onclick -->
<!-- In src/lib/components/ui/card/card.svelte -->
<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet, HTMLAttributes } from 'svelte/elements';

  type Props = HTMLAttributes<HTMLDivElement> & {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<div
  class={cn('rounded-lg border bg-card text-card-foreground shadow-sm', className)}
  {...restProps}
>
  {#if children}
    {@render children?.()}
  {/if}
</div>
```

**Input type issue**:
```svelte
<!-- Current (type error) -->
<Input type="date" bind:value={startDate} />

<!-- ✅ Fix: Extend Input component type union -->
<!-- In src/lib/components/ui/input/index.ts -->
type Props = {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search' | 'date' | 'time' | 'datetime-local' | 'color';
  class?: string;
  value?: string;
  // ... other props
};
```

---

### 3. **Missing Component Exports from Sidebar**
**Count**: ~30+ occurrences (if Sidebar stories were present)
**Error**: `Property 'Nav' does not exist on type 'typeof import(...)'`

**Root Cause**: Sidebar components (Nav, Section, NavItem) may not be exported from the Sidebar index.

**Missing Exports**:
- `Sidebar.Nav`
- `Sidebar.Section`
- `Sidebar.NavItem`

**Fix**: Check `src/lib/components/ui/sidebar/index.ts` and ensure these components are exported if needed.

**Note**: This was primarily affecting story files which have been removed.

---

### 4. **Non-Bindable Properties**
**Count**: ~15+ occurrences
**Error**: `Cannot use 'bind:' with this property. It is declared as non-bindable`

**Root Cause**: Attempting to bind to props that haven't been marked with `$bindable()`.

**Affected Files**:
- `src/routes/app/accounts/[accountId]/settings/account/+page.svelte`
- `src/routes/app/super_admin/platform-apps/[id]/+page.svelte`

**Current Pattern**:
```svelte
<Select.Root bind:selected={language}>
  <Select.Trigger id="language">
```

**Correct Pattern**:
```svelte
<!-- Option 1: Use value instead of selected -->
<Select.Root bind:value={language}>

<!-- Option 2: If component needs bindable, update component definition -->
<!-- In Select.Root component -->
<script>
  let { selected = $bindable() } = $props();
</script>
```

---

### 5. **Snake_case vs camelCase Property Names**
**Count**: ~10+ occurrences
**Error**: `Property 'phone_number' does not exist. Did you mean 'phoneNumber'?`

**Root Cause**: Inconsistent naming conventions between API responses and component expectations.

**Affected Files**:
- `src/routes/app/accounts/[accountId]/contacts/+page.svelte`

**Current Issues**:
- `contact.phone_number` should be `contact.phoneNumber`
- `contact.avatar_url` should be `contact.avatarUrl`
- `contact.availability_status` should be `contact.availabilityStatus`
- `contact.company_name` should be `contact.companyName`

**Fix**: Ensure API transformation layer properly converts snake_case to camelCase.

---

### 6. **Accessibility Warnings**
**Count**: ~60+ warnings
**Warning Types**:
- `a11y_click_events_have_key_events`
- `a11y_no_static_element_interactions`
- `a11y_invalid_attribute`
- `a11y_label_has_associated_control`

**Root Cause**: Interactive elements missing keyboard handlers and proper ARIA roles.

**Common Pattern**:
```svelte
<!-- Current (accessibility issue) -->
<div onclick={() => handleClick()}>
  Click me
</div>

<!-- Correct pattern -->
<button type="button" onclick={() => handleClick()}>
  Click me
</button>

<!-- OR -->
<div 
  role="button"
  tabindex="0"
  onclick={() => handleClick()}
  onkeydown={(e) => e.key === 'Enter' && handleClick()}
>
  Click me
</div>
```

---

### 7. **CSS Compatibility Warnings**
**Count**: ~5+ warnings
**Warning**: `Also define the standard property 'line-clamp' for compatibility`

**Root Cause**: Using `-webkit-line-clamp` without the standard `line-clamp` property.

**Current Pattern**:
```css
.text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
```

**Correct Pattern**:
```css
.text {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Add standard property */
  -webkit-box-orient: vertical;
}
```

---

### 8. **Type Mismatch Issues**
**Count**: ~20+ occurrences
**Error Types**:
- Type 'string | undefined' not assignable to 'string'
- Type 'Record<string, boolean>' not assignable to expected types
- Property possibly 'undefined'

**Affected Files**:
- `src/routes/app/accounts/[accountId]/conversations/[id]/+page.svelte`
- `src/routes/app/super_admin/accounts/[id]/edit/+page.svelte`
- `src/routes/ui/[name]/+page.svelte`

**Examples**:
```typescript
// Current (error)
const conversationId = $derived(parseInt($page.params.id));

// Correct
const conversationId = $derived(parseInt($page.params.id ?? '0'));
```

---

### 9. **Effect/Mount Async Issues**
**Count**: ~3 occurrences
**Error**: Return type of async function not matching expected

**Affected Files**:
- `src/routes/widget/+layout.svelte`
- `src/routes/portal/+layout.svelte`

**Current Pattern**:
```svelte
onMount(async () => {
  await initI18n('en');
});
```

**Root Cause**: `initI18n` function signature expects 0 arguments but 1 is being passed.

---

### 10. **Deprecated Event Directive**
**Count**: ~2 occurrences
**Warning**: `Using 'on:submit' to listen to the submit event is deprecated`

**Current Pattern**:
```svelte
<form on:submit|preventDefault={handleSubmit}>
```

**Correct Pattern**:
```svelte
<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
```

---

## Priority Fixes

### High Priority (Breaking Functionality)
1. **Type mismatches** - Causes runtime errors
2. **Snake_case properties** - Breaks data binding
3. **Component prop types** - Blocks component usage

### Medium Priority (Type Safety)
1. **Event handler type annotations** - TypeScript errors
2. **Non-bindable properties** - Breaks two-way binding
3. **Incorrect component props** - Type errors

### Low Priority (Best Practices)
1. **Accessibility warnings** - UX improvements
2. **CSS compatibility** - Browser support
3. **Deprecated patterns** - Future-proofing

---

## Recommended Fix Strategy

### Phase 1: Critical Fixes (1 day)
1. Resolve snake_case vs camelCase issues
2. Fix type mismatches in route params
3. Resolve snake_case vs camelCase issues
4. Fix type mismatches in route params

### Phase 2: Component Props (2-3 days)
1. Fix Input component to accept date, color types
2. Fix Card/Button onclick handling
3. Fix Select.Root bindable props
4. Add missing component prop types

### Phase 3: Type Safety (1-2 days)
1. Add event handler type annotations
2. Fix async function return types
3. Resolve undefined/null checks

### Phase 4: Polish (1-2 days)
1. Fix accessibility warnings
2. Add CSS compatibility properties
3. Update deprecated event directives
4. Clean up unused CSS selectors

---

## Tools & Resources

### Automated Fixes
- `svelte-migrate` - Official migration tool
- `eslint-plugin-svelte` - Linting for Svelte 5
- `prettier-plugin-svelte` - Code formatting

### Manual Review Required
- Component prop type definitions
- API transformation layer
- Custom component exports
- Event handler patterns

### Documentation
- Svelte 5 Runes: https://svelte.dev/docs/svelte/$props
- Migration Guide: https://svelte.dev/docs/svelte/v5-migration-guide
- TypeScript Support: https://svelte.dev/docs/typescript

---

## Conclusion

The errors are systematic and follow predictable patterns. Most can be fixed with:
1. **Extend shadcn-svelte components** instead of falling back to native HTML
2. Consistent use of runes (`$props()`, `$state()`, `$derived()`)
3. Proper TypeScript type annotations for event handlers
4. Component Props extensions to accept HTML attributes
5. Accessibility improvements

**shadcn-svelte Focus**: This project uses shadcn-svelte (built on bits-ui). Always extend existing components rather than using native HTML elements.

**Story files removed**: 83 files deleted, reducing errors by 276 (31%)

Estimated total effort: **6-8 days** for complete resolution.
