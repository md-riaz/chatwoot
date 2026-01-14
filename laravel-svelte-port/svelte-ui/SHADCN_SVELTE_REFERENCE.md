# shadcn-svelte Component Reference

**Source**: `/laravel-svelte-port/shadcn-svelte` - Official shadcn-svelte repository  
**Created**: 2026-01-14  
**Purpose**: Quick reference for available shadcn-svelte components and their proper usage

This document provides a comprehensive reference of all available shadcn-svelte components found in the local repository, along with their proper usage patterns based on official documentation.

---

## 📚 Component Categories

### Form Components
- [Input](#input)
- [Textarea](#textarea)
- [Select](#select)
- [Checkbox](#checkbox)
- [Radio Group](#radio-group)
- [Switch](#switch)
- [Slider](#slider)
- [Native Select](#native-select)

### Date & Time
- [Calendar](#calendar)
- [Date Picker](#date-picker)
- [Range Calendar](#range-calendar)

### Layout & Container
- [Card](#card)
- [Sheet](#sheet)
- [Dialog](#dialog)
- [Drawer](#drawer)
- [Sidebar](#sidebar)
- [Resizable](#resizable)
- [Separator](#separator)

### Navigation
- [Dropdown Menu](#dropdown-menu)
- [Context Menu](#context-menu)
- [Menubar](#menubar)
- [Navigation Menu](#navigation-menu)
- [Tabs](#tabs)
- [Breadcrumb](#breadcrumb)
- [Pagination](#pagination)

### Feedback
- [Alert](#alert)
- [Alert Dialog](#alert-dialog)
- [Toast/Sonner](#sonner)
- [Progress](#progress)
- [Spinner](#spinner)
- [Skeleton](#skeleton)

### Data Display
- [Table](#table)
- [Data Table](#data-table)
- [Avatar](#avatar)
- [Badge](#badge)
- [Chart](#chart)

### Overlay
- [Popover](#popover)
- [Tooltip](#tooltip)
- [Hover Card](#hover-card)
- [Command](#command)

### Advanced
- [Accordion](#accordion)
- [Collapsible](#collapsible)
- [Carousel](#carousel)
- [Scroll Area](#scroll-area)
- [Toggle](#toggle)
- [Toggle Group](#toggle-group)
- [Combobox](#combobox)

---

## 📖 Component Details

### Input

**Purpose**: Text input field  
**Supports**: All native HTML input attributes via `{...restProps}`  
**Docs**: `docs/content/components/input.md`

```svelte
<script lang="ts">
  import { Input } from "$lib/components/ui/input";
</script>

<!-- Basic usage -->
<Input placeholder="Email" />

<!-- With type -->
<Input type="email" placeholder="Email" />

<!-- With number constraints (SUPPORTED via ...restProps) -->
<Input type="number" min={0} max={100} step={1} />

<!-- With events (SUPPORTED via ...restProps) -->
<Input
  type="text"
  oninput={(e: Event & { currentTarget: HTMLInputElement }) => console.log(e.currentTarget.value)}
/>

<!-- File input example (from docs) -->
<Input type="file" />
```

**Key Features**:
- ✅ Supports all native `<input>` attributes
- ✅ Uses `{...restProps}` pattern
- ✅ Event handlers work directly (oninput, onfocus, onblur, etc.)
- ✅ Can use with `type="number"`, `type="email"`, etc.

---

### Textarea

**Purpose**: Multi-line text input  
**Supports**: All native HTML textarea attributes via `{...restProps}`  
**Docs**: `docs/content/components/textarea.md`

```svelte
<script lang="ts">
  import { Textarea } from "$lib/components/ui/textarea";
</script>

<!-- Basic usage -->
<Textarea placeholder="Type your message here." />

<!-- With events (SUPPORTED via ...restProps) -->
<Textarea
  placeholder="Message"
  onkeydown={(e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      handleSend();
    }
  }}
/>

<!-- With rows -->
<Textarea rows={10} />
```

**Key Features**:
- ✅ Supports all native `<textarea>` attributes
- ✅ Uses `{...restProps}` pattern
- ✅ Event handlers work directly (onkeydown, oninput, onfocus, etc.)
- ✅ Bindable value and ref

---

### Date Picker

**Purpose**: Date selection with calendar popup  
**Uses**: Composition of Popover + Calendar  
**Docs**: `docs/content/components/date-picker.md`  
**Important**: Uses `@internationalized/date` library

```svelte
<script lang="ts">
  import CalendarIcon from "@lucide/svelte/icons/calendar";
  import {
    type DateValue,
    DateFormatter,
    getLocalTimeZone,
    parseDate
  } from "@internationalized/date";
  import { cn } from "$lib/utils.js";
  import { Button } from "$lib/components/ui/button";
  import { Calendar } from "$lib/components/ui/calendar";
  import * as Popover from "$lib/components/ui/popover";

  const df = new DateFormatter("en-US", {
    dateStyle: "long",
  });

  let value = $state<DateValue>();
  
  // Convert from string to DateValue
  // value = parseDate('2024-01-15');
  
  // Convert from DateValue to string
  // const dateString = value.toString(); // "2024-01-15"
</script>

<Popover.Root>
  <Popover.Trigger>
    {#snippet child({ props })}
      <Button
        variant="outline"
        class={cn(
          "w-[280px] justify-start text-start font-normal",
          !value && "text-muted-foreground"
        )}
        {...props}
      >
        <CalendarIcon class="me-2 size-4" />
        {value ? df.format(value.toDate(getLocalTimeZone())) : "Select a date"}
      </Button>
    {/snippet}
  </Popover.Trigger>
  <Popover.Content class="w-auto p-0">
    <Calendar
      bind:value
      type="single"
      initialFocus
      captionLayout="dropdown"
    />
  </Popover.Content>
</Popover.Root>
```

**Key Features**:
- ✅ Professional date picker with calendar UI
- ✅ Uses `DateValue` type from `@internationalized/date`
- ✅ Supports min/max date constraints
- ✅ Multiple variants: with range, with time, natural language
- ⚠️ Don't use native `<input type="date">` - use this instead

**Date Conversion**:
```typescript
import { parseDate } from '@internationalized/date';

// String → DateValue
const dateValue = parseDate('2024-01-15');

// DateValue → String
const dateString = dateValue.toString(); // "2024-01-15"

// DateValue → JavaScript Date
const jsDate = dateValue.toDate(getLocalTimeZone());
```

---

### Calendar

**Purpose**: Calendar component (used by Date Picker)  
**Docs**: `docs/content/components/calendar.md`

```svelte
<script lang="ts">
  import { Calendar } from "$lib/components/ui/calendar";
  import type { DateValue } from "@internationalized/date";
  
  let value = $state<DateValue>();
</script>

<Calendar bind:value type="single" />
```

---

### Select

**Purpose**: Dropdown selection  
**Built on**: bits-ui Select  
**Docs**: `docs/content/components/select.md`  
**API**: https://bits-ui.com/docs/components/select#api-reference

```svelte
<script lang="ts">
  import * as Select from "$lib/components/ui/select";
  
  let selected = $state<string>();
</script>

<!-- IMPORTANT: Use 'value' NOT 'selected' -->
<Select.Root bind:value={selected}>
  <Select.Trigger class="w-[180px]">
    <Select.Value placeholder="Select theme" />
  </Select.Trigger>
  <Select.Content>
    <Select.Item value="light">Light</Select.Item>
    <Select.Item value="dark">Dark</Select.Item>
    <Select.Item value="system">System</Select.Item>
  </Select.Content>
</Select.Root>
```

**Key Features**:
- ✅ Use `bind:value` (NOT `bind:selected`)
- ✅ Based on bits-ui primitives
- ✅ Supports grouping, scrollable lists
- ✅ Fully accessible

---

### Switch

**Purpose**: Toggle switch  
**Built on**: bits-ui Switch  
**Docs**: `docs/content/components/switch.md`  
**API**: https://bits-ui.com/docs/components/switch#api-reference

```svelte
<script lang="ts">
  import { Switch } from "$lib/components/ui/switch";
  import { Label } from "$lib/components/ui/label";
  
  let checked = $state(false);
</script>

<!-- Method 1: Wrap in Label -->
<Label class="flex items-center space-x-2 cursor-pointer">
  <Switch bind:checked />
  <span>Enable notifications</span>
</Label>

<!-- Method 2: Use aria-labelledby -->
<div class="flex items-center space-x-2">
  <Switch bind:checked aria-labelledby="switch-label" />
  <Label id="switch-label">Enable notifications</Label>
</div>
```

**Key Features**:
- ✅ Use `bind:checked` for state
- ❌ Don't try to add `id` prop
- ✅ Use Label wrapping or aria-labelledby for association

---

### Checkbox

**Purpose**: Checkbox input  
**Similar to**: Switch (same label association patterns)

```svelte
<script lang="ts">
  import { Checkbox } from "$lib/components/ui/checkbox";
  import { Label } from "$lib/components/ui/label";
  
  let checked = $state(false);
</script>

<Label class="flex items-center space-x-2 cursor-pointer">
  <Checkbox bind:checked />
  <span>Accept terms and conditions</span>
</Label>
```

---

### Card

**Purpose**: Container component  
**Docs**: `docs/content/components/card.md`  
**Note**: Not an interactive element

```svelte
<script lang="ts">
  import * as Card from "$lib/components/ui/card";
</script>

<!-- Basic Card -->
<Card.Root>
  <Card.Header>
    <Card.Title>Card Title</Card.Title>
    <Card.Description>Card Description</Card.Description>
  </Card.Header>
  <Card.Content>
    <p>Card Content</p>
  </Card.Content>
  <Card.Footer>
    <p>Card Footer</p>
  </Card.Footer>
</Card.Root>

<!-- ❌ WRONG: Card is not clickable -->
<Card.Root onclick={() => navigate()}>

<!-- ✅ CORRECT: Wrap in button for clickable card -->
<button type="button" onclick={() => navigate()}>
  <Card.Root>
    <Card.Header>
      <Card.Title>Clickable Card</Card.Title>
    </Card.Header>
  </Card.Root>
</button>
```

**Key Features**:
- ✅ Semantic component structure
- ❌ Not designed to be interactive
- ✅ Create wrapper component if you need clickable cards

---

### Dropdown Menu

**Purpose**: Menu triggered by button  
**Built on**: bits-ui DropdownMenu  
**Docs**: `docs/content/components/dropdown-menu.md`  
**API**: https://bits-ui.com/docs/components/dropdown-menu#api-reference

```svelte
<script lang="ts">
  import * as DropdownMenu from "$lib/components/ui/dropdown-menu";
</script>

<DropdownMenu.Root>
  <DropdownMenu.Trigger>Open</DropdownMenu.Trigger>
  <DropdownMenu.Content>
    <DropdownMenu.Group>
      <DropdownMenu.Label>My Account</DropdownMenu.Label>
      <DropdownMenu.Separator />
      
      <!-- IMPORTANT: Use 'onselect' NOT 'onclick' -->
      <DropdownMenu.Item onselect={() => handleProfile()}>
        Profile
      </DropdownMenu.Item>
      <DropdownMenu.Item onselect={() => handleBilling()}>
        Billing
      </DropdownMenu.Item>
    </DropdownMenu.Group>
  </DropdownMenu.Content>
</DropdownMenu.Root>
```

**Key Features**:
- ✅ Use `onselect` for item actions (bits-ui pattern)
- ❌ Don't use `onclick` on items
- ✅ Supports checkboxes, radio groups
- ✅ Can trigger dialogs

---

### Dialog

**Purpose**: Modal dialog overlay  
**Built on**: bits-ui Dialog

```svelte
<script lang="ts">
  import * as Dialog from "$lib/components/ui/dialog";
  
  let open = $state(false);
</script>

<Dialog.Root bind:open>
  <Dialog.Trigger>Open Dialog</Dialog.Trigger>
  <Dialog.Content>
    <Dialog.Header>
      <Dialog.Title>Dialog Title</Dialog.Title>
      <Dialog.Description>Dialog description</Dialog.Description>
    </Dialog.Header>
    <!-- Content here -->
    <Dialog.Footer>
      <!-- Actions here -->
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
```

**For Custom Wrapper Components**:
```svelte
<!-- In your custom dialog component -->
<script lang="ts">
  import * as Dialog from "$lib/components/ui/dialog";
  
  // IMPORTANT: Use $bindable for open prop
  let { open = $bindable(false) } = $props<{ open?: boolean }>();
</script>

<Dialog.Root bind:open>
  <!-- ... -->
</Dialog.Root>
```

---

### Button

**Purpose**: Button component with variants  
**Supports**: All native button attributes via `{...restProps}`

```svelte
<script lang="ts">
  import { Button } from "$lib/components/ui/button";
</script>

<Button variant="default">Default</Button>
<Button variant="destructive">Destructive</Button>
<Button variant="outline">Outline</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="ghost">Ghost</Button>
<Button variant="link">Link</Button>

<!-- With event handler -->
<Button onclick={() => handleClick()}>
  Click me
</Button>
```

---

## 🚫 Components NOT Available in shadcn-svelte

The following are NOT shadcn-svelte components. Create custom wrappers if needed:

### ColorInput (Not Available)
No native color picker in shadcn-svelte. Create custom wrapper:

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
  }>();
</script>

<input
  type="color"
  bind:value
  class={cn(
    'border-input bg-background h-10 w-full rounded-md border px-1 py-1',
    className
  )}
  {...restProps}
/>
```

### FileUpload
Check if exists in ui components, otherwise create wrapper around native file input.

---

## 📝 Best Practices

### 1. Check Component Source First
Before assuming a component doesn't support something, check if it uses `{...restProps}`:

```svelte
<!-- Most shadcn components pass through props -->
<input
  bind:this={ref}
  bind:value
  {...restProps}  <!-- This means ALL HTML attributes are supported -->
/>
```

### 2. Use bits-ui Documentation
Many shadcn-svelte components are built on bits-ui. Check bits-ui docs for:
- API reference
- Event handlers
- Props

Links in component docs:
- `doc: https://bits-ui.com/docs/components/[component-name]`
- `api: https://bits-ui.com/docs/components/[component-name]#api-reference`

### 3. Date Handling
Always use `@internationalized/date` for date components:

```typescript
import { parseDate } from '@internationalized/date';

// ✅ Use DateValue
let date: DateValue = parseDate('2024-01-15');

// ❌ Don't use native Date or strings directly
let date: string = '2024-01-15'; // Wrong type
```

### 4. Event Handlers
Different components use different event patterns:

| Component | Event Handler | Example |
|-----------|--------------|---------|
| Input | Standard HTML | `oninput={handler}` |
| Textarea | Standard HTML | `onkeydown={handler}` |
| Button | Standard HTML | `onclick={handler}` |
| DropdownMenu.Item | bits-ui | `onselect={handler}` |
| Select | bits-ui | `bind:value` |

### 5. Label Association
For form controls:

```svelte
<!-- ✅ Method 1: Wrap in Label -->
<Label class="flex items-center space-x-2">
  <Switch bind:checked />
  <span>Text</span>
</Label>

<!-- ✅ Method 2: aria-labelledby -->
<Switch bind:checked aria-labelledby="label-id" />
<Label id="label-id">Text</Label>

<!-- ❌ Wrong: Trying to add id to component -->
<Switch id="switch-id" bind:checked />
<Label for="switch-id">Text</Label>
```

---

## 🔗 Additional Resources

- **Official Docs**: `laravel-svelte-port/shadcn-svelte/docs/content/components/`
- **Component Source**: Check actual implementation in your project's `src/lib/components/ui/`
- **bits-ui Docs**: https://bits-ui.com/docs/components
- **Svelte 5 Docs**: See `llms.txt` for Svelte 5 runes and syntax

---

**Last Updated**: 2026-01-14  
**Repository**: `/laravel-svelte-port/shadcn-svelte`  
**Status**: Complete reference based on official documentation
