# Svelte 5 Quick Fix Guide

Quick reference for the most common Svelte 5 migration fixes based on llms.txt documentation.

**Note**: Story/Histoire files have been removed (83 files, 276 errors eliminated). This guide focuses on production component fixes.

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

## 4. Fix Component Props That Don't Accept onclick

### ❌ onclick on Card
```svelte
<Card.Root onclick={() => navigate()}>
  <Card.Header>...</Card.Header>
</Card.Root>
```

### ✅ Wrap in button or add to inner element
```svelte
<!-- Option 1: Wrap in button -->
<button type="button" onclick={() => navigate()}>
  <Card.Root>
    <Card.Header>...</Card.Header>
  </Card.Root>
</button>

<!-- Option 2: Add to Card.Header -->
<Card.Root>
  <Card.Header onclick={() => navigate()}>...</Card.Header>
</Card.Root>
```

---

## 5. Fix Input Type Restrictions

### ❌ Type not in union
```svelte
<Input type="date" bind:value={date} />
<Input type="color" bind:value={color} />
```

### ✅ Option 1: Use native element
```svelte
<input type="date" bind:value={date} class="..." />
<input type="color" bind:value={color} class="..." />
```

### ✅ Option 2: Extend Input component
```typescript
// In Input component
interface InputProps extends HTMLAttributes<HTMLInputElement> {
  type?: 'text' | 'email' | 'password' | 'number' | 'search' | 'tel' | 'url' | 'date' | 'color';
}
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

## 8. Replace Deprecated Event Directives

### ❌ Old directive syntax
```svelte
<form on:submit|preventDefault={handleSubmit}>
```

### ✅ New event handler syntax
```svelte
<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
```

---

## 9. Fix Accessibility Issues

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

## 10. Fix CSS Line-Clamp

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

## 11. Fix Non-Reactive State

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

## 12. Fix Component Prop Restrictions

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

**Table.Cell colspan**:
```svelte
<!-- ❌ -->
<Table.Cell colspan="2">

<!-- ✅ Add to component props or use native -->
<td colspan="2" class="...">
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
