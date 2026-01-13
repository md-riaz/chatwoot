# Svelte 5 Quick Fix Guide

Quick reference for the most common Svelte 5 migration fixes based on llms.txt documentation.

**Note**: Story/Histoire files have been removed (83 files, 276 errors eliminated). This guide focuses on production component fixes.

## 🎨 About shadcn-svelte Components

This project uses **[shadcn-svelte](https://www.shadcn-svelte.com/)** components built on **[bits-ui](https://www.bits-ui.com/)** primitives. When fixing type errors:

✅ **DO**: Extend shadcn-svelte components to accept additional props  
✅ **DO**: Use shadcn-svelte components consistently (Input, Select, Card, etc.)  
✅ **DO**: Add proper TypeScript types to component Props  
❌ **DON'T**: Fall back to native HTML elements (`<input>`, `<select>`)  
❌ **DON'T**: Wrap shadcn components unnecessarily  

**Key Resources**:
- shadcn-svelte docs: https://www.shadcn-svelte.com/docs/components
- bits-ui docs: https://www.bits-ui.com/docs/components
- Component locations: `src/lib/components/ui/`

---

---

## 1. Replace `export let` with `$props()` (LEGACY PATTERN - Mostly in story files)

**Note**: This pattern was primarily in story files which have been removed. Production components mostly already use proper prop patterns.

### ❌ Old (Svelte 4)
```svelte
<script lang="ts">
  export let title: string;
  export let count: number = 0;
</script>
```

### ✅ New (Svelte 5)
```svelte
<script lang="ts">
  let { title, count = 0 } = $props<{ title: string; count?: number }>();
</script>
```

**With rest props**:
```svelte
<script lang="ts">
  let { title, count = 0, ...rest } = $props<{ 
    title: string; 
    count?: number;
    [key: string]: any;
  }>();
</script>
```

---

## 2. Add Type Annotations to Event Handlers

### ❌ Missing Type
```svelte
<Input oninput={(e) => handleChange(e.currentTarget.value)} />
```

### ✅ With Type
```svelte
<Input oninput={(e: Event & { currentTarget: HTMLInputElement }) => handleChange(e.currentTarget.value)} />
```

**Common event types**:
```typescript
// Input events
(e: Event & { currentTarget: HTMLInputElement }) => ...

// Click events
(e: MouseEvent) => ...

// Keyboard events
(e: KeyboardEvent) => ...

// Form submit
(e: SubmitEvent) => ...
```

---

## 3. Fix Non-Bindable Properties

### ❌ Binding to non-bindable prop
```svelte
<Select.Root bind:selected={value}>
```

### ✅ Use correct prop name
```svelte
<Select.Root bind:value={value}>
```

**For custom components**, mark props as bindable:
```svelte
<script>
  let { value = $bindable() } = $props();
</script>
```

---

## 4. Extend shadcn-svelte Components for Event Handlers

### ❌ onclick on Card (Type Error)
```svelte
<Card.Root onclick={() => navigate()}>
  <Card.Header>...</Card.Header>
</Card.Root>
```

### ✅ Extend Card component to accept HTML attributes
Update the Card component to accept all standard HTML div attributes:

```svelte
<!-- src/lib/components/ui/card/card.svelte -->
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

Then use normally:
```svelte
<Card.Root onclick={() => navigate()}>
  <Card.Header>...</Card.Header>
</Card.Root>
```

---

## 5. Extend Input Component for Additional Types

### ❌ Type not in union
```svelte
<Input type="date" bind:value={date} />
<Input type="color" bind:value={color} />
```

### ✅ Extend Input component type definition
Update the Input component's Props type to include additional input types:

```typescript
// src/lib/components/ui/input/index.ts
type Props = {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search' 
    | 'date' | 'time' | 'datetime-local' | 'month' | 'week' | 'color' 
    | 'file' | 'range' | 'hidden';
  class?: string;
  value?: string;
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  required?: boolean;
  name?: string;
  id?: string;
  min?: string | number;  // Add for date/number inputs
  max?: string | number;  // Add for date/number inputs
  step?: string | number; // Add for number/range inputs
  accept?: string;        // Add for file inputs
  'aria-label'?: string;
  'aria-describedby'?: string;
};
```

Then use the shadcn-svelte Input component normally:
```svelte
<Input type="date" bind:value={date} class="w-40" />
<Input type="color" bind:value={color} />
<Input type="number" min="0" max="100" bind:value={count} />
```

---

## 6. Fix Snake_case Property Names

### ❌ Using snake_case (API response)
```svelte
<p>{contact.phone_number}</p>
<p>{contact.avatar_url}</p>
```

### ✅ Use camelCase (after transformation)
```svelte
<p>{contact.phoneNumber}</p>
<p>{contact.avatarUrl}</p>
```

**Note**: The API transformation layer should automatically convert snake_case to camelCase.

---

## 7. Fix Type Mismatches with Undefined

### ❌ Missing null check
```typescript
const id = $derived(parseInt($page.params.id));
```

### ✅ With null coalescing
```typescript
const id = $derived(parseInt($page.params.id ?? '0'));
```

**Optional chaining**:
```typescript
const name = $derived(user?.profile?.name ?? 'Guest');
```

---

## 8. Extend DropdownMenuItem for onclick Support

### ❌ onclick not supported
```svelte
<DropdownMenuItem onclick={handleAction}>
  Action Item
</DropdownMenuItem>
```

### ✅ Extend DropdownMenuItem component
shadcn-svelte dropdown menu is built on bits-ui which uses `onselect` instead of `onclick`. Update the component to support the familiar `onclick` pattern:

```svelte
<!-- src/lib/components/ui/dropdown-menu/dropdown-menu-item.svelte -->
<script lang="ts">
  import { DropdownMenu as DropdownMenuPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    inset?: boolean;
    children?: Snippet;
    onclick?: () => void;  // Add onclick support
    disabled?: boolean;
  };

  let { 
    class: className, 
    inset = false, 
    children, 
    onclick,
    ...restProps 
  }: Props = $props();
</script>

<DropdownMenuPrimitive.Item
  class={cn(
    'relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
    inset && 'pl-8',
    className
  )}
  onselect={onclick}  {/* Map onclick to bits-ui's onselect */}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</DropdownMenuPrimitive.Item>
```

---

## 9. Replace Deprecated Event Directives

### ❌ Old directive syntax
```svelte
<form on:submit|preventDefault={handleSubmit}>
```

### ✅ New event handler syntax
```svelte
<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
```

---

## 10. Fix Accessibility Issues

### ❌ div with onclick
```svelte
<div onclick={() => handleClick()}>
  Click me
</div>
```

### ✅ Use button
```svelte
<button type="button" onclick={() => handleClick()}>
  Click me
</button>
```

### ✅ OR add ARIA attributes
```svelte
<div 
  role="button"
  tabindex="0"
  onclick={() => handleClick()}
  onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && handleClick()}
>
  Click me
</div>
```

---

## 11. Fix CSS Line-Clamp

### ❌ Webkit only
```css
.truncated {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
```

### ✅ With standard property
```css
.truncated {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Add standard property */
  -webkit-box-orient: vertical;
}
```

---

## 12. Fix Non-Reactive State

### ❌ Not wrapped in $state
```svelte
<script>
  let data: User | null = null; // Not reactive
  
  onMount(async () => {
    data = await fetchUser();
  });
</script>
```

### ✅ Using $state
```svelte
<script>
  let data = $state<User | null>(null); // Reactive
  
  onMount(async () => {
    data = await fetchUser();
  });
</script>
```

---

## 13. Fix Component Prop Restrictions

### Common issues and solutions

**Switch/Checkbox id prop**:
```svelte
<!-- ❌ -->
<Switch id="my-switch" bind:checked={value} />

<!-- ✅ Wrap with label -->
<label for="my-switch">
  <Switch bind:checked={value} />
</label>
```

**DropdownMenuItem onclick**:
```svelte
<!-- ❌ -->
<DropdownMenuItem onclick={handleClick}>

<!-- ✅ Use as child -->
<DropdownMenuItem>
  <button onclick={handleClick}>Action</button>
</DropdownMenuItem>
```

---

## Testing Your Fixes

After making changes, run:

```bash
cd laravel-svelte-port/svelte-ui
pnpm run check
```

Look for reduction in error count. Aim to fix errors by category for efficiency.

---

## Priority Order for Fixes

1. **Critical**: Type mismatches (runtime errors)
2. **Critical**: Snake_case properties (data binding)
3. **High**: Event handler type annotations (type safety)
4. **High**: Component prop types (type safety)
5. **Medium**: Accessibility (UX)
6. **Low**: CSS compatibility (browser support)

**Note**: export let → $props() was primarily in story files (now removed)

---

## Resources

- **Svelte 5 Runes**: See llms.txt in this directory
- **Migration Guide**: https://svelte.dev/docs/svelte/v5-migration-guide
- **Full Analysis**: See SVELTE5_ERROR_ANALYSIS.md
- **File Breakdown**: See SVELTE5_FILE_BREAKDOWN.md
