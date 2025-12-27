# Chatwoot Svelte UI

A modern UI component library built with **SvelteKit**, **shadcn-svelte**, **Tailwind CSS**, and **Histoire**.

## 📦 Overview

This is a SvelteKit SPA project designed to work as the frontend for Chatwoot's Laravel API backend. All components are built using shadcn-svelte patterns with complete Histoire stories for documentation.

## 🚀 Quick Start

```bash
# Navigate to the svelte-ui directory
cd custom/ui/svelte-ui

# Install dependencies
pnpm install

# Start development server
pnpm dev

# Start Histoire story viewer
pnpm story:dev
```

## 📁 Project Structure

```
svelte-ui/
├── src/
│   ├── app.css              # Global styles with CSS variables
│   ├── app.html             # HTML template
│   ├── app.d.ts             # TypeScript declarations
│   ├── histoire.setup.ts    # Histoire configuration
│   ├── lib/
│   │   ├── components/
│   │   │   └── ui/          # UI components
│   │   │       ├── button/
│   │   │       │   ├── index.ts
│   │   │       │   ├── button.svelte
│   │   │       │   └── Button.story.svelte
│   │   │       ├── input/
│   │   │       ├── badge/
│   │   │       ├── avatar/
│   │   │       ├── card/
│   │   │       ├── spinner/
│   │   │       ├── switch/
│   │   │       ├── checkbox/
│   │   │       ├── textarea/
│   │   │       ├── label/
│   │   │       └── separator/
│   │   ├── utils/
│   │   │   └── index.ts     # Utility functions (cn, etc.)
│   │   └── hooks/           # Custom hooks
│   └── routes/
│       ├── +layout.svelte   # Root layout
│       └── +page.svelte     # Home page
├── static/                  # Static assets
├── package.json
├── svelte.config.js         # SvelteKit config (SPA mode)
├── vite.config.ts           # Vite config
├── histoire.config.ts       # Histoire config
├── tailwind.config.ts       # Tailwind config
├── postcss.config.js
├── tsconfig.json
└── components.json          # shadcn-svelte config
```

## 🎨 Available Components

### Primitives
| Component | Description | Story |
|-----------|-------------|-------|
| **Button** | Primary interaction element with variants | ✅ |
| **Input** | Text input with various types | ✅ |
| **Textarea** | Multi-line text input | ✅ |
| **Checkbox** | Boolean selection with indeterminate state | ✅ |
| **Switch** | Toggle switch for on/off states | ✅ |
| **Label** | Form field labels | ✅ |
| **Separator** | Visual divider | ✅ |

### Data Display
| Component | Description | Story |
|-----------|-------------|-------|
| **Badge** | Status indicators and labels | ✅ |
| **Avatar** | User profile images with fallback | ✅ |
| **Card** | Container with header, content, footer | ✅ |
| **Spinner** | Loading indicator | ✅ |

## 🎭 Histoire Stories

Each component has a complete Histoire story showcasing:
- Default state
- All variants
- Different sizes
- Interactive states (disabled, loading)
- Real-world usage examples

```bash
# Run Histoire development server
pnpm story:dev

# Build Histoire static site
pnpm story:build

# Preview built stories
pnpm story:preview
```

## 🎨 Theming

The design system uses CSS variables for theming:

```css
/* Light mode */
--primary: 221.2 83.2% 53.3%;
--secondary: 210 40% 96.1%;
--destructive: 0 72.2% 50.6%;
--success: 142 76% 36%;
--warning: 38 92% 50%;
--info: 199 89% 48%;

/* Dark mode */
.dark {
  --primary: 217.2 91.2% 59.8%;
  /* ... */
}
```

### Custom Variants

The Button component includes additional variants beyond shadcn defaults:
- `success` - Green for positive actions
- `warning` - Yellow for cautionary actions  
- `info` - Blue for informational actions

## 🔧 Adding Components

To add a new shadcn-svelte component:

```bash
pnpx shadcn-svelte@latest add <component-name>
```

Or manually create in `src/lib/components/ui/`:

1. Create component directory
2. Add `index.ts` with exports and types
3. Add `<component>.svelte` with implementation
4. Add `<Component>.story.svelte` for Histoire

## 📱 SPA Mode Configuration

This project is configured as a Single Page Application for use with a Laravel API backend:

```javascript
// svelte.config.js
adapter: adapter({
  fallback: 'index.html', // SPA mode
  pages: 'build',
  assets: 'build'
}),
prerender: {
  entries: [] // No prerendering
}
```

## 🔗 API Integration

API calls should be made to the Laravel backend:

```typescript
import ky from 'ky';

const api = ky.create({
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

// Example usage
const conversations = await api.get('conversations').json();
```

## 📦 Dependencies

### Core
- `svelte` ^5.7.0
- `@sveltejs/kit` ^2.9.0
- `@sveltejs/adapter-static` ^3.0.0

### UI
- `bits-ui` ^1.0.0 (Headless primitives)
- `tailwindcss` ^3.4.17
- `tailwind-variants` ^0.3.0
- `tailwind-merge` ^2.6.0
- `clsx` ^2.1.0

### Features
- `mode-watcher` ^1.0.0 (Dark mode)
- `svelte-sonner` ^1.0.0 (Toasts)
- `@lucide/svelte` ^0.482.0 (Icons)
- `ky` ^1.7.0 (HTTP client)
- `svelte-i18n` ^4.0.0 (Internationalization)

### Development
- `histoire` ^0.17.17
- `@histoire/plugin-svelte` ^0.17.17
- `typescript` ^5.6.0

## 📝 Component Roadmap

### To Be Added
- [ ] Dialog
- [ ] Dropdown Menu
- [ ] Select
- [ ] Popover
- [ ] Tooltip
- [ ] Tabs
- [ ] Accordion
- [ ] Alert
- [ ] Toast (Sonner)
- [ ] Sidebar
- [ ] Table
- [ ] Command (cmdk)
- [ ] Calendar
- [ ] Date Picker
- [ ] Form validation

### Custom Components (Chatwoot-specific)
- [ ] Message Bubble
- [ ] Conversation Card
- [ ] Contact Card
- [ ] Reply Box
- [ ] File Upload
- [ ] Audio Recorder
- [ ] Emoji Picker

## 🏗️ Build

```bash
# Build for production
pnpm build

# The output will be in the `build` directory
# Deploy this to your static hosting or serve from Laravel
```

## 📚 References

- [shadcn-svelte Documentation](https://shadcn-svelte.com)
- [SvelteKit Documentation](https://kit.svelte.dev)
- [Svelte 5 Documentation](https://svelte.dev/docs)
- [Histoire Documentation](https://histoire.dev)
- [Bits UI Documentation](https://bits-ui.com)
- [Tailwind CSS Documentation](https://tailwindcss.com)
