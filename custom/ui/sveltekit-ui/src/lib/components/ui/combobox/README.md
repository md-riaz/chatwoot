# Combobox Component

## Current Implementation

This is a custom implementation of a Combobox component that provides basic autocomplete functionality with search and selection capabilities.

## Future Improvement Recommendation

⚠️ **Note**: This custom implementation should eventually be refactored to use the `bits-ui` Combobox primitive for better accessibility and consistency.

### Why use bits-ui Combobox?

1. **Better Accessibility**: bits-ui provides battle-tested ARIA attributes and keyboard navigation
2. **Consistency**: Matches the patterns used throughout the app with shadcn-svelte
3. **Maintainability**: Leverages a well-maintained, community-supported library
4. **Features**: Handles edge cases and complex interactions out of the box

### Migration Path

When ready to migrate, use the bits-ui Combobox primitive:

```svelte
<script lang="ts">
  import { Combobox } from 'bits-ui';
  
  // bits-ui provides proper keyboard navigation, ARIA attributes,
  // focus management, and composable primitives
</script>

<Combobox.Root>
  <Combobox.Input />
  <Combobox.Content>
    <Combobox.Item value="option1">Option 1</Combobox.Item>
    <Combobox.Item value="option2">Option 2</Combobox.Item>
  </Combobox.Content>
</Combobox.Root>
```

## Current Usage

The current implementation is functional and can be used as follows:

```svelte
<script>
  import Combobox from '$lib/components/ui/combobox/combobox.svelte';
  
  let selected = $state('');
  const options = [
    { value: '1', label: 'Option 1' },
    { value: '2', label: 'Option 2' }
  ];
</script>

<Combobox
  {options}
  bind:value={selected}
  placeholder="Search..."
/>
```
