# Select.Value Issue - Critical Fix Required

## ❌ Problem Identified

The codebase is using `<Select.Value />` component which **does not exist** in Bits UI Select component. This is causing TypeScript errors.

## 📚 Correct Pattern from Bits UI Documentation

According to the official Bits UI documentation, you should **render the selected label directly** in the trigger:

```svelte
<script lang="ts">
const selectedLabel = $derived(
  value 
    ? themes.find((theme) => theme.value === value)?.label 
    : "Select a theme"
);
</script>

<Select.Trigger>
  {selectedLabel}  <!-- Render directly, NOT <Select.Value /> -->
</Select.Trigger>
```

## 🔧 Fix Pattern

### Pattern 1: Simple Value Display
```svelte
<!-- ❌ WRONG -->
<Select.Root bind:value={timezone} type="single">
  <Select.Trigger>
    <Select.Value placeholder="Select timezone" />
  </Select.Trigger>
</Select.Root>

<!-- ✅ CORRECT -->
<Select.Root bind:value={timezone} type="single">
  <Select.Trigger>
    {timezone || "Select timezone"}
  </Select.Trigger>
</Select.Root>
```

### Pattern 2: With Label Lookup
```svelte
<!-- ❌ WRONG -->
<Select.Root bind:value={selectedCategory} type="single">
  <Select.Trigger>
    <Select.Value placeholder="All Categories" />
  </Select.Trigger>
  <Select.Content>
    {#each categories as cat}
      <Select.Item value={cat.id} label={cat.name}>{cat.name}</Select.Item>
    {/each}
  </Select.Content>
</Select.Root>

<!-- ✅ CORRECT -->
<script>
const selectedCategoryLabel = $derived(
  categories.find(c => c.id === selectedCategory)?.name || "All Categories"
);
</script>

<Select.Root bind:value={selectedCategory} type="single">
  <Select.Trigger>
    {selectedCategoryLabel}
  </Select.Trigger>
  <Select.Content>
    {#each categories as cat}
      <Select.Item value={cat.id} label={cat.name}>{cat.name}</Select.Item>
    {/each}
  </Select.Content>
</Select.Root>
```

### Pattern 3: With Items Array
```svelte
<!-- ✅ CORRECT - Using items prop -->
<script>
const items = [
  { value: "apple", label: "Apple" },
  { value: "banana", label: "Banana" }
];

const selectedLabel = $derived(
  items.find(item => item.value === value)?.label || "Select fruit"
);
</script>

<Select.Root bind:value type="single" {items}>
  <Select.Trigger>
    {selectedLabel}
  </Select.Trigger>
  <Select.Content>
    {#each items as item}
      <Select.Item value={item.value} label={item.label}>
        {item.label}
      </Select.Item>
    {/each}
  </Select.Content>
</Select.Root>
```

## 📋 Files That Need Fixing

### High Priority (Causing TypeScript Errors)

1. **inboxes/new/+page.svelte** - Line 1004
2. **AutoResolve.svelte** - Line 118
3. **account/+page.svelte** - Line 123
4. **new-conversation-form.svelte** - Line 33
5. **contact-list.svelte** - Line 155
6. **articles-page.svelte** - Lines 106, 119
7. **portal-config.svelte** - Line 114
8. **search.svelte** - Lines 78, 92
9. **article-editor.svelte** - Lines 144, 158
10. **condition-row.svelte** - Lines 88, 99, 111, 124
11. **ConversationFilters.svelte** - Line 99
12. **AttributeForm.svelte** - Lines 185, 213
13. **WhatsAppCampaignForm.svelte** - Lines 206, 229
14. **AgentForm.svelte** - Line 147
15. **SMSCampaignForm.svelte** - Line 187
16. **LiveChatCampaignForm.svelte** - Line 164
17. **ui/[name]/+page.svelte** - Line 130

## 🎯 Recommended Fix Strategy

### Option 1: Create a Reusable Wrapper Component (RECOMMENDED)

Create a custom Select component that handles the value display logic:

```svelte
<!-- MySelect.svelte -->
<script lang="ts">
import { Select, type WithoutChildren } from "bits-ui";

type Props = WithoutChildren<Select.RootProps> & {
  placeholder?: string;
  items: { value: string; label: string; disabled?: boolean }[];
  contentProps?: WithoutChildren<Select.ContentProps>;
};

let {
  value = $bindable(),
  items,
  contentProps,
  placeholder = "Select an option",
  ...restProps
}: Props = $props();

const selectedLabel = $derived(
  items.find((item) => item.value === value)?.label || placeholder
);
</script>

<Select.Root bind:value={value as never} {...restProps}>
  <Select.Trigger>
    {selectedLabel}
  </Select.Trigger>
  <Select.Portal>
    <Select.Content {...contentProps}>
      {#each items as { value, label, disabled } (value)}
        <Select.Item {value} {label} {disabled}>
          {label}
        </Select.Item>
      {/each}
    </Select.Content>
  </Select.Portal>
</Select.Root>
```

### Option 2: Fix Each File Individually

For each file, add a `$derived` variable and replace `<Select.Value />` with the variable.

## 🚨 Why This Wasn't Caught Earlier

1. **select-value.svelte exists** - There's a file in the codebase, but it's NOT exported from index.ts
2. **TypeScript wasn't checked** - The analysis focused on runtime behavior, not TypeScript compilation
3. **Pattern looked correct** - `Select.Value` seems like a reasonable API, but it's not part of Bits UI

## ✅ Action Items

1. **Remove select-value.svelte** - It's not used and causes confusion
2. **Fix all 17+ files** - Replace `<Select.Value />` with proper value display
3. **Update documentation** - Add correct pattern to project docs
4. **Add linting rule** - Prevent future usage of non-existent components

## 📖 Reference

- [Bits UI Select Documentation](https://bits-ui.com/docs/components/select)
- See "Reusable Components" section for the correct pattern
