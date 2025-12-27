# Chatwoot UI Component Stories

## Overview

Chatwoot uses **[Histoire](https://histoire.dev/)** as its component documentation and playground tool. Histoire is a modern, Vue 3-native alternative to Storybook, specifically designed for the Vue ecosystem.

> **Note:** This project does NOT use Storybook. It uses Histoire, which serves the same purpose but is optimized for Vue 3 with Composition API.

## Quick Start

### Development Server

```bash
# Start the Histoire development server (runs on port 6179)
pnpm story:dev
```

### Build for Production

```bash
# Build static documentation site
pnpm story:build

# Preview built documentation
pnpm story:preview
```

## Configuration

The Histoire configuration is located at the repository root:

- **Config File:** `histoire.config.ts`
- **Setup File:** `histoire.setup.ts`
- **Port:** 6179

### Configuration Details

```typescript
// histoire.config.ts
import { defineConfig } from 'histoire';
import { HstVue } from '@histoire/plugin-vue';

export default defineConfig({
  setupFile: './histoire.setup.ts',
  plugins: [HstVue()],
  collectMaxThreads: 4,
  vite: {
    server: {
      port: 6179,
    },
  },
  viteIgnorePlugins: ['vite-plugin-ruby'],
  theme: {
    darkClass: 'dark',
    title: '@chatwoot/design',
    // Logo configuration...
  },
  // ...
});
```

## Story Files

### Naming Convention

Story files use the `.story.vue` extension and are co-located with their components:

```
Component.vue        # Main component
Component.story.vue  # Story file
```

### Story File Locations

All story files are located in:
```
app/javascript/dashboard/components-next/
```

### Current Story Count

The project contains **90+ story files** covering various UI components.

## Component Categories

### Core UI Components
- `button/` - Button, ConfirmButton
- `input/` - Input fields
- `checkbox/` - Checkbox component
- `switch/` - Toggle switches
- `textarea/` - Text area inputs
- `dialog/` - Modal dialogs
- `dropdown-menu/` - Dropdown menus
- `selectmenu/` - Select menus
- `combobox/` - Combo box components
- `spinner/` - Loading spinners
- `avatar/` - User avatars
- `badge/` - Status badges
- `tabbar/` - Tab navigation

### Feature Components
- `message/` - Message bubbles (Text, Media, Email, Instagram)
- `copilot/` - AI Copilot components
- `captain/` - Captain AI assistant components
- `HelpCenter/` - Help center components
- `Contacts/` - Contact management components
- `Conversation/` - Conversation components
- `Label/` - Label components
- `filter/` - Filter components

### Layout Components
- `breadcrumb/` - Breadcrumb navigation
- `pagination/` - Pagination footer
- `feature-spotlight/` - Feature spotlights
- `changelog-card/` - Changelog cards

## Writing Stories

### Basic Story Structure

```vue
<script setup>
import MyComponent from './MyComponent.vue';

// Define variants and options
const VARIANTS = ['primary', 'secondary'];
</script>

<template>
  <Story title="Components/MyComponent">
    <Variant title="Basic">
      <MyComponent label="Basic Example" />
    </Variant>

    <Variant title="Variants">
      <div class="flex gap-2 p-4 bg-n-background">
        <MyComponent
          v-for="variant in VARIANTS"
          :key="variant"
          :variant="variant"
        />
      </div>
    </Variant>
  </Story>
</template>
```

### Story Best Practices

1. **Group related variants** in meaningful sections
2. **Use realistic data** in examples
3. **Show all states** (loading, disabled, error, success)
4. **Include dark mode** preview when relevant
5. **Document props** with clear examples

## Related Documentation

For complete design system documentation, see:

- **[../docs/DESIGN_SYSTEM.md](../docs/DESIGN_SYSTEM.md)** - Complete design system guide
- **[../docs/AI_DESIGN_GUIDE.md](../docs/AI_DESIGN_GUIDE.md)** - Quick reference for development
- **[../docs/DESIGN_SYSTEM_INDEX.md](../docs/DESIGN_SYSTEM_INDEX.md)** - Documentation index

## Dependencies

Histoire-related packages in `package.json`:

```json
{
  "devDependencies": {
    "histoire": "0.17.15",
    "@histoire/plugin-vue": "0.17.15"
  }
}
```

## Accessing the Component Library

1. Clone the repository
2. Install dependencies: `pnpm install`
3. Run Histoire: `pnpm story:dev`
4. Open browser at `http://localhost:6179`

---

**Last Updated:** December 2024
