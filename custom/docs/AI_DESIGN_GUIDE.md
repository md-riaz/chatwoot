# AI Agent Quick Reference - Chatwoot Design System

> **Purpose:** Fast lookup reference for AI agents to generate pixel-perfect UI components for Chatwoot  
> **Format:** Structured for LLM parsing with minimal context switching

---

## 🎯 System Summary

- **Framework:** Vue 3 Composition API with Tailwind CSS
- **Color System:** Radix UI semantic tokens with 12-step scales
- **Typography:** Inter font family, 14px base size
- **Spacing:** 4px increment scale (Tailwind default)
- **Icons:** Iconify with Lucide as primary collection
- **Dark Mode:** CSS class-based (`dark` class on root)
- **Build Tool:** Vite + TypeScript
- **Component Library:** Custom components in `app/javascript/dashboard/components-next/`

---

## 🎨 Color Quick Reference

### Text (Use These First)
```
Primary:      text-n-slate-12
Secondary:    text-n-slate-11
Tertiary:     text-n-slate-10 (placeholders)
Disabled:     text-n-slate-9
```

### Backgrounds (Common)
```
Page:         bg-n-background
Card:         bg-n-solid-2
Subtle:       bg-n-alpha-1
Input:        bg-n-alpha-black2
Hover:        hover:bg-n-alpha-2
Active:       hover:bg-n-alpha-3
```

### Borders (Standard)
```
Default:      border-n-weak outline-n-weak
Container:    border-n-container outline-n-container
Strong:       border-n-strong outline-n-strong
```

### Interactive (Buttons, Actions)
```
Primary:      bg-n-blue-9 hover:bg-n-blue-10
Success:      bg-n-teal-9 hover:bg-n-teal-10
Error:        bg-n-ruby-9 hover:bg-n-ruby-10
Warning:      bg-n-amber-9 hover:bg-n-amber-10
Neutral:      bg-n-slate-9 hover:bg-n-slate-10
```

### Focus State (Always Add)
```
focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2
```

---

## 📏 Size Standards

### Component Heights
```
Extra Small:  h-7   (28px)
Small:        h-8   (32px)
Medium:       h-10  (40px) ← Default
Large:        h-12  (48px)
```

### Icon Sizes
```
Tiny:         size-3  (12px)
Small:        size-4  (16px) ← Button icons
Medium:       size-5  (20px)
Large:        size-6  (24px)
```

### Border Radius
```
Input/Button:   rounded-lg    (8px)
Card:           rounded-2xl   (16px)
Avatar:         rounded-xl    (12px) or rounded-full
Badge:          rounded-full
Modal:          rounded-2xl   (16px)
```

### Spacing (Component Gaps)
```
Tight:        gap-2   (8px)
Normal:       gap-3   (12px) ← Default
Comfortable:  gap-4   (16px)
Loose:        gap-6   (24px)
Section:      gap-8   (32px)
```

### Padding (Component Internal)
```
Button:       px-4 py-2.5
Input:        px-3 py-2.5
Card:         p-6 (24px)
Modal:        px-6 py-5
Dropdown:     p-1
```

---

## 🔧 Component Templates

### Button (Copy-Paste Ready)

**Solid Primary:**
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
  <i class="i-lucide-plus size-4"></i>
  <span>Button Text</span>
</button>
```

**Outline:**
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 hover:border-n-slate-7 focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 disabled:opacity-50 transition-colors duration-200">
  Button Text
</button>
```

**Destructive:**
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-ruby-9 text-white hover:bg-n-ruby-10 focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 transition-colors duration-200">
  <i class="i-lucide-trash size-4"></i>
  <span>Delete</span>
</button>
```

**Small Size:**
```html
<button class="inline-flex items-center justify-center gap-2 px-3 py-2 h-8 rounded-lg text-xs font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 transition-colors duration-200">
  Button
</button>
```

**Icon Only:**
```html
<button class="inline-flex items-center justify-center size-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 transition-colors duration-200">
  <i class="i-lucide-plus size-4"></i>
  <span class="sr-only">Add</span>
</button>
```

**Loading State:**
```html
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white focus:outline-none focus:ring-2 focus:ring-n-brand focus:ring-offset-2 opacity-50 cursor-not-allowed" disabled>
  <div class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
  <span>Loading...</span>
</button>
```

### Input (Copy-Paste Ready)

**Standard:**
```html
<div class="flex flex-col gap-1">
  <label for="input-id" class="text-sm font-medium text-n-slate-12">Label</label>
  <input 
    id="input-id"
    type="text"
    placeholder="Placeholder text"
    class="block w-full px-3 py-2.5 h-10 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand focus:outline-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-500"
  />
  <p class="text-xs text-n-slate-11">Helper text</p>
</div>
```

**With Icon:**
```html
<div class="flex flex-col gap-1">
  <label for="search" class="text-sm font-medium text-n-slate-12">Search</label>
  <div class="relative">
    <i class="i-lucide-search absolute left-3 top-1/2 -translate-y-1/2 size-4 text-n-slate-11"></i>
    <input 
      id="search"
      type="search"
      placeholder="Search..."
      class="block w-full pl-9 pr-3 py-2.5 h-10 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand focus:outline-1 transition-all duration-500"
    />
  </div>
</div>
```

**Error State:**
```html
<div class="flex flex-col gap-1">
  <label for="email" class="text-sm font-medium text-n-slate-12">Email</label>
  <input 
    id="email"
    type="email"
    placeholder="email@example.com"
    class="block w-full px-3 py-2.5 h-10 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-ruby-8 bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-ruby-9 focus:outline-n-ruby-9 focus:outline-1 transition-all duration-500"
    aria-describedby="email-error"
  />
  <p id="email-error" class="text-xs text-n-ruby-9">Please enter a valid email</p>
</div>
```

**Small Size:**
```html
<input 
  type="text"
  class="block w-full px-3 py-2 h-8 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand transition-all duration-500"
/>
```

### Card (Copy-Paste Ready)

**Basic:**
```html
<div class="rounded-2xl bg-n-solid-2 shadow outline outline-1 outline-n-container p-6">
  <h3 class="text-lg font-semibold text-n-slate-12 mb-4">Card Title</h3>
  <p class="text-sm text-n-slate-11">Card content goes here</p>
</div>
```

**With Header & Footer:**
```html
<div class="flex flex-col rounded-2xl bg-n-solid-2 shadow outline outline-1 outline-n-container">
  <div class="px-6 py-5 border-b border-n-container">
    <h3 class="text-lg font-semibold text-n-slate-12">Card Header</h3>
  </div>
  <div class="px-6 py-5">
    <p class="text-sm text-n-slate-11">Card body content</p>
  </div>
  <div class="px-6 py-4 border-t border-n-container flex justify-end gap-3">
    <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 transition-colors duration-200">
      Cancel
    </button>
    <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 transition-colors duration-200">
      Confirm
    </button>
  </div>
</div>
```

**Selectable/Clickable:**
```html
<div class="rounded-2xl bg-n-solid-2 shadow outline outline-1 outline-n-container p-6 cursor-pointer hover:bg-n-alpha-1 transition-colors duration-200">
  <p class="text-sm text-n-slate-11">Clickable card</p>
</div>
```

### Avatar (Copy-Paste Ready)

**With Image:**
```html
<span class="relative inline-flex size-10 rounded-xl overflow-hidden flex-shrink-0">
  <img src="avatar.jpg" alt="User Name" class="w-full h-full object-cover" />
</span>
```

**With Initials:**
```html
<span class="relative inline-flex items-center justify-center size-10 rounded-xl overflow-hidden flex-shrink-0 font-medium" style="background-color: #FBDCEF; color: #C2298A;">
  <span class="text-sm select-none">AB</span>
</span>
```

**With Status Badge:**
```html
<span class="relative inline-flex size-10 rounded-xl overflow-hidden flex-shrink-0">
  <img src="avatar.jpg" alt="User Name" class="w-full h-full object-cover" />
  <div class="absolute z-20 size-3.5 rounded-full border border-n-slate-3 bg-n-teal-10" style="top: 28px; left: 28px;"></div>
</span>
```

**Sizes:**
```html
<!-- Small -->
<span class="relative inline-flex size-8 rounded-xl overflow-hidden">...</span>

<!-- Medium (Default) -->
<span class="relative inline-flex size-10 rounded-xl overflow-hidden">...</span>

<!-- Large -->
<span class="relative inline-flex size-12 rounded-xl overflow-hidden">...</span>

<!-- Extra Large -->
<span class="relative inline-flex size-16 rounded-xl overflow-hidden">...</span>

<!-- Circular -->
<span class="relative inline-flex size-10 rounded-full overflow-hidden">...</span>
```

### Modal/Dialog (Copy-Paste Ready)

```html
<!-- Overlay -->
<div class="fixed inset-0 z-40 flex items-center justify-center p-4">
  <!-- Backdrop -->
  <div class="fixed inset-0 bg-modal-backdrop-light dark:bg-modal-backdrop-dark"></div>
  
  <!-- Dialog -->
  <div class="relative z-50 w-full max-w-md rounded-2xl bg-n-solid-2 shadow-xl outline outline-1 outline-n-container animate-fade-in-up">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-n-container">
      <h2 class="text-lg font-semibold text-n-slate-12">Dialog Title</h2>
      <button class="text-n-slate-11 hover:text-n-slate-12">
        <i class="i-lucide-x size-5"></i>
        <span class="sr-only">Close</span>
      </button>
    </div>
    
    <!-- Body -->
    <div class="px-6 py-5">
      <p class="text-sm text-n-slate-11">Dialog content goes here.</p>
    </div>
    
    <!-- Footer -->
    <div class="px-6 py-4 border-t border-n-container flex justify-end gap-3">
      <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 transition-colors duration-200">
        Cancel
      </button>
      <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 transition-colors duration-200">
        Confirm
      </button>
    </div>
  </div>
</div>
```

### Dropdown Menu (Copy-Paste Ready)

```html
<!-- Trigger Button -->
<button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 transition-colors duration-200">
  <span>Menu</span>
  <i class="i-lucide-chevron-down size-4"></i>
</button>

<!-- Menu (Position with JavaScript) -->
<div class="absolute z-30 min-w-[200px] rounded-xl bg-n-solid-2 shadow-lg outline outline-1 outline-n-container p-1 animate-fade-in-up">
  <ul class="flex flex-col gap-0.5">
    <li>
      <button class="flex items-center gap-3 w-full px-2 py-2 rounded-lg text-sm text-n-slate-12 hover:bg-n-alpha-2 transition-colors">
        <i class="i-lucide-edit size-4 text-n-slate-11"></i>
        <span>Edit</span>
      </button>
    </li>
    <li>
      <button class="flex items-center gap-3 w-full px-2 py-2 rounded-lg text-sm text-n-slate-12 hover:bg-n-alpha-2 transition-colors">
        <i class="i-lucide-copy size-4 text-n-slate-11"></i>
        <span>Duplicate</span>
      </button>
    </li>
    <!-- Separator -->
    <li class="h-px bg-n-container my-1"></li>
    <li>
      <button class="flex items-center gap-3 w-full px-2 py-2 rounded-lg text-sm text-n-ruby-11 hover:bg-n-ruby-3 transition-colors">
        <i class="i-lucide-trash size-4"></i>
        <span>Delete</span>
      </button>
    </li>
  </ul>
</div>
```

### Checkbox (Copy-Paste Ready)

```html
<div class="flex items-center gap-2">
  <div class="relative w-4 h-4">
    <input 
      type="checkbox"
      id="checkbox-1"
      class="peer absolute inset-0 z-10 w-4 h-4 appearance-none rounded border border-n-slate-6 ring-transparent transition-all duration-200 cursor-pointer checked:border-n-brand checked:bg-n-brand hover:enabled:bg-n-blue-border disabled:opacity-50"
    />
    <svg 
      class="pointer-events-none absolute w-3.5 h-3.5 z-20 stroke-white opacity-0 peer-checked:opacity-100 transition-opacity duration-200 left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
      viewBox="0 0 14 14"
      fill="none"
    >
      <path d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </div>
  <label for="checkbox-1" class="text-sm text-n-slate-12 cursor-pointer">Checkbox Label</label>
</div>
```

### Switch (Copy-Paste Ready)

```html
<div class="flex items-center gap-2">
  <button
    type="button"
    role="switch"
    aria-checked="false"
    class="relative h-4 w-7 rounded-full flex-shrink-0 bg-n-slate-6 transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-n-brand focus:ring-offset-2"
  >
    <span class="sr-only">Toggle</span>
    <span class="absolute top-0.5 left-0.5 h-3 w-3 rounded-full shadow-sm bg-n-background transform translate-x-0 transition-transform duration-200"></span>
  </button>
  <label class="text-sm text-n-slate-12">Switch Label</label>
</div>

<!-- Active State (aria-checked="true") -->
<button
  type="button"
  role="switch"
  aria-checked="true"
  class="relative h-4 w-7 rounded-full flex-shrink-0 bg-n-brand transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-n-brand focus:ring-offset-2"
>
  <span class="sr-only">Toggle</span>
  <span class="absolute top-0.5 left-0.5 h-3 w-3 rounded-full shadow-sm bg-n-background transform translate-x-3 transition-transform duration-200"></span>
</button>
```

### Badge (Copy-Paste Ready)

```html
<!-- Default -->
<span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-n-alpha-3 text-n-slate-11">
  Badge
</span>

<!-- Blue -->
<span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-n-blue-3 text-n-blue-11">
  New
</span>

<!-- Ruby (Error) -->
<span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-n-ruby-3 text-n-ruby-11">
  Error
</span>

<!-- Teal (Success) -->
<span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-n-teal-3 text-n-teal-11">
  Active
</span>

<!-- Amber (Warning) -->
<span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-n-amber-3 text-n-amber-11">
  Warning
</span>

<!-- With Icon -->
<span class="inline-flex items-center justify-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-n-blue-3 text-n-blue-11">
  <i class="i-lucide-check size-3"></i>
  <span>Verified</span>
</span>
```

### Toast/Snackbar (Copy-Paste Ready)

```html
<div class="fixed bottom-4 right-4 z-50 flex items-center gap-3 min-w-[300px] max-w-md px-4 py-3 rounded-lg bg-n-solid-3 shadow-lg outline outline-1 outline-n-container animate-fade-in-up">
  <i class="i-lucide-check-circle size-5 text-n-teal-10 flex-shrink-0"></i>
  <p class="text-sm text-n-slate-12 flex-1">Success message here</p>
  <button class="text-n-slate-11 hover:text-n-slate-12">
    <i class="i-lucide-x size-4"></i>
    <span class="sr-only">Close</span>
  </button>
</div>

<!-- Error Toast -->
<div class="fixed bottom-4 right-4 z-50 flex items-center gap-3 min-w-[300px] max-w-md px-4 py-3 rounded-lg bg-n-solid-3 shadow-lg outline outline-1 outline-n-container animate-fade-in-up">
  <i class="i-lucide-alert-circle size-5 text-n-ruby-10 flex-shrink-0"></i>
  <p class="text-sm text-n-slate-12 flex-1">Error message here</p>
  <button class="text-n-slate-11 hover:text-n-slate-12">
    <i class="i-lucide-x size-4"></i>
  </button>
</div>
```

### Empty State (Copy-Paste Ready)

```html
<div class="flex flex-col items-center justify-center py-12 px-6 text-center">
  <i class="i-lucide-inbox size-12 text-n-slate-9 mb-4"></i>
  <h3 class="text-lg font-semibold text-n-slate-12 mb-2">
    No Items Found
  </h3>
  <p class="text-sm text-n-slate-11 mb-6 max-w-sm">
    You don't have any items yet. Create your first item to get started.
  </p>
  <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 transition-colors duration-200">
    <i class="i-lucide-plus size-4"></i>
    <span>Create Item</span>
  </button>
</div>
```

### Banner (Copy-Paste Ready)

```html
<!-- Info Banner -->
<div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-n-blue-3 text-n-blue-11">
  <i class="i-lucide-info size-5 flex-shrink-0"></i>
  <div class="flex-1">
    <p class="text-sm font-medium">Information</p>
    <p class="text-sm">This is an informational message.</p>
  </div>
  <button class="hover:opacity-80">
    <i class="i-lucide-x size-4"></i>
  </button>
</div>

<!-- Success Banner -->
<div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-n-teal-3 text-n-teal-11">
  <i class="i-lucide-check-circle size-5 flex-shrink-0"></i>
  <div class="flex-1">
    <p class="text-sm font-medium">Success</p>
    <p class="text-sm">Operation completed successfully.</p>
  </div>
  <button class="hover:opacity-80">
    <i class="i-lucide-x size-4"></i>
  </button>
</div>

<!-- Warning Banner -->
<div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-n-amber-3 text-n-amber-11">
  <i class="i-lucide-alert-triangle size-5 flex-shrink-0"></i>
  <div class="flex-1">
    <p class="text-sm font-medium">Warning</p>
    <p class="text-sm">Please review this carefully.</p>
  </div>
  <button class="hover:opacity-80">
    <i class="i-lucide-x size-4"></i>
  </button>
</div>

<!-- Error Banner -->
<div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-n-ruby-3 text-n-ruby-11">
  <i class="i-lucide-alert-circle size-5 flex-shrink-0"></i>
  <div class="flex-1">
    <p class="text-sm font-medium">Error</p>
    <p class="text-sm">An error occurred. Please try again.</p>
  </div>
  <button class="hover:opacity-80">
    <i class="i-lucide-x size-4"></i>
  </button>
</div>
```

---

## 📐 Layout Patterns

### Page Container
```html
<div class="container mx-auto px-6 py-8">
  <!-- Page content -->
</div>
```

### Two-Column Layout
```html
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div>Left column</div>
  <div>Right column</div>
</div>
```

### Three-Column Grid
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <div>Column 1</div>
  <div>Column 2</div>
  <div>Column 3</div>
</div>
```

### Sidebar Layout
```html
<div class="flex h-screen">
  <!-- Sidebar -->
  <aside class="w-64 bg-n-solid-1 border-r border-n-container">
    <!-- Sidebar content -->
  </aside>
  
  <!-- Main Content -->
  <main class="flex-1 overflow-auto">
    <!-- Main content -->
  </main>
</div>
```

### Header with Actions
```html
<header class="flex items-center justify-between px-6 py-4 border-b border-n-container bg-n-background">
  <div class="flex items-center gap-3">
    <h1 class="text-xl font-semibold text-n-slate-12">Page Title</h1>
  </div>
  <div class="flex items-center gap-3">
    <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 transition-colors duration-200">
      Secondary
    </button>
    <button class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 transition-colors duration-200">
      <i class="i-lucide-plus size-4"></i>
      <span>Primary Action</span>
    </button>
  </div>
</header>
```

### Form with Fields
```html
<form class="flex flex-col gap-6 max-w-lg">
  <!-- Field 1 -->
  <div class="flex flex-col gap-1">
    <label for="field-1" class="text-sm font-medium text-n-slate-12">Field Label</label>
    <input 
      id="field-1"
      type="text"
      class="block w-full px-3 py-2.5 h-10 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand transition-all duration-500"
    />
  </div>
  
  <!-- Field 2 -->
  <div class="flex flex-col gap-1">
    <label for="field-2" class="text-sm font-medium text-n-slate-12">Another Field</label>
    <textarea 
      id="field-2"
      rows="4"
      class="block w-full px-3 py-2.5 rounded-lg border-none outline outline-1 outline-offset-[-1px] outline-n-weak bg-n-alpha-black2 text-sm text-n-slate-12 placeholder:text-n-slate-10 hover:outline-n-slate-6 focus:outline-n-brand transition-all duration-500 resize-y"
    ></textarea>
  </div>
  
  <!-- Actions -->
  <div class="flex justify-end gap-3">
    <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium border border-n-slate-6 bg-transparent text-n-slate-12 hover:bg-n-alpha-2 transition-colors duration-200">
      Cancel
    </button>
    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 h-10 rounded-lg text-sm font-medium bg-n-blue-9 text-white hover:bg-n-blue-10 transition-colors duration-200">
      Submit
    </button>
  </div>
</form>
```

### List with Avatars
```html
<ul class="divide-y divide-n-container">
  <li class="flex items-center justify-between py-3 px-4 hover:bg-n-alpha-1 transition-colors">
    <div class="flex items-center gap-3">
      <span class="relative inline-flex size-10 rounded-xl overflow-hidden flex-shrink-0">
        <img src="avatar.jpg" alt="User" class="w-full h-full object-cover" />
      </span>
      <div>
        <p class="text-sm font-medium text-n-slate-12">Item Title</p>
        <p class="text-xs text-n-slate-11">Item description or subtitle</p>
      </div>
    </div>
    <button class="inline-flex items-center justify-center size-8 rounded-lg text-sm font-medium bg-transparent text-n-slate-11 hover:bg-n-alpha-2 transition-colors duration-200">
      <i class="i-lucide-more-vertical size-4"></i>
    </button>
  </li>
  <!-- Repeat for more items -->
</ul>
```

---

## 🎭 State Patterns

### Button States
```html
<!-- Default -->
<button class="[base-classes]">Default</button>

<!-- Hover (automatic with hover:) -->
<button class="[base-classes] hover:bg-n-blue-10">Hover</button>

<!-- Active -->
<button class="[base-classes] active:bg-n-blue-11">Active</button>

<!-- Disabled -->
<button class="[base-classes] opacity-50 cursor-not-allowed" disabled>Disabled</button>

<!-- Loading -->
<button class="[base-classes] opacity-50 cursor-not-allowed" disabled>
  <div class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
  Loading...
</button>
```

### Input States
```html
<!-- Default -->
<input class="[base-classes] outline-n-weak hover:outline-n-slate-6 focus:outline-n-brand" />

<!-- Error -->
<input class="[base-classes] outline-n-ruby-8 hover:outline-n-ruby-9 focus:outline-n-ruby-9" />

<!-- Success -->
<input class="[base-classes] outline-n-teal-8 hover:outline-n-teal-9 focus:outline-n-teal-9" />

<!-- Disabled -->
<input class="[base-classes] opacity-50 cursor-not-allowed" disabled />
```

### Card States
```html
<!-- Default -->
<div class="[card-classes]">Default</div>

<!-- Hover (for clickable cards) -->
<div class="[card-classes] hover:bg-n-alpha-1 cursor-pointer transition-colors">Clickable</div>

<!-- Selected -->
<div class="[card-classes] outline-2 outline-n-brand">Selected</div>
```

---

## 🔤 Typography Quick Reference

```html
<!-- Heading 1 -->
<h1 class="text-2xl font-semibold text-n-slate-12">Heading 1</h1>

<!-- Heading 2 -->
<h2 class="text-xl font-semibold text-n-slate-12">Heading 2</h2>

<!-- Heading 3 -->
<h3 class="text-lg font-semibold text-n-slate-12">Heading 3</h3>

<!-- Body Large -->
<p class="text-base text-n-slate-12">Body large text</p>

<!-- Body Default -->
<p class="text-sm text-n-slate-12">Body default text</p>

<!-- Body Small -->
<p class="text-xs text-n-slate-11">Body small text</p>

<!-- Label -->
<label class="text-sm font-medium text-n-slate-12">Label</label>

<!-- Caption -->
<span class="text-xs text-n-slate-11">Caption text</span>

<!-- Link -->
<a href="#" class="text-sm text-n-blue-10 hover:text-n-blue-11 transition-colors">Link text</a>
```

---

## 🎨 Icon Quick Reference

### Common Icons (Lucide)

```html
<!-- Actions -->
<i class="i-lucide-plus"></i>          <!-- Add/Create -->
<i class="i-lucide-edit"></i>          <!-- Edit -->
<i class="i-lucide-trash"></i>         <!-- Delete -->
<i class="i-lucide-save"></i>          <!-- Save -->
<i class="i-lucide-x"></i>             <!-- Close/Cancel -->
<i class="i-lucide-check"></i>         <!-- Confirm/Success -->
<i class="i-lucide-copy"></i>          <!-- Copy -->
<i class="i-lucide-download"></i>      <!-- Download -->
<i class="i-lucide-upload"></i>        <!-- Upload -->

<!-- Navigation -->
<i class="i-lucide-arrow-left"></i>    <!-- Back -->
<i class="i-lucide-arrow-right"></i>   <!-- Forward -->
<i class="i-lucide-chevron-down"></i>  <!-- Dropdown -->
<i class="i-lucide-chevron-up"></i>    <!-- Collapse -->
<i class="i-lucide-menu"></i>          <!-- Menu -->
<i class="i-lucide-home"></i>          <!-- Home -->

<!-- Status -->
<i class="i-lucide-check-circle"></i>  <!-- Success -->
<i class="i-lucide-alert-circle"></i>  <!-- Error -->
<i class="i-lucide-alert-triangle"></i><!-- Warning -->
<i class="i-lucide-info"></i>          <!-- Info -->
<i class="i-lucide-loader-2"></i>      <!-- Loading (use with animate-spin) -->

<!-- Common -->
<i class="i-lucide-search"></i>        <!-- Search -->
<i class="i-lucide-filter"></i>        <!-- Filter -->
<i class="i-lucide-settings"></i>      <!-- Settings -->
<i class="i-lucide-user"></i>          <!-- User -->
<i class="i-lucide-mail"></i>          <!-- Email -->
<i class="i-lucide-phone"></i>         <!-- Phone -->
<i class="i-lucide-calendar"></i>      <!-- Calendar -->
<i class="i-lucide-clock"></i>         <!-- Time -->
<i class="i-lucide-inbox"></i>         <!-- Inbox -->
<i class="i-lucide-more-vertical"></i> <!-- More options -->
```

---

## ⚡ Animation Classes

```css
/* Transitions (apply to base) */
transition-colors duration-200    /* Color changes */
transition-all duration-200       /* All properties */
transition-transform duration-300 /* Movement */

/* Pre-built Animations */
animate-spin                      /* Continuous rotation */
animate-fade-in-up               /* Modal/Toast entrance */
animate-loader-pulse             /* Pulsing loader */
animate-wiggle                   /* Error shake */
animate-shake                    /* Validation error */

/* Loading Spinner */
<div class="inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
```

---

## ✅ Checklist for Every Component

When creating a component, ensure:

- [ ] Uses semantic color tokens (`n-*`)
- [ ] Has focus state with ring (`focus:ring-2 focus:ring-n-brand`)
- [ ] Includes hover state
- [ ] Includes disabled state (if applicable)
- [ ] Has proper ARIA attributes
- [ ] Supports keyboard navigation
- [ ] Works in dark mode (automatic with `n-*` tokens)
- [ ] Uses proper spacing (4px increments)
- [ ] Has smooth transitions (`transition-* duration-200`)
- [ ] Text meets contrast requirements
- [ ] Responsive on mobile (`sm:`, `md:`, `lg:`)

---

## 🚫 Common Mistakes to Avoid

1. **Don't use legacy colors**: Use `n-slate-*` NOT `woot-*` or `slate-*`
2. **Don't forget focus states**: Always add `focus:ring-2 focus:ring-n-brand`
3. **Don't use inline styles**: Use Tailwind classes (except dynamic values)
4. **Don't forget dark mode**: Use semantic tokens that auto-adapt
5. **Don't skip transitions**: Add `transition-* duration-200` for interactions
6. **Don't use arbitrary values**: Stick to spacing scale (0, 0.5, 1, 1.5, 2...)
7. **Don't forget accessibility**: Add `aria-*` and `sr-only` labels
8. **Don't use custom CSS**: Use Tailwind utilities only

---

## 🔍 Finding More Components

All Vue components are in:
```
app/javascript/dashboard/components-next/
```

Each component typically has:
- `ComponentName.vue` - Main component
- `ComponentName.story.vue` - Storybook examples

To see a component in action:
```bash
pnpm story:dev
```

---

## 📚 Full Documentation

For complete details, see [`DESIGN_SYSTEM.md`](./DESIGN_SYSTEM.md)

---

**Version:** 1.0.0 | **Updated:** December 2024
