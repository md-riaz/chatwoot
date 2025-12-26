# Chatwoot Design System

> **LLM-Friendly UI/UX Design System Documentation**  
> This document provides a comprehensive, framework-agnostic specification of Chatwoot's design system to enable pixel-perfect replication in any frontend framework.

---

## Table of Contents

1. [Design Principles](#design-principles)
2. [Design Tokens](#design-tokens)
3. [Typography](#typography)
4. [Layout System](#layout-system)
5. [Component Library](#component-library)
6. [Patterns & Compositions](#patterns--compositions)
7. [Accessibility Guidelines](#accessibility-guidelines)
8. [Dark Mode Implementation](#dark-mode-implementation)
9. [Animation & Transitions](#animation--transitions)
10. [Quick Reference](#quick-reference)

---

## Design Principles

### Core Values
- **Clarity**: Simple, understandable interfaces with clear visual hierarchy
- **Consistency**: Uniform patterns and components across the application
- **Efficiency**: Streamlined workflows for support teams
- **Accessibility**: WCAG 2.1 AA compliance with keyboard navigation support
- **Responsive**: Mobile-first approach with fluid layouts

### Visual Style
- **Modern & Clean**: Minimal design with purposeful use of color
- **Professional**: Business-appropriate aesthetics for customer support
- **Friendly**: Approachable UI that feels human-centered
- **Systematic**: Token-based design with predictable patterns

---

## Design Tokens

### Color System

Chatwoot uses the Radix UI color system with CSS custom properties for theming. All colors support light/dark modes.

#### Brand Colors
```
Brand Primary (Blue): #2781F6
```

#### Semantic Color Scales

Each color has a 12-step scale (1-12) where:
- Steps 1-2: Backgrounds
- Steps 3-5: Component backgrounds
- Steps 6-8: Borders and separators
- Steps 9-10: Solid backgrounds
- Steps 11-12: Text

**Available Color Families:**
- `slate` (neutral grays)
- `iris` (purple-blue)
- `blue` (primary brand)
- `ruby` (errors, destructive actions)
- `amber` (warnings, alerts)
- `teal` (success, positive actions)
- `gray` (alternative neutral)

**Usage Pattern:**
```css
/* Tailwind class format */
bg-n-slate-1     /* Background level 1 */
text-n-slate-12  /* Text (highest contrast) */
border-n-slate-6 /* Border/separator */
bg-n-blue-9      /* Solid blue background */
```

#### Alpha Colors (Transparency)
```css
bg-n-alpha-1      /* Subtle overlay */
bg-n-alpha-2      /* Medium overlay */
bg-n-alpha-3      /* Strong overlay */
bg-n-alpha-black1 /* Black with alpha */
bg-n-alpha-black2 /* Black with more alpha */
bg-n-alpha-white  /* White with alpha */
```

#### Solid Colors (High Contrast)
```css
bg-n-solid-1      /* Solid surface level 1 */
bg-n-solid-2      /* Solid surface level 2 */
bg-n-solid-3      /* Solid surface level 3 */
bg-n-solid-active /* Active state */
bg-n-solid-amber  /* Amber solid */
bg-n-solid-blue   /* Blue solid */
bg-n-solid-iris   /* Iris solid */
```

#### Border Colors
```css
border-n-weak       /* Subtle borders (default) */
border-n-container  /* Container borders */
border-n-strong     /* Emphasized borders */
border-n-blue-border /* Blue accent border */
```

#### Legacy Colors (Transitioning Away)
```css
/* Woot brand colors */
woot-25, woot-50, woot-75, woot-100, woot-200, woot-300, 
woot-400, woot-500, woot-600, woot-700, woot-800, woot-900

/* Similar scales for: green, yellow, red, violet, black */
```

### Spacing Scale

Based on 0.25rem (4px) increments using Tailwind's default scale:

```
0    = 0
0.5  = 2px   (0.125rem)
1    = 4px   (0.25rem)
1.5  = 6px   (0.375rem)
2    = 8px   (0.5rem)
2.5  = 10px  (0.625rem)
3    = 12px  (0.75rem)
3.5  = 14px  (0.875rem)
4    = 16px  (1rem)
5    = 20px  (1.25rem)
6    = 24px  (1.5rem)
7    = 28px  (1.75rem)
8    = 32px  (2rem)
9    = 36px  (2.25rem)
10   = 40px  (2.5rem)
11   = 44px  (2.75rem)
12   = 48px  (3rem)
14   = 56px  (3.5rem)
16   = 64px  (4rem)
20   = 80px  (5rem)
24   = 96px  (6rem)
28   = 112px (7rem)
32   = 128px (8rem)
```

### Border Radius

```
none     = 0
sm       = 2px
DEFAULT  = 4px
md       = 6px
lg       = 8px
xl       = 12px
2xl      = 16px
3xl      = 24px
full     = 9999px (circular)
```

**Common Usage:**
- Buttons: `rounded-lg` (8px)
- Cards: `rounded-2xl` (16px)
- Inputs: `rounded-md` or `rounded-lg` (6-8px)
- Avatars: `rounded-xl` or `rounded-full` (12px or circular)
- Badges: `rounded-full` (circular)

### Shadows

```css
/* Tailwind shadow utilities */
shadow-sm   /* Subtle elevation */
shadow      /* Default card shadow */
shadow-md   /* Medium elevation */
shadow-lg   /* High elevation */
shadow-xl   /* Maximum elevation */
shadow-none /* No shadow */
```

### Z-Index Layers

```
0   = Base content
10  = Overlays, badges, status indicators
20  = Tooltips, popovers
30  = Dropdowns, menus
40  = Modals, dialogs
50  = Toast notifications
```

---

## Typography

### Font Family

**Primary Font Stack:**
```css
font-sans: [
  'Inter',
  '-apple-system',
  'system-ui',
  'BlinkMacSystemFont',
  'Segoe UI',
  'Roboto',
  'Helvetica Neue',
  'Tahoma',
  'Arial',
  'sans-serif'
]
```

**Display Font:**
```css
font-interDisplay: ['InterDisplay', ...sans-serif-stack]
```

### Font Sizes

```
text-xxs    = 0.625rem (10px)  /* Custom size */
text-xs     = 0.75rem (12px)
text-sm     = 0.875rem (14px)  /* Body default */
text-base   = 1rem (16px)
text-lg     = 1.125rem (18px)
text-xl     = 1.25rem (20px)
text-2xl    = 1.5rem (24px)
text-3xl    = 1.875rem (30px)
text-4xl    = 2.25rem (36px)
text-5xl    = 3rem (48px)
```

### Font Weights

```
font-normal   = 400  /* Body text */
font-medium   = 500  /* Labels, subtle emphasis */
font-semibold = 600  /* Headings */
font-bold     = 700  /* Strong emphasis */
```

### Line Heights

```
leading-none    = 1
leading-tight   = 1.25
leading-snug    = 1.375
leading-normal  = 1.5    /* Default */
leading-relaxed = 1.625
leading-loose   = 2
```

### Text Colors

Primary text uses semantic color tokens:
```css
text-n-slate-12  /* Primary text (highest contrast) */
text-n-slate-11  /* Secondary text */
text-n-slate-10  /* Tertiary text, placeholders */
text-n-slate-9   /* Disabled text */
```

### Typography Presets

**Heading 1:**
```css
font-semibold text-2xl text-n-slate-12
```

**Heading 2:**
```css
font-semibold text-xl text-n-slate-12
```

**Heading 3:**
```css
font-semibold text-lg text-n-slate-12
```

**Body Large:**
```css
text-base text-n-slate-12
```

**Body Default:**
```css
text-sm text-n-slate-12
```

**Body Small:**
```css
text-xs text-n-slate-11
```

**Label:**
```css
text-sm font-medium text-n-slate-12
```

**Caption:**
```css
text-xs text-n-slate-11
```

---

## Layout System

### Breakpoints

```
xs:   480px   /* Extra small devices */
sm:   640px   /* Small devices */
md:   768px   /* Medium devices (tablets) */
lg:   1024px  /* Large devices (desktops) */
xl:   1280px  /* Extra large devices */
2xl:  1536px  /* 2X large devices */
```

### Container Widths

Follow Tailwind's default container behavior with padding:
```css
container mx-auto px-4 sm:px-6 lg:px-8
```

### Grid System

Uses CSS Grid with Tailwind utilities:

**12-Column Grid:**
```css
grid grid-cols-12 gap-4
col-span-6    /* Half width */
col-span-4    /* Third width */
col-span-3    /* Quarter width */
```

**Responsive Columns:**
```css
grid-cols-1 md:grid-cols-2 lg:grid-cols-3
```

### Flexbox Patterns

**Horizontal Stack:**
```css
flex flex-row items-center gap-3
```

**Vertical Stack:**
```css
flex flex-col gap-3
```

**Space Between:**
```css
flex justify-between items-center
```

**Centered:**
```css
flex items-center justify-center
```

### Common Layouts

**Card Container:**
```css
rounded-2xl bg-n-solid-2 outline outline-1 outline-n-container shadow
```

**Page Container:**
```css
container mx-auto px-6 py-8
```

**Sidebar Layout:**
```css
/* Parent */
flex h-screen

/* Sidebar */
w-64 bg-n-solid-1 border-r border-n-container

/* Main Content */
flex-1 overflow-auto
```

---

## Component Library

### Button

**Variants:**
- `solid` - Filled background (default)
- `outline` - Outlined with transparent background
- `faded` - Subtle background
- `link` - Text-only button
- `ghost` - Minimal styling

**Colors:**
- `blue` - Primary actions (default)
- `ruby` - Destructive actions
- `amber` - Warning actions
- `slate` - Neutral actions
- `teal` - Success actions

**Sizes:**
- `xs` - Extra small
- `sm` - Small
- `md` - Medium (default)
- `lg` - Large

**States:**
- Default
- Hover
- Active
- Disabled
- Loading

**Base Structure:**
```html
<button class="
  inline-flex items-center justify-center gap-2
  rounded-lg
  font-medium
  transition-all duration-200
  outline-none focus:ring-2 focus:ring-offset-2
  disabled:opacity-50 disabled:cursor-not-allowed
  [variant-specific-classes]
  [size-specific-classes]
">
  [icon] [label]
</button>
```

**Solid Button (Blue):**
```css
bg-n-blue-9 text-white
hover:bg-n-blue-10
active:bg-n-blue-11
focus:ring-n-brand
```

**Outline Button:**
```css
border border-n-slate-6 bg-transparent text-n-slate-12
hover:bg-n-alpha-2 hover:border-n-slate-7
active:bg-n-alpha-3
```

**Size Classes:**
```css
/* xs */
px-2.5 py-1.5 text-xs h-7

/* sm */
px-3 py-2 text-sm h-8

/* md (default) */
px-4 py-2.5 text-sm h-10

/* lg */
px-5 py-3 text-base h-12
```

**With Icon:**
```html
<button class="[button-classes]">
  <Icon class="size-4" />
  <span>Label</span>
</button>
```

**Icon Only:**
```html
<button class="[button-classes] aspect-square">
  <Icon class="size-4" />
</button>
```

**Loading State:**
```html
<button class="[button-classes]" disabled>
  <Spinner class="size-4" />
  <span>Loading...</span>
</button>
```

### Input

**Base Structure:**
```html
<div class="flex flex-col gap-1">
  <label class="text-sm font-medium text-n-slate-12">
    Label
  </label>
  <input 
    type="text"
    class="
      block w-full
      rounded-lg
      border-none outline outline-1 outline-offset-[-1px]
      bg-n-alpha-black2
      text-sm text-n-slate-12
      placeholder:text-n-slate-10
      px-3 py-2.5 h-10
      transition-all duration-500
      outline-n-weak
      hover:outline-n-slate-6
      focus:outline-n-brand focus:outline-1
      disabled:opacity-50 disabled:cursor-not-allowed
    "
    placeholder="Placeholder text"
  />
  <p class="text-xs text-n-slate-11">Helper text</p>
</div>
```

**Error State:**
```css
/* Input */
outline-n-ruby-8 hover:outline-n-ruby-9

/* Helper text */
text-n-ruby-9
```

**Success State:**
```css
/* Input */
outline-n-teal-8

/* Helper text */
text-n-teal-10
```

**Sizes:**
```css
/* sm */
px-3 py-2 h-8

/* md (default) */
px-3 py-2.5 h-10
```

**With Icon:**
```html
<div class="relative">
  <Icon class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-n-slate-11" />
  <input class="[input-classes] pl-9" />
</div>
```

### Textarea

Similar to input but:
```css
min-h-[80px] resize-y
```

### Checkbox

**Structure:**
```html
<div class="relative w-4 h-4">
  <input 
    type="checkbox"
    class="
      peer absolute inset-0 z-10
      w-4 h-4
      appearance-none rounded
      border border-n-slate-6
      ring-transparent
      transition-all duration-200
      cursor-pointer
      checked:border-n-brand checked:bg-n-brand
      indeterminate:border-n-brand indeterminate:bg-n-brand
      hover:enabled:bg-n-blue-border
      disabled:opacity-50
    "
  />
  <!-- Checkmark SVG -->
  <svg 
    class="
      pointer-events-none absolute
      w-3.5 h-3.5 z-20
      stroke-white
      opacity-0 peer-checked:opacity-100
      transition-opacity duration-200
      left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
    "
    viewBox="0 0 14 14"
  >
    <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </svg>
</div>
```

### Switch

**Structure:**
```html
<button
  type="button"
  role="switch"
  class="
    relative h-4 w-7
    rounded-full
    flex-shrink-0
    transition-colors duration-200
    focus:outline-none focus:ring-1 focus:ring-n-brand focus:ring-offset-2
    [state-classes]
  "
>
  <span class="sr-only">Toggle</span>
  <span class="
    absolute top-0.5 left-0.5
    h-3 w-3
    rounded-full shadow-sm
    bg-n-background
    transform transition-transform duration-200
    [state-classes]
  " />
</button>
```

**States:**
```css
/* Off */
bg-n-slate-6
translate-x-0

/* On */
bg-n-brand
translate-x-3
```

### Avatar

**Base Structure:**
```html
<span class="
  relative inline-flex
  [size-classes]
  rounded-xl (or rounded-full)
  overflow-hidden
  bg-[dynamic-color]
">
  <!-- Image -->
  <img src="..." alt="..." />
  
  <!-- Or Initials -->
  <span class="
    inline-flex items-center justify-center
    font-medium
    text-[dynamic-color]
    [size-classes]
  ">
    AB
  </span>
  
  <!-- Or Icon -->
  <Icon class="size-[dynamic]" />
  
  <!-- Status Badge (optional) -->
  <div class="
    absolute z-20
    rounded-full
    border border-n-slate-3
    [position-classes]
    [status-color]
  " />
</span>
```

**Sizes:**
```css
/* Default calculation: size prop in pixels */
width: {size}px;
height: {size}px;

/* Common sizes */
size-8   (32px)
size-10  (40px)
size-12  (48px)
size-16  (64px)
```

**Status Colors:**
```css
bg-n-teal-10   /* online */
bg-n-amber-10  /* busy */
bg-n-slate-10  /* offline */
```

**Initials Colors (Dynamic):**
```javascript
// Background colors based on name length
const colors = [
  { bg: '#FBDCEF', darkBg: '#4B143D', text: '#C2298A', darkText: '#FF8DCC' },
  { bg: '#FFE0BB', darkBg: '#3F220D', text: '#99543A', darkText: '#FFA366' },
  { bg: '#E8E8E8', darkBg: '#2A2A2A', text: '#60646C', darkText: '#ADB1B8' },
  { bg: '#CCF3EA', darkBg: '#023B37', text: '#008573', darkText: '#0BD8B6' },
  { bg: '#EBEBFE', darkBg: '#27264D', text: '#4747C2', darkText: '#A19EFF' },
  { bg: '#E1E9FF', darkBg: '#1D2E62', text: '#3A5BC7', darkText: '#9EB1FF' },
];
const index = name.length % colors.length;
```

### Icon

**Usage:**
```html
<!-- Lucide icons via Iconify -->
<i class="i-lucide-[icon-name] size-4 text-n-slate-11"></i>

<!-- Examples -->
<i class="i-lucide-plus size-4"></i>
<i class="i-lucide-x size-5"></i>
<i class="i-lucide-search size-6"></i>
```

**Common Sizes:**
```css
size-3   (12px)
size-4   (16px)  /* Default for buttons */
size-5   (20px)
size-6   (24px)
```

**Icon Collections Available:**
- `i-lucide-*` (primary)
- `i-logos-*`
- `i-ri-*`
- `i-ph-*`
- `i-material-symbols-*`
- `i-teenyicons-*`
- `i-woot-*` (custom)

### Spinner

**Structure:**
```html
<div class="
  inline-block
  animate-spin
  rounded-full
  border-2 border-current border-t-transparent
  [size-classes]
">
  <span class="sr-only">Loading...</span>
</div>
```

**Sizes:**
```css
size-4   (16px)  /* Small */
size-5   (20px)  /* Medium */
size-6   (24px)  /* Large */
```

### Card

**Base Structure:**
```html
<div class="
  flex flex-col
  w-full
  rounded-2xl
  bg-n-solid-2
  shadow
  outline outline-1 outline-n-container
">
  <div class="px-6 py-5">
    <!-- Card content -->
  </div>
</div>
```

**With Header:**
```html
<div class="[card-classes]">
  <div class="px-6 py-5 border-b border-n-container">
    <h3 class="text-lg font-semibold text-n-slate-12">Header</h3>
  </div>
  <div class="px-6 py-5">
    <!-- Card content -->
  </div>
</div>
```

### Dropdown Menu

**Structure:**
```html
<!-- Trigger -->
<button class="[button-classes]">
  Menu
</button>

<!-- Menu Container (positioned absolutely) -->
<div class="
  absolute z-30
  min-w-[200px]
  rounded-xl
  bg-n-solid-2
  shadow-lg
  outline outline-1 outline-n-container
  p-1
">
  <ul class="flex flex-col gap-0.5">
    <!-- Menu Item -->
    <li>
      <button class="
        flex items-center gap-3
        w-full
        px-2 py-2
        rounded-lg
        text-sm text-n-slate-12
        hover:bg-n-alpha-2
        transition-colors
      ">
        <Icon class="size-4 text-n-slate-11" />
        <span>Menu Item</span>
      </button>
    </li>
    
    <!-- Separator -->
    <li class="h-px bg-n-container my-1" />
    
    <!-- Another Item -->
    <li>
      <button class="[item-classes]">
        Item 2
      </button>
    </li>
  </ul>
</div>
```

### Badge

**Base Structure:**
```html
<span class="
  inline-flex items-center justify-center
  px-2 py-1
  rounded-full
  text-xs font-medium
  [variant-classes]
">
  Badge
</span>
```

**Variants:**
```css
/* Default */
bg-n-alpha-3 text-n-slate-11

/* Blue */
bg-n-blue-3 text-n-blue-11

/* Ruby */
bg-n-ruby-3 text-n-ruby-11

/* Amber */
bg-n-amber-3 text-n-amber-11

/* Teal */
bg-n-teal-3 text-n-teal-11
```

### Modal/Dialog

**Overlay:**
```css
fixed inset-0 z-40
bg-modal-backdrop-light dark:bg-modal-backdrop-dark
```

**Dialog Container:**
```html
<div class="
  fixed inset-0 z-40
  flex items-center justify-center
  p-4
">
  <!-- Backdrop -->
  <div class="fixed inset-0 bg-modal-backdrop-light dark:bg-modal-backdrop-dark" />
  
  <!-- Dialog -->
  <div class="
    relative z-50
    w-full max-w-md
    rounded-2xl
    bg-n-solid-2
    shadow-xl
    outline outline-1 outline-n-container
    animate-fade-in-up
  ">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-n-container">
      <h2 class="text-lg font-semibold text-n-slate-12">Dialog Title</h2>
    </div>
    
    <!-- Body -->
    <div class="px-6 py-5">
      <p class="text-sm text-n-slate-11">Dialog content</p>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 border-t border-n-container flex justify-end gap-3">
      <Button variant="outline">Cancel</Button>
      <Button>Confirm</Button>
    </div>
  </div>
</div>
```

### Toast/Snackbar

**Structure:**
```html
<div class="
  fixed bottom-4 right-4 z-50
  flex items-center gap-3
  min-w-[300px] max-w-md
  px-4 py-3
  rounded-lg
  bg-n-solid-3
  shadow-lg
  outline outline-1 outline-n-container
  animate-fade-in-up
">
  <Icon class="size-5 flex-shrink-0 [color]" />
  <p class="text-sm text-n-slate-12 flex-1">Message</p>
  <button class="text-n-slate-11 hover:text-n-slate-12">
    <Icon class="i-lucide-x size-4" />
  </button>
</div>
```

**Variants:**
```css
/* Success */
Icon: text-n-teal-10

/* Error */
Icon: text-n-ruby-10

/* Warning */
Icon: text-n-amber-10

/* Info */
Icon: text-n-blue-10
```

### Tooltip

**Structure:**
```html
<!-- Trigger -->
<button data-tooltip="Tooltip text">
  Hover me
</button>

<!-- Tooltip (positioned) -->
<div class="
  absolute z-20
  px-2 py-1
  rounded-md
  bg-n-slate-12
  text-xs text-white
  whitespace-nowrap
  shadow-lg
  pointer-events-none
">
  Tooltip text
  <!-- Arrow -->
  <div class="absolute w-2 h-2 bg-n-slate-12 rotate-45 [position]" />
</div>
```

### Tab Bar

**Structure:**
```html
<div class="flex border-b border-n-container">
  <!-- Active Tab -->
  <button class="
    px-4 py-2
    text-sm font-medium
    border-b-2 border-n-brand
    text-n-brand
  ">
    Tab 1
  </button>
  
  <!-- Inactive Tab -->
  <button class="
    px-4 py-2
    text-sm font-medium
    border-b-2 border-transparent
    text-n-slate-11
    hover:text-n-slate-12 hover:border-n-slate-6
  ">
    Tab 2
  </button>
</div>
```

### Empty State

**Structure:**
```html
<div class="
  flex flex-col items-center justify-center
  py-12 px-6
  text-center
">
  <Icon class="size-12 text-n-slate-9 mb-4" />
  <h3 class="text-lg font-semibold text-n-slate-12 mb-2">
    Empty State Title
  </h3>
  <p class="text-sm text-n-slate-11 mb-6 max-w-sm">
    Description of the empty state
  </p>
  <Button>Take Action</Button>
</div>
```

### Banner

**Structure:**
```html
<div class="
  flex items-start gap-3
  px-4 py-3
  rounded-lg
  [variant-classes]
">
  <Icon class="size-5 flex-shrink-0" />
  <div class="flex-1">
    <p class="text-sm font-medium">Banner Title</p>
    <p class="text-sm">Banner description</p>
  </div>
  <button class="hover:opacity-80">
    <Icon class="i-lucide-x size-4" />
  </button>
</div>
```

**Variants:**
```css
/* Info */
bg-n-blue-3 text-n-blue-11

/* Success */
bg-n-teal-3 text-n-teal-11

/* Warning */
bg-n-amber-3 text-n-amber-11

/* Error */
bg-n-ruby-3 text-n-ruby-11
```

---

## Patterns & Compositions

### Form Layout

**Standard Form:**
```html
<form class="flex flex-col gap-6 max-w-lg">
  <!-- Form Group -->
  <div class="flex flex-col gap-1">
    <label class="text-sm font-medium text-n-slate-12">
      Field Label
    </label>
    <input class="[input-classes]" />
    <p class="text-xs text-n-slate-11">Helper text</p>
  </div>
  
  <!-- Actions -->
  <div class="flex justify-end gap-3">
    <Button variant="outline">Cancel</Button>
    <Button type="submit">Submit</Button>
  </div>
</form>
```

**Inline Form (Horizontal):**
```html
<form class="flex gap-3 items-end">
  <div class="flex-1 flex flex-col gap-1">
    <label class="text-sm font-medium text-n-slate-12">Label</label>
    <input class="[input-classes]" />
  </div>
  <Button type="submit">Submit</Button>
</form>
```

### List with Actions

```html
<ul class="divide-y divide-n-container">
  <li class="
    flex items-center justify-between
    py-3 px-4
    hover:bg-n-alpha-1
    transition-colors
  ">
    <div class="flex items-center gap-3">
      <Avatar size="40" />
      <div>
        <p class="text-sm font-medium text-n-slate-12">Item Title</p>
        <p class="text-xs text-n-slate-11">Item Description</p>
      </div>
    </div>
    <Button size="sm" variant="ghost">
      <Icon class="i-lucide-more-vertical size-4" />
    </Button>
  </li>
</ul>
```

### Sidebar Navigation

```html
<nav class="flex flex-col gap-1 p-2">
  <!-- Active Item -->
  <a class="
    flex items-center gap-3
    px-3 py-2
    rounded-lg
    bg-n-alpha-2
    text-n-slate-12 font-medium
  ">
    <Icon class="size-5" />
    <span>Navigation Item</span>
  </a>
  
  <!-- Inactive Item -->
  <a class="
    flex items-center gap-3
    px-3 py-2
    rounded-lg
    text-n-slate-11
    hover:bg-n-alpha-1 hover:text-n-slate-12
  ">
    <Icon class="size-5" />
    <span>Navigation Item</span>
  </a>
</nav>
```

### Header with Actions

```html
<header class="
  flex items-center justify-between
  px-6 py-4
  border-b border-n-container
  bg-n-background
">
  <div class="flex items-center gap-3">
    <h1 class="text-xl font-semibold text-n-slate-12">Page Title</h1>
    <Badge>Beta</Badge>
  </div>
  <div class="flex items-center gap-3">
    <Button variant="outline" size="sm">Secondary</Button>
    <Button size="sm">Primary Action</Button>
  </div>
</header>
```

### Search with Filters

```html
<div class="flex gap-3">
  <!-- Search -->
  <div class="relative flex-1">
    <Icon class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-n-slate-11" />
    <input 
      type="search"
      placeholder="Search..."
      class="[input-classes] pl-9"
    />
  </div>
  
  <!-- Filter Button -->
  <Button variant="outline">
    <Icon class="i-lucide-filter size-4" />
    Filters
  </Button>
</div>
```

### Data Table Header

```html
<div class="flex items-center justify-between px-6 py-4 border-b border-n-container">
  <div class="flex items-center gap-3">
    <Checkbox />
    <span class="text-sm text-n-slate-11">3 selected</span>
  </div>
  <div class="flex items-center gap-2">
    <Button size="sm" variant="outline">Delete</Button>
    <Button size="sm" variant="outline">Export</Button>
  </div>
</div>
```

---

## Accessibility Guidelines

### Keyboard Navigation

**Required Support:**
- Tab: Navigate forward through interactive elements
- Shift+Tab: Navigate backward
- Enter/Space: Activate buttons and controls
- Escape: Close modals, dropdowns, and menus
- Arrow Keys: Navigate within menus, tabs, and lists

**Focus Indicators:**
```css
focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2
```

### ARIA Attributes

**Button States:**
```html
<button 
  aria-label="Descriptive label"
  aria-pressed="true|false"  <!-- For toggles -->
  aria-expanded="true|false"  <!-- For dropdowns -->
  disabled  <!-- For disabled state -->
>
```

**Form Labels:**
```html
<label for="input-id">Label</label>
<input id="input-id" aria-describedby="help-text" />
<p id="help-text">Helper text</p>
```

**Live Regions:**
```html
<div role="status" aria-live="polite">
  <!-- Dynamic content updates -->
</div>
```

### Screen Reader Text

```html
<span class="sr-only">Screen reader only text</span>
```

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
```

### Color Contrast

**Minimum Ratios (WCAG AA):**
- Normal text: 4.5:1
- Large text (18px+): 3:1
- UI components: 3:1

**Text Colors:**
```css
/* Primary text (high contrast) */
text-n-slate-12  /* Contrast ratio > 7:1 */

/* Secondary text */
text-n-slate-11  /* Contrast ratio > 4.5:1 */

/* Tertiary/disabled */
text-n-slate-10  /* Use sparingly, may not meet AA */
```

### Focus Management

**Modal Open:**
1. Trap focus within modal
2. Focus first interactive element
3. Return focus to trigger on close

**Dropdown Open:**
1. Focus first menu item
2. Allow arrow key navigation
3. Close on Escape

---

## Dark Mode Implementation

### Toggle Mechanism

Dark mode is controlled via a `dark` class on the root element:

```html
<html class="dark">
```

### Color Token Behavior

All semantic color tokens (`n-slate-*`, `n-blue-*`, etc.) automatically adapt:

**Light Mode:**
```css
--slate-1: /* Light background */
--slate-12: /* Dark text */
```

**Dark Mode:**
```css
.dark {
  --slate-1: /* Dark background */
  --slate-12: /* Light text */
}
```

### Component Adaptation

Components using semantic tokens automatically support dark mode:

```html
<!-- Automatically adapts -->
<div class="bg-n-slate-1 text-n-slate-12">
  Content
</div>
```

### Manual Dark Mode Styles

For specific dark mode overrides:

```css
dark:bg-n-slate-3
dark:text-n-slate-11
dark:border-n-slate-6
```

### Image Handling

```html
<!-- Light mode image -->
<img src="logo-light.png" class="block dark:hidden" />

<!-- Dark mode image -->
<img src="logo-dark.png" class="hidden dark:block" />
```

---

## Animation & Transitions

### Transition Utilities

**Duration:**
```css
transition-none      /* No transition */
transition-all       /* All properties */
transition-colors    /* Color properties only */
transition-opacity   /* Opacity only */
transition-transform /* Transform only */
```

**Timing:**
```css
duration-150  /* 150ms */
duration-200  /* 200ms - Default for most interactions */
duration-300  /* 300ms */
duration-500  /* 500ms - For smooth, noticeable changes */
```

**Easing:**
```css
ease-linear
ease-in
ease-out
ease-in-out  /* Default, most natural */
```

### Custom Animations

**Wiggle (Error indication):**
```css
animate-wiggle

/* Keyframes */
@keyframes wiggle {
  0%, 100% { transform: translateX(0); }
  15% { transform: translateX(6px); }
  30% { transform: translateX(-6px); }
  45% { transform: translateX(6px); }
  60% { transform: translateX(-6px); }
  75% { transform: translateX(6px); }
  90% { transform: translateX(-6px); }
}
/* Duration: 0.5s ease-in-out */
```

**Fade In Up (Modals, toasts):**
```css
animate-fade-in-up

/* Keyframes */
@keyframes fade-in-up {
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: translateY(0); }
}
/* Duration: 0.3s ease-out */
```

**Loader Pulse (Loading states):**
```css
animate-loader-pulse

/* Keyframes */
@keyframes loader-pulse {
  0%, 100% { opacity: 0.4; }
  50% { opacity: 1; }
}
/* Duration: 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite */
```

**Card Select (Selection feedback):**
```css
animate-card-select

/* Keyframes */
@keyframes card-select {
  0%, 100% { transform: translateX(0); }
  50% { transform: translateX(1px); }
}
/* Duration: 0.25s ease-in-out */
```

**Shake (Validation error):**
```css
animate-shake

/* Keyframes */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(3.75px); }
  50% { transform: translateX(-3.75px); }
  75% { transform: translateX(3.75px); }
}
/* Duration: 0.3s ease-in-out, 2 iterations */
```

**Spin (Loading spinners):**
```css
animate-spin

/* Infinite rotation */
```

### Interaction Feedback

**Button Hover:**
```css
transition-all duration-200
hover:brightness-110
active:brightness-90
```

**Card Hover:**
```css
transition-all duration-200
hover:shadow-md hover:-translate-y-0.5
```

**Link Hover:**
```css
transition-colors duration-200
hover:text-n-blue-10
```

---

## Quick Reference

### Component Checklist

When implementing any component, ensure:

- [ ] Uses semantic color tokens (`n-*`)
- [ ] Includes hover, active, focus, disabled states
- [ ] Supports dark mode automatically
- [ ] Has proper focus indicators
- [ ] Includes ARIA attributes where needed
- [ ] Keyboard navigation support
- [ ] Responsive on all breakpoints
- [ ] Uses appropriate transitions
- [ ] Follows spacing scale (4px increments)
- [ ] Text meets contrast requirements

### Common Patterns Quick Copy

**Button:**
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 focus:ring-2 focus:ring-n-brand transition-colors duration-200">
  Label
</button>
```

**Input:**
```html
<input class="block w-full px-3 py-2.5 h-10 rounded-lg border-none outline outline-1 outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand transition-all duration-500" />
```

**Card:**
```html
<div class="rounded-2xl bg-n-solid-2 shadow outline outline-1 outline-n-container p-6">
  Content
</div>
```

**Avatar:**
```html
<span class="inline-flex items-center justify-center size-10 rounded-xl overflow-hidden bg-n-slate-3">
  <img src="..." alt="..." />
</span>
```

**Icon:**
```html
<i class="i-lucide-plus size-4"></i>
```

### Color Usage Guidelines

**Text:**
- Primary: `text-n-slate-12`
- Secondary: `text-n-slate-11`
- Tertiary/Placeholder: `text-n-slate-10`

**Backgrounds:**
- Page: `bg-n-background`
- Card: `bg-n-solid-2`
- Subtle: `bg-n-alpha-1`
- Hover: `hover:bg-n-alpha-2`

**Borders:**
- Default: `border-n-weak`
- Container: `border-n-container`
- Emphasized: `border-n-strong`

**Interactive:**
- Primary: `bg-n-blue-9`
- Success: `bg-n-teal-9`
- Error: `bg-n-ruby-9`
- Warning: `bg-n-amber-9`

### Spacing Guidelines

**Component Internal:**
- Buttons: `px-4 py-2.5` (16px/10px)
- Cards: `p-6` (24px)
- Inputs: `px-3 py-2.5` (12px/10px)
- Modals: `px-6 py-5` (24px/20px)

**Layout:**
- Section gaps: `gap-8` or `gap-6` (32px/24px)
- Component gaps: `gap-3` or `gap-4` (12px/16px)
- Dense layouts: `gap-2` (8px)
- Form fields: `gap-6` (24px)

### Responsive Patterns

**Mobile First:**
```css
/* Base (mobile) */
flex-col gap-4

/* Tablet+ */
md:flex-row md:gap-6

/* Desktop+ */
lg:gap-8
```

**Hide/Show:**
```css
hidden md:block    /* Hide on mobile */
block md:hidden    /* Show on mobile only */
```

**Grid Responsive:**
```css
grid-cols-1 md:grid-cols-2 lg:grid-cols-3
```

---

## Implementation Notes

### Framework Agnostic Translation

This design system can be implemented in any framework by:

1. **Converting Tailwind Classes** to your CSS-in-JS or CSS modules:
   ```
   Tailwind: bg-n-blue-9 text-white rounded-lg
   CSS: background-color: var(--blue-9); color: white; border-radius: 8px;
   ```

2. **Using Semantic Tokens**: Define CSS custom properties:
   ```css
   :root {
     --slate-1: #fcfcfd;
     --slate-12: #0d0e11;
     /* ... */
   }
   
   .dark {
     --slate-1: #111113;
     --slate-12: #eeeef0;
     /* ... */
   }
   ```

3. **Component Props to Classes**: Map component props to style variations:
   ```javascript
   // React example
   const Button = ({ variant = 'solid', size = 'md' }) => {
     const classes = cn(
       'base-button-classes',
       variantClasses[variant],
       sizeClasses[size]
     );
     return <button className={classes}>...</button>;
   };
   ```

4. **State Management**: Use framework's state management for:
   - Dark mode toggle
   - Modal visibility
   - Dropdown open/close
   - Form validation states

### CSS Custom Properties

For dynamic values (colors, avatar backgrounds, etc.), use CSS custom properties:

```css
/* Set in inline style */
style="--size: 40px; --bg-color: #FBDCEF;"

/* Use in CSS */
.avatar {
  width: var(--size);
  height: var(--size);
  background-color: var(--bg-color);
}
```

### Icon Integration

Integrate icon system based on framework:

- **React/Vue**: Use `@iconify/react` or `@iconify/vue`
- **HTML**: Use Iconify web component
- **SVG**: Export SVGs from Iconify and use directly

### Animation Libraries

If your framework doesn't support Tailwind animations:

- **Framer Motion** (React): For complex animations
- **Vue Transition**: Built-in Vue animations
- **CSS Animations**: Define keyframes manually
- **GSAP**: For advanced timeline animations

---

## Version

**Design System Version:** 1.0.0  
**Last Updated:** December 2024  
**Chatwoot Version:** 4.9.1

---

## Contributing

When adding new components or patterns:

1. Follow existing token system
2. Ensure dark mode support
3. Add accessibility features
4. Document all variants and states
5. Include responsive behavior
6. Add animation/transition details
7. Update this document

---

## Support

For questions or clarifications about this design system:
- GitHub: [chatwoot/chatwoot](https://github.com/chatwoot/chatwoot)
- Documentation: [chatwoot.com/help-center](https://www.chatwoot.com/help-center)
- Discord: [discord.gg/cJXdrwS](https://discord.gg/cJXdrwS)
