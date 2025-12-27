# Svelte UI Migration Project

This document provides an overview of the new Svelte UI project located at `custom/ui/svelte-ui/`.

## Project Location

```
custom/ui/
├── storybooks/          # Documentation (this directory)
│   ├── README.md                        # Vue Histoire docs
│   ├── STORY_FILES_INDEX.md             # Vue component index
│   ├── SVELTE_CONVERSION_CRITIQUE.md    # Conversion analysis
│   ├── SHADCN_SVELTE_MIGRATION_GUIDE.md # Migration guide
│   └── SVELTE_UI_PROJECT.md             # This file
└── svelte-ui/           # New SvelteKit project
    ├── src/
    │   ├── lib/components/ui/           # UI components
    │   └── routes/                      # SvelteKit routes
    ├── package.json
    └── README.md
```

## Quick Start

```bash
# Navigate to svelte-ui project
cd custom/ui/svelte-ui

# Install dependencies
pnpm install

# Run Histoire for component stories
pnpm story:dev        # Opens at http://localhost:6006

# Run SvelteKit development server
pnpm dev              # Opens at http://localhost:5173
```

## Implemented Components

### Phase 1: Primitives (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Button** | `button/index.ts`, `button.svelte`, `Button.story.svelte` | ✅ |
| **Input** | `input/index.ts`, `input.svelte`, `Input.story.svelte` | ✅ |
| **Textarea** | `textarea/index.ts`, `textarea.svelte`, `Textarea.story.svelte` | ✅ |
| **Checkbox** | `checkbox/index.ts`, `checkbox.svelte`, `Checkbox.story.svelte` | ✅ |
| **Switch** | `switch/index.ts`, `switch.svelte`, `Switch.story.svelte` | ✅ |
| **Label** | `label/index.ts`, `label.svelte` | ✅ |
| **Separator** | `separator/index.ts`, `separator.svelte` | ✅ |
| **Badge** | `badge/index.ts`, `badge.svelte`, `Badge.story.svelte` | ✅ |
| **Avatar** | `avatar/*.svelte`, `Avatar.story.svelte` | ✅ |
| **Card** | `card/*.svelte`, `Card.story.svelte` | ✅ |
| **Spinner** | `spinner/index.ts`, `spinner.svelte`, `Spinner.story.svelte` | ✅ |

### Phase 2: Overlays & Navigation (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Dialog** | `dialog/*.svelte`, `Dialog.story.svelte` | ✅ |
| **Dropdown Menu** | `dropdown-menu/*.svelte`, `DropdownMenu.story.svelte` | ✅ |
| **Popover** | `popover/*.svelte`, `Popover.story.svelte` | ✅ |
| **Tooltip** | `tooltip/*.svelte`, `Tooltip.story.svelte` | ✅ |
| **Tabs** | `tabs/*.svelte`, `Tabs.story.svelte` | ✅ |
| **Select** | `select/*.svelte`, `Select.story.svelte` | ✅ |
| **Accordion** | `accordion/*.svelte`, `Accordion.story.svelte` | ✅ |
| **Alert** | `alert/*.svelte`, `Alert.story.svelte` | ✅ |
| **Progress** | `progress/*.svelte`, `Progress.story.svelte` | ✅ |
| **Scroll Area** | `scroll-area/*.svelte`, `ScrollArea.story.svelte` | ✅ |
| **Table** | `table/*.svelte`, `Table.story.svelte` | ✅ |
| **Skeleton** | `skeleton/*.svelte`, `Skeleton.story.svelte` | ✅ |
| **Sidebar** | `sidebar/*.svelte`, `Sidebar.story.svelte` | ✅ |

### Phase 3: Chatwoot-Specific (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Message Bubble** | `message-bubble/*.svelte`, `MessageBubble.story.svelte` | ✅ |
| **Conversation Card** | `conversation-card/*.svelte`, `ConversationCard.story.svelte` | ✅ |
| **Contact Card** | `contact-card/*.svelte`, `ContactCard.story.svelte` | ✅ |
| **Reply Box** | `reply-box/*.svelte`, `ReplyBox.story.svelte` | ✅ |

### Phase 4: Additional Components (Future)

| Component | Status |
|-----------|--------|
| Command (cmdk) | ⏳ Pending |
| Sheet | ⏳ Pending |
| Toast (Sonner) | ⏳ Pending |
| Calendar | ⏳ Pending |
| Date Picker | ⏳ Pending |
| Form Validation | ⏳ Pending |

## Histoire Stories

Each component includes comprehensive Histoire stories demonstrating:

1. **Default State** - Basic usage
2. **Variants** - All visual variants
3. **Sizes** - All size options
4. **States** - Disabled, loading, error states
5. **Examples** - Real-world usage patterns

### Story File Structure

```svelte
<script lang="ts">
  import { Button } from './index.js';
  
  export let Hst: any;
  
  const variants = ['default', 'destructive', 'outline', ...];
</script>

<Hst.Story title="Primitives/Button" icon="lucide:mouse-pointer-click">
  <Hst.Variant title="Default">
    <Button>Click me</Button>
  </Hst.Variant>

  <Hst.Variant title="Variants">
    {#each variants as variant}
      <Button {variant}>{variant}</Button>
    {/each}
  </Hst.Variant>
</Hst.Story>
```

## Technology Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Svelte | ^5.7.0 | UI Framework |
| SvelteKit | ^2.9.0 | App Framework |
| shadcn-svelte | - | Component patterns |
| bits-ui | ^1.0.0 | Headless primitives |
| Tailwind CSS | ^3.4.17 | Styling |
| Histoire | ^0.17.17 | Component stories |
| TypeScript | ^5.6.0 | Type safety |

## Color System

The project includes custom color variants beyond shadcn defaults:

```css
:root {
  /* Standard shadcn colors */
  --primary: 221.2 83.2% 53.3%;
  --secondary: 210 40% 96.1%;
  --destructive: 0 72.2% 50.6%;
  
  /* Custom additions for Chatwoot */
  --success: 142 76% 36%;
  --warning: 38 92% 50%;
  --info: 199 89% 48%;
}
```

## Button Variants

The Button component includes 9 variants:

| Variant | Use Case |
|---------|----------|
| `default` | Primary actions |
| `secondary` | Secondary actions |
| `destructive` | Delete, remove actions |
| `outline` | Bordered buttons |
| `ghost` | Subtle actions |
| `link` | Navigation-style |
| `success` | Confirm, complete actions |
| `warning` | Cautionary actions |
| `info` | Informational actions |

## Integration with Laravel API

The project is configured as an SPA that can connect to a Laravel backend:

```typescript
// src/lib/api/client.ts
import ky from 'ky';

export const api = ky.create({
  prefixUrl: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  hooks: {
    beforeRequest: [
      request => {
        const token = localStorage.getItem('token');
        if (token) {
          request.headers.set('Authorization', `Bearer ${token}`);
        }
      }
    ]
  }
});
```

## Next Steps

1. **Install Dependencies**: `cd custom/ui/svelte-ui && pnpm install`
2. **Run Histoire**: `pnpm story:dev` to view component stories
3. **Add More Components**: Continue implementing shadcn-svelte components
4. **Build Custom Components**: Create Chatwoot-specific components
5. **API Integration**: Connect to Laravel backend

## Related Documentation

- [SHADCN_SVELTE_MIGRATION_GUIDE.md](./SHADCN_SVELTE_MIGRATION_GUIDE.md) - Complete migration guide
- [SVELTE_CONVERSION_CRITIQUE.md](./SVELTE_CONVERSION_CRITIQUE.md) - Feasibility analysis
- [svelte-ui/README.md](../svelte-ui/README.md) - Project README

---

**Created:** December 2024  
**Status:** Phase 1, 2 & 3 Complete - 30 Components with Stories
