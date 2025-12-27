# Comprehensive Guide: Migrating to shadcn-svelte with Histoire for Chatwoot

This document provides a complete migration guide for converting Chatwoot's frontend from Vue 3 to **Svelte 5 with SvelteKit SPA** using **shadcn-svelte** as the UI component library and **Histoire** for component documentation.

---

## Table of Contents

1. [Overview](#overview)
2. [Technology Stack](#technology-stack)
3. [Required Libraries](#required-libraries)
4. [Project Setup](#project-setup)
5. [shadcn-svelte Components](#shadcn-svelte-components)
6. [Histoire Integration](#histoire-integration)
7. [Component Mapping: Chatwoot Vue → shadcn-svelte](#component-mapping-chatwoot-vue--shadcn-svelte)
8. [Design System Migration](#design-system-migration)
9. [Migration Strategy](#migration-strategy)
10. [Estimated Timeline](#estimated-timeline)

---

## Overview

### What is shadcn-svelte?

[shadcn-svelte](https://shadcn-svelte.com) is an unofficial, community-led Svelte port of [shadcn/ui](https://ui.shadcn.com). It is **not a component library** in the traditional sense — instead, it provides:

- **Open Code**: You get the actual component source code that you can customize
- **Composition**: Components use a common, composable interface
- **Beautiful Defaults**: Carefully designed default styles
- **AI-Ready**: Code that LLMs can easily understand and modify

### Why shadcn-svelte?

| Feature | shadcn-svelte | Traditional Component Libraries |
|---------|---------------|--------------------------------|
| Customization | Full source code access | Limited to props/slots |
| Bundle size | Only what you use | Full library import |
| Updates | Update dependencies only | Library updates may break |
| Design system | Your own CSS variables | Library's design tokens |

---

## Technology Stack

### Core Framework

| Package | Version | Purpose |
|---------|---------|---------|
| `svelte` | `^5.7.0` | UI framework |
| `@sveltejs/kit` | `^2.9.0` | Application framework |
| `@sveltejs/adapter-static` | `^3.0.0` | SPA build adapter |

### UI Components (shadcn-svelte)

| Package | Version | Purpose |
|---------|---------|---------|
| `bits-ui` | `^1.0.0` | Headless UI primitives |
| `tailwindcss` | `^4.0.0` | CSS framework |
| `tailwindcss-animate` | `^1.0.7` | Animation utilities |
| `clsx` | `^2.1.0` | Class name utility |
| `tailwind-merge` | `^2.6.0` | Tailwind class merging |

### Additional Libraries

| Package | Version | Purpose |
|---------|---------|---------|
| `mode-watcher` | `^1.0.0` | Dark mode support |
| `svelte-sonner` | `^1.0.0` | Toast notifications |
| `paneforge` | `^1.0.0-next.5` | Resizable panels |
| `vaul-svelte` | `^1.0.0-next.7` | Drawer component |
| `@lucide/svelte` | `^0.482.0` | Icon library |
| `embla-carousel-svelte` | `^8.0.0` | Carousel component |
| `layerchart` | `^0.46.0` | Charts (alternative to chart.js) |

### Component Documentation

| Package | Version | Purpose |
|---------|---------|---------|
| `histoire` | `^0.17.15` | Component stories |
| `@histoire/plugin-svelte` | `^0.17.15` | Svelte plugin for Histoire |

### Form Handling

| Package | Version | Purpose |
|---------|---------|---------|
| `sveltekit-superforms` | `^2.0.0` | Form validation |
| `zod` | `^3.23.0` | Schema validation |
| `formsnap` | `^2.0.0` | Form components |

### State Management

| Package | Purpose |
|---------|---------|
| Built-in `$state` runes | Local component state |
| Svelte stores | Global state management |
| `@tanstack/svelte-query` | Server state / caching (optional) |

### Internationalization

| Package | Version | Purpose |
|---------|---------|---------|
| `svelte-i18n` | `^4.0.0` | i18n support |
| `@formatjs/intl-locale` | `^3.0.0` | Locale detection |

### HTTP Client

| Package | Version | Purpose |
|---------|---------|---------|
| `ky` | `^1.7.0` | HTTP client (lighter than axios) |
| — OR — | | |
| `axios` | `^1.7.0` | HTTP client (familiar API) |

### Data Tables

| Package | Version | Purpose |
|---------|---------|---------|
| `@tanstack/table-core` | `^8.20.0` | Headless table utilities |

---

## Required Libraries

### Complete `package.json` Dependencies

```json
{
  "name": "chatwoot-svelte",
  "type": "module",
  "scripts": {
    "dev": "vite dev",
    "build": "vite build",
    "preview": "vite preview",
    "check": "svelte-kit sync && svelte-check --tsconfig ./tsconfig.json",
    "story:dev": "histoire dev",
    "story:build": "histoire build",
    "story:preview": "histoire preview"
  },
  "dependencies": {
    "@lucide/svelte": "^0.482.0",
    "@tanstack/table-core": "^8.20.0",
    "bits-ui": "^1.0.0",
    "clsx": "^2.1.0",
    "embla-carousel-svelte": "^8.0.0",
    "formsnap": "^2.0.0",
    "ky": "^1.7.0",
    "layerchart": "^0.46.0",
    "mode-watcher": "^1.0.0",
    "paneforge": "^1.0.0-next.5",
    "svelte-i18n": "^4.0.0",
    "svelte-sonner": "^1.0.0",
    "sveltekit-superforms": "^2.0.0",
    "tailwind-merge": "^2.6.0",
    "vaul-svelte": "^1.0.0-next.7",
    "zod": "^3.23.0"
  },
  "devDependencies": {
    "@histoire/plugin-svelte": "^0.17.15",
    "@sveltejs/adapter-static": "^3.0.0",
    "@sveltejs/kit": "^2.9.0",
    "@sveltejs/vite-plugin-svelte": "^5.0.0",
    "@tailwindcss/typography": "^0.5.15",
    "histoire": "^0.17.15",
    "svelte": "^5.7.0",
    "svelte-check": "^4.1.1",
    "tailwindcss": "^4.0.0",
    "tailwindcss-animate": "^1.0.7",
    "typescript": "^5.6.0",
    "vite": "^6.0.0"
  }
}
```

---

## Project Setup

### Step 1: Create SvelteKit Project

```bash
# Create new SvelteKit project with Tailwind
pnpx sv create chatwoot-svelte --add tailwindcss

cd chatwoot-svelte
```

### Step 2: Configure SPA Mode

```javascript
// svelte.config.js
import adapter from '@sveltejs/adapter-static';
import { vitePreprocess } from '@sveltejs/vite-plugin-svelte';

/** @type {import('@sveltejs/kit').Config} */
const config = {
  preprocess: vitePreprocess(),
  kit: {
    adapter: adapter({
      fallback: 'index.html', // SPA mode
      pages: 'build',
      assets: 'build',
      precompress: false,
      strict: true
    }),
    prerender: {
      entries: [] // No prerendering for SPA
    },
    alias: {
      '$lib': './src/lib',
      '$lib/*': './src/lib/*'
    }
  }
};

export default config;
```

### Step 3: Initialize shadcn-svelte

```bash
# Initialize shadcn-svelte
pnpx shadcn-svelte@latest init
```

Configure when prompted:
```
Which base color would you like to use? › Slate
Where is your global CSS file? › src/routes/layout.css
Configure the import alias for lib: › $lib
Configure the import alias for components: › $lib/components
Configure the import alias for utils: › $lib/utils
Configure the import alias for hooks: › $lib/hooks
Configure the import alias for ui: › $lib/components/ui
```

### Step 4: Add shadcn-svelte Components

```bash
# Add commonly needed components
pnpx shadcn-svelte@latest add button
pnpx shadcn-svelte@latest add input
pnpx shadcn-svelte@latest add dialog
pnpx shadcn-svelte@latest add dropdown-menu
pnpx shadcn-svelte@latest add select
pnpx shadcn-svelte@latest add checkbox
pnpx shadcn-svelte@latest add switch
pnpx shadcn-svelte@latest add tabs
pnpx shadcn-svelte@latest add avatar
pnpx shadcn-svelte@latest add badge
pnpx shadcn-svelte@latest add card
pnpx shadcn-svelte@latest add table
pnpx shadcn-svelte@latest add form
pnpx shadcn-svelte@latest add sonner
pnpx shadcn-svelte@latest add sidebar
pnpx shadcn-svelte@latest add tooltip
pnpx shadcn-svelte@latest add popover
pnpx shadcn-svelte@latest add scroll-area
pnpx shadcn-svelte@latest add spinner
pnpx shadcn-svelte@latest add pagination
pnpx shadcn-svelte@latest add calendar
pnpx shadcn-svelte@latest add command
pnpx shadcn-svelte@latest add drawer
pnpx shadcn-svelte@latest add sheet
```

### Step 5: Install Additional Dependencies

```bash
# Core dependencies
pnpm add ky svelte-i18n zod sveltekit-superforms formsnap

# Optional but recommended
pnpm add @tanstack/table-core embla-carousel-svelte layerchart

# Dev dependencies for Histoire
pnpm add -D histoire @histoire/plugin-svelte
```

---

## shadcn-svelte Components

### Available Components (59 total)

| Category | Components |
|----------|-----------|
| **Layout** | Card, Separator, Scroll Area, Resizable, Sidebar |
| **Navigation** | Breadcrumb, Navigation Menu, Pagination, Tabs, Menubar |
| **Forms** | Button, Checkbox, Input, Label, Radio Group, Select, Slider, Switch, Textarea, Form, Field, Input OTP, Native Select |
| **Feedback** | Alert, Alert Dialog, Sonner (Toast), Progress, Skeleton, Spinner |
| **Overlay** | Dialog, Drawer, Popover, Sheet, Tooltip, Hover Card |
| **Data Display** | Avatar, Badge, Calendar, Carousel, Chart, Table, Data Table |
| **Disclosure** | Accordion, Collapsible |
| **Command** | Command (cmdk), Context Menu, Dropdown Menu |
| **Typography** | Typography components |
| **Utility** | Aspect Ratio, Toggle, Toggle Group, Button Group, Kbd, Item |

---

## Histoire Integration

### Step 1: Configure Histoire

```typescript
// vite.config.ts
import { sveltekit } from '@sveltejs/kit/vite';
import { HstSvelte } from '@histoire/plugin-svelte';
import { defineConfig } from 'vite';

export default defineConfig({
  plugins: [sveltekit()],
  histoire: {
    plugins: [HstSvelte()],
    setupFile: './src/histoire.setup.ts',
    theme: {
      darkClass: 'dark',
      title: '@chatwoot/design-svelte',
      logo: {
        square: './static/logo-thumbnail.svg',
        light: './static/logo.png',
        dark: './static/logo-dark.png',
      },
    },
    tree: {
      groups: [
        { id: 'top', title: '' },
        { id: 'components', title: 'Components', include: () => true },
      ],
    },
  },
});
```

### Step 2: Create Setup File

```typescript
// src/histoire.setup.ts
import './routes/layout.css';
```

### Step 3: Write Stories

```svelte
<!-- src/lib/components/ui/button/Button.story.svelte -->
<script>
  import { Button } from './index.js';
  
  export let Hst;
  
  const variants = ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link'];
  const sizes = ['default', 'sm', 'lg', 'icon'];
</script>

<Hst.Story title="Components/Button" layout={{ type: 'grid', width: '800px' }}>
  <Hst.Variant title="Variants">
    <div class="flex flex-wrap gap-2 p-4">
      {#each variants as variant}
        <Button {variant}>{variant}</Button>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sizes">
    <div class="flex flex-wrap items-center gap-2 p-4">
      {#each sizes as size}
        <Button {size}>{size}</Button>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Icons">
    <div class="flex flex-wrap gap-2 p-4">
      <Button>
        <Plus class="mr-2 size-4" />
        Add Item
      </Button>
      <Button variant="outline">
        <Mail class="mr-2 size-4" />
        Email
      </Button>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Loading State">
    <div class="flex flex-wrap gap-2 p-4">
      <Button disabled>
        <Loader2 class="mr-2 size-4 animate-spin" />
        Loading...
      </Button>
    </div>
  </Hst.Variant>
</Hst.Story>
```

### Step 4: Run Histoire

```bash
# Development
pnpm story:dev

# Build
pnpm story:build

# Preview
pnpm story:preview
```

---

## Component Mapping: Chatwoot Vue → shadcn-svelte

### Core Components

| Chatwoot Vue Component | shadcn-svelte Equivalent |
|----------------------|-------------------------|
| `Button.vue` | `Button` from shadcn-svelte |
| `Input.vue` | `Input` from shadcn-svelte |
| `Checkbox.vue` | `Checkbox` from shadcn-svelte |
| `Switch.vue` | `Switch` from shadcn-svelte |
| `TextArea.vue` | `Textarea` from shadcn-svelte |
| `Dialog.vue` | `Dialog` from shadcn-svelte |
| `DropdownMenu.vue` | `Dropdown Menu` from shadcn-svelte |
| `SelectMenu.vue` | `Select` from shadcn-svelte |
| `ComboBox.vue` | `Combobox` (Command + Popover) |
| `Avatar.vue` | `Avatar` from shadcn-svelte |
| `Spinner.vue` | `Spinner` from shadcn-svelte |
| `TabBar.vue` | `Tabs` from shadcn-svelte |
| `Breadcrumb.vue` | `Breadcrumb` from shadcn-svelte |

### Feature Components (Need Custom Implementation)

| Chatwoot Component | Implementation Approach |
|-------------------|------------------------|
| Message Bubbles | Custom Svelte components with shadcn styling |
| Contact Card | Extend `Card` component |
| Conversation List | Custom with `Scroll Area` + virtual scrolling |
| Help Center | Custom pages with shadcn components |
| Captain AI | Custom components |

---

## Design System Migration

### CSS Variable Mapping

Chatwoot uses custom CSS variables that need to be mapped to shadcn-svelte's convention:

```css
/* Chatwoot Variables → shadcn-svelte Variables */

/* Background colors */
--n-background       → --background
--n-solid-2         → --card
--n-alpha-1         → --muted

/* Text colors */
--n-slate-12        → --foreground
--n-slate-11        → --muted-foreground

/* Border colors */
--n-weak            → --border
--n-container       → --border
--n-strong          → --ring

/* Brand colors */
--n-blue-9          → --primary
--n-ruby-9          → --destructive
--n-teal-9          → Custom success variable
--n-amber-9         → Custom warning variable

/* Sidebar (shadcn-svelte already has these) */
--sidebar-background
--sidebar-foreground
--sidebar-primary
--sidebar-accent
--sidebar-border
```

### Custom Color Extensions

```css
/* src/routes/layout.css - Add custom colors */
:root {
  /* Keep shadcn-svelte defaults, add custom ones */
  --success: oklch(0.723 0.191 149.579);
  --success-foreground: oklch(0.985 0 0);
  --warning: oklch(0.84 0.16 84);
  --warning-foreground: oklch(0.28 0.07 46);
  --info: oklch(0.546 0.245 262.881);
  --info-foreground: oklch(0.985 0 0);
}

.dark {
  --success: oklch(0.696 0.17 162.48);
  --success-foreground: oklch(0.145 0 0);
  --warning: oklch(0.828 0.189 84.429);
  --warning-foreground: oklch(0.145 0 0);
  --info: oklch(0.488 0.243 264.376);
  --info-foreground: oklch(0.985 0 0);
}

@theme inline {
  --color-success: var(--success);
  --color-success-foreground: var(--success-foreground);
  --color-warning: var(--warning);
  --color-warning-foreground: var(--warning-foreground);
  --color-info: var(--info);
  --color-info-foreground: var(--info-foreground);
}
```

---

## Migration Strategy

### Phase 1: Foundation (Weeks 1-2)

1. ✅ Create new SvelteKit project
2. ✅ Configure SPA mode with adapter-static
3. ✅ Initialize shadcn-svelte
4. ✅ Add all needed shadcn-svelte components
5. ✅ Set up Histoire
6. ✅ Configure custom color variables
7. ✅ Set up i18n with svelte-i18n

### Phase 2: Core Components (Weeks 3-6)

1. Build custom components not available in shadcn-svelte:
   - Message bubbles (text, media, email, instagram)
   - Conversation cards
   - Contact cards
   - File upload
   - Audio recorder
   
2. Create Histoire stories for each component

3. Port utility functions from `@chatwoot/utils`

### Phase 3: Feature Modules (Weeks 7-12)

1. **Conversations Module**
   - Conversation list
   - Message thread
   - Reply box
   - Attachments

2. **Contacts Module**
   - Contact list
   - Contact details
   - Contact form

3. **Help Center Module**
   - Article list
   - Category navigation
   - Article editor

4. **Settings Module**
   - Account settings
   - Team settings
   - Inbox configuration

### Phase 4: Integration (Weeks 13-16)

1. API integration with existing Chatwoot backend
2. WebSocket/ActionCable integration
3. Authentication flow
4. State management setup
5. Testing and bug fixes

---

## Estimated Timeline

| Phase | Duration | Deliverables |
|-------|----------|--------------|
| Foundation | 2 weeks | Project setup, shadcn-svelte, Histoire |
| Core Components | 4 weeks | Custom components, stories |
| Feature Modules | 6 weeks | Full module implementations |
| Integration | 4 weeks | API, WebSocket, Auth, Testing |
| **Total** | **16 weeks** | Complete Svelte migration |

---

## Quick Start Commands

```bash
# Create project
pnpx sv create chatwoot-svelte --add tailwindcss
cd chatwoot-svelte

# Initialize shadcn-svelte
pnpx shadcn-svelte@latest init

# Add all components at once
pnpx shadcn-svelte@latest add button input dialog dropdown-menu select checkbox switch tabs avatar badge card table form sonner sidebar tooltip popover scroll-area spinner pagination calendar command drawer sheet accordion alert alert-dialog breadcrumb carousel collapsible context-menu hover-card label menubar navigation-menu progress radio-group resizable separator skeleton slider textarea toggle toggle-group

# Install additional dependencies
pnpm add ky svelte-i18n zod sveltekit-superforms formsnap @tanstack/table-core embla-carousel-svelte layerchart mode-watcher

# Install Histoire
pnpm add -D histoire @histoire/plugin-svelte

# Run development
pnpm dev

# Run Histoire
pnpm story:dev
```

---

## References

- [shadcn-svelte Documentation](https://shadcn-svelte.com)
- [SvelteKit Documentation](https://kit.svelte.dev)
- [Svelte 5 Documentation](https://svelte.dev/docs/svelte)
- [Histoire Documentation](https://histoire.dev)
- [Bits UI Documentation](https://bits-ui.com)
- [Tailwind CSS v4](https://tailwindcss.com)

---

**Document Version:** 1.0.0  
**Created:** December 2024
