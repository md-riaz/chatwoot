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

### Phase 4: Navigation & Core Components (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Breadcrumb** | `breadcrumb/*.svelte`, `Breadcrumb.story.svelte` | ✅ |
| **PaginationFooter** | `pagination/*.svelte`, `PaginationFooter.story.svelte` | ✅ |
| **TabBar** | `tab-bar/*.svelte`, `TabBar.story.svelte` | ✅ |
| **Combobox** | `combobox/*.svelte`, `Combobox.story.svelte` | ✅ |
| **TagInput** | `tag-input/*.svelte`, `TagInput.story.svelte` | ✅ |
| **InlineInput** | `inline-input/*.svelte`, `InlineInput.story.svelte` | ✅ |
| **PhoneInput** | `phone-input/*.svelte`, `PhoneInput.story.svelte` | ✅ |
| **ConfirmButton** | `confirm-button/*.svelte`, `ConfirmButton.story.svelte` | ✅ |
| **Flag** | `flag/*.svelte`, `Flag.story.svelte` | ✅ |
| **FileIcon** | `file-icon/*.svelte`, `FileIcon.story.svelte` | ✅ |
| **Filter** | `filter/*.svelte`, `Filter.story.svelte` | ✅ |

### Phase 5: Business Components (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **ArticleCard** | `article-card/*.svelte`, `ArticleCard.story.svelte` | ✅ |
| **CategoryCard** | `category-card/*.svelte`, `CategoryCard.story.svelte` | ✅ |
| **LocaleCard** | `locale-card/*.svelte`, `LocaleCard.story.svelte` | ✅ |
| **AssistantCard** | `assistant-card/*.svelte`, `AssistantCard.story.svelte` | ✅ |
| **DocumentCard** | `document-card/*.svelte`, `DocumentCard.story.svelte` | ✅ |
| **Copilot** | `copilot/*.svelte`, `Copilot.story.svelte` | ✅ |
| **LabelInput** | `label-input/*.svelte`, `LabelInput.story.svelte` | ✅ |
| **CustomAttributes** | `custom-attributes/*.svelte`, `CustomAttributes.story.svelte` | ✅ |
| **FeatureSpotlight** | `feature-spotlight/*.svelte`, `FeatureSpotlight.story.svelte` | ✅ |
| **ChangelogCard** | `changelog-card/*.svelte`, `ChangelogCard.story.svelte` | ✅ |

### Phase 6: Additional UI Components (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Toast** | `toast/*.svelte`, `Toast.story.svelte` | ✅ |
| **Sheet** | `sheet/*.svelte`, `Sheet.story.svelte` | ✅ |
| **Command** | `command/*.svelte`, `Command.story.svelte` | ✅ |
| **RadioGroup** | `radio-group/*.svelte`, `RadioGroup.story.svelte` | ✅ |

### Future Components (Planned)

| Component | Status |
|-----------|--------|
| Calendar | ⏳ Pending |
| Date Picker | ⏳ Pending |
| Color Picker | ⏳ Pending |
| Rich Text Editor | ⏳ Pending |
| Chart/Data Visualization | ⏳ Pending |

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

### Phase 7: Extended Components (Complete)

| Component | Files | Story |
|-----------|-------|-------|
| **Captain Components** | `captain/*.svelte`, `Captain.story.svelte` | ✅ |
| - AnimatingImg | AI avatar animation | ✅ |
| - AddNewRulesInput | Rule input field | ✅ |
| - InboxCard | Inbox selection | ✅ |
| - ResponseCard | Canned response display | ✅ |
| - RuleCard | Automation rule | ✅ |
| - ScenariosCard | AI scenarios | ✅ |
| - SuggestedRules | AI suggestions | ✅ |
| - ToolsDropdown | Captain tools | ✅ |
| - SettingsHeader | Captain settings | ✅ |
| **CopilotLoader** | `copilot-loader/*.svelte`, `CopilotLoader.story.svelte` | ✅ |
| **CopilotThinkingGroup** | AI thinking steps | ✅ |
| **Message Templates** | `message-template/*.svelte`, `MessageTemplate.story.svelte` | ✅ |
| - Text Template | Plain text messages | ✅ |
| - Media Template | Image/video/audio/document | ✅ |
| - Card Template | Product cards | ✅ |
| - CallToAction | CTA buttons | ✅ |
| - ListPicker | Selection lists | ✅ |
| - QuickReply | Quick reply buttons | ✅ |
| **ContactForm** | `contact-form/*.svelte`, `ContactForm.story.svelte` | ✅ |
| **ContactMergeForm** | Merge duplicate contacts | ✅ |
| **ContactHeader** | `contact-header/*.svelte`, `ContactHeader.story.svelte` | ✅ |
| **ContactNoteItem** | `contact-note/*.svelte`, `ContactNote.story.svelte` | ✅ |
| **EmptyState** | `empty-state/*.svelte`, `EmptyState.story.svelte` | ✅ |
| - ContactEmptyState | No contacts view | ✅ |
| - ArticleEmptyState | No articles view | ✅ |
| - PortalEmptyState | No portal view | ✅ |
| **AssignmentPolicy** | `assignment-policy/*.svelte`, `AssignmentPolicy.story.svelte` | ✅ |
| - AssignmentCard | Assignment toggle | ✅ |
| - AssignmentPolicyCard | Policy display | ✅ |
| - AgentCapacityCard | Agent capacity | ✅ |
| - RadioCard | Radio selection | ✅ |
| - DataTable | Data grid | ✅ |
| **PortalSwitcher** | `portal-switcher/*.svelte`, `PortalSwitcher.story.svelte` | ✅ |
| **NewConversationForm** | `new-conversation/*.svelte`, `NewConversation.story.svelte` | ✅ |
| **AvailabilityText** | `availability/*.svelte`, `Availability.story.svelte` | ✅ |
| **SidebarActionsHeader** | `sidebar-actions/*.svelte`, `SidebarActionsHeader.story.svelte` | ✅ |
| **SelectMenu** | `select-menu/*.svelte`, `SelectMenu.story.svelte` | ✅ |

---

**Created:** December 2024  
**Status:** All Phases Complete - 90+ Components with Stories

## Component Summary

| Category | Count |
|----------|-------|
| Primitives | 11 |
| Overlays & Navigation | 13 |
| Chatwoot-Specific | 4 |
| Navigation & Core | 11 |
| Business Components | 10 |
| Additional UI | 4 |
| Captain/AI | 12 |
| Message Templates | 6 |
| Contacts | 5 |
| Empty States | 4 |
| Assignment Policy | 5 |
| Help Center Extended | 2 |
| Other | 4 |
| **Total** | **91** |

## Vue to Svelte Migration Coverage

| Vue Story Category | Count | Svelte Equivalent | Status |
|-------------------|-------|-------------------|--------|
| Core UI Components | 21 | Primitives, Forms, Navigation | ✅ Complete |
| Filter Components | 5 | Filter, MultiSelect | ✅ Complete |
| Message Components | 12 | MessageBubble, Templates | ✅ Complete |
| Captain Components | 12 | Captain/* | ✅ Complete |
| Copilot Components | 3 | Copilot, CopilotLoader | ✅ Complete |
| Contacts Components | 6 | Contact*, EmptyState | ✅ Complete |
| Conversation Components | 2 | ConversationCard, NewConversation | ✅ Complete |
| Help Center Components | 10 | ArticleCard, CategoryCard, Portal* | ✅ Complete |
| Assignment Policy | 11 | AssignmentPolicy/* | ✅ Complete |
| Label Components | 2 | LabelInput | ✅ Complete |
| Custom Attributes | 1 | CustomAttributes | ✅ Complete |
| Feature Spotlight | 2 | FeatureSpotlight | ✅ Complete |
| Changelog | 2 | ChangelogCard | ✅ Complete |
| Widget Components | 1 | AvailabilityText | ✅ Complete |
| **Total** | **90** | **91 Svelte Components** | ✅ **Fully Migrated** |
