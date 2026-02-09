# Component Library Strategy

## Overview

We use `shadcn-svelte` as the base UI library with additional reference components.

## Reference Directories

**IMPORTANT**: These are **External Reference Projects** - do not develop features inside them.

- `shadcn-svelte/` - Core library reference
- `shadcn-svelte-extras/` - Extra components (e.g., PhoneInput)

## Workflow

### 1. Locate
Find the desired component in reference directories.

### 2. Copy
Copy component files to `svelte-ui/src/lib/components/ui/<component>`.

### 3. Implement
Integrate and modify the copied component within `svelte-ui` project.

## Example: Using PhoneInput

**Step 1: Copy**
```bash
cp -r shadcn-svelte-extras/phone-input svelte-ui/src/lib/components/ui/phone-input
```

**Step 2: Import**
```svelte
<script>
  import PhoneInput from '$lib/components/ui/phone-input/phone-input.svelte';
</script>

<PhoneInput bind:value={phoneNumber} />
```

## Available Components

### Core (shadcn-svelte)
- Button
- Input
- Select
- Dialog
- Card
- Table
- Form
- Dropdown Menu
- Popover
- Tooltip
- Badge
- Avatar
- Checkbox
- Radio Group
- Switch
- Textarea
- Label
- Separator
- Tabs
- Alert
- Toast

### Extras (shadcn-svelte-extras)
- PhoneInput
- DatePicker
- ColorPicker
- RichTextEditor
- FileUpload
- ImageCropper

## Component Customization

After copying, you can:
- Modify styles
- Add props
- Extend functionality
- Integrate with stores

**Example:**
```svelte
<script>
  import Button from '$lib/components/ui/button/button.svelte';
  
  let { variant = 'default', size = 'md', ...props } = $props();
</script>

<Button {variant} {size} {...props}>
  <slot />
</Button>

<style>
  /* Custom styles */
</style>
```

## Best Practices

1. **Always copy, never link** - Maintain independence from reference projects
2. **Customize after copying** - Adapt components to project needs
3. **Keep reference updated** - Periodically sync reference directories
4. **Document changes** - Note modifications made to copied components
5. **Test thoroughly** - Ensure copied components work in your context

## Integration with Tailwind

All components use Tailwind CSS classes:

```svelte
<button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
  Click me
</button>
```

## TypeScript Support

Components include TypeScript definitions:

```typescript
interface ButtonProps {
  variant?: 'default' | 'destructive' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  onclick?: () => void;
}
```

## Accessibility

All components follow WCAG 2.1 AA standards:
- Keyboard navigation
- Screen reader support
- Focus management
- ARIA attributes

## Component Structure

```
svelte-ui/src/lib/components/ui/
├── button/
│   ├── button.svelte
│   ├── button.test.ts
│   └── index.ts
├── input/
│   ├── input.svelte
│   ├── input.test.ts
│   └── index.ts
└── ...
```

## Testing Components

```typescript
import { render, fireEvent } from '@testing-library/svelte';
import Button from './button.svelte';

test('button renders and handles click', async () => {
  let clicked = false;
  const { getByRole } = render(Button, {
    props: {
      onclick: () => { clicked = true; }
    }
  });
  
  const button = getByRole('button');
  await fireEvent.click(button);
  expect(clicked).toBe(true);
});
```
