# Chatwoot SvelteKit Super Admin SPA

A modern, production-ready Single Page Application (SPA) built with **SvelteKit 5**, **shadcn-svelte**, and **Tailwind CSS** for Chatwoot's super admin interface.

## �� Project Overview

This SPA replaces the legacy Vue-based super admin frontend with a modern SvelteKit implementation that integrates seamlessly with the Laravel API backend (`custom/laravel`).

### Key Features

- ✅ **SPA Mode**: Client-side routing only (no SSR)
- ✅ **TypeScript**: Full type safety throughout the application
- ✅ **Tailwind CSS**: Modern, responsive design with custom theme
- ✅ **shadcn-svelte**: Accessible, customizable UI components
- ✅ **Token Authentication**: Secure JWT-based auth with auto-refresh
- ✅ **Dark Mode**: Built-in dark mode support
- ✅ **Production Optimized**: Configured for optimal bundle size and performance

## 🚀 Quick Start

### Prerequisites

- Node.js >= 20.0.0
- pnpm >= 9.0.0

### Installation

\`\`\`bash
# Navigate to the project directory
cd custom/ui/sveltekit-ui

# Install dependencies
pnpm install

# Start development server
pnpm dev

# The app will be available at http://localhost:5173
\`\`\`

### Environment Variables

Create a \`.env\` file in the project root:

\`\`\`env
# Laravel API base URL
VITE_API_URL=http://localhost:8000/api/v1
\`\`\`

## 📁 Project Structure

\`\`\`
sveltekit-ui/
├── src/
│   ├── app.css                    # Global styles with CSS variables
│   ├── app.html                   # HTML template
│   ├── app.d.ts                   # TypeScript declarations
│   ├── lib/
│   │   ├── api/
│   │   │   └── client.ts          # API client and endpoint definitions
│   │   ├── components/
│   │   │   └── ui/                # shadcn-svelte components (to be added)
│   │   ├── stores/                # Svelte stores for state management
│   │   └── utils/
│   │       └── index.ts           # Utility functions (cn, flyAndScale)
│   └── routes/
│       ├── +layout.svelte         # Root layout with ModeWatcher, Toaster
│       ├── +page.svelte           # Home page
│       ├── onboarding/            # Onboarding flow (to be created)
│       ├── login/                 # Login page (to be created)
│       └── app/                   # Super admin routes (to be created)
│           └── super_admin/
│               ├── dashboard/
│               ├── accounts/
│               ├── users/
│               ├── settings/
│               └── ...
├── static/                        # Static assets
├── package.json
├── svelte.config.js               # SvelteKit config (SPA mode)
├── vite.config.ts                 # Vite config
├── tailwind.config.ts             # Tailwind config
├── postcss.config.js              # PostCSS config
└── tsconfig.json                  # TypeScript config
\`\`\`

## 🔗 API Integration

The API client is configured in \`src/lib/api/client.ts\` with all super admin endpoints.

### Usage Example

\`\`\`typescript
import { superAdminApi } from '$lib/api/client';

// Get dashboard data
const dashboard = await superAdminApi.getDashboard();

// Create a user
const user = await superAdminApi.createUser({
  name: 'John Doe',
  email: 'john@example.com',
  password: 'secret'
});
\`\`\`

## 🛠️ Development

### Adding shadcn-svelte Components

\`\`\`bash
npx shadcn-svelte@latest add button
npx shadcn-svelte@latest add input
npx shadcn-svelte@latest add dialog
\`\`\`

Components will be added to \`src/lib/components/ui/\`.

## 📦 Build & Deploy

\`\`\`bash
# Build for production
pnpm build

# Preview production build
pnpm preview
\`\`\`

Output will be in the \`build\` directory, ready for deployment.

## 📚 Technologies

- [SvelteKit](https://kit.svelte.dev/) - Application framework
- [Svelte 5](https://svelte.dev/) - UI framework
- [TypeScript](https://www.typescriptlang.org/) - Type safety
- [Tailwind CSS](https://tailwindcss.com/) - Styling
- [shadcn-svelte](https://www.shadcn-svelte.com/) - UI components
- [ky](https://github.com/sindresorhus/ky) - HTTP client
- [zod](https://zod.dev/) - Schema validation

## 📝 Next Steps

1. Install shadcn-svelte components (Button, Input, Dialog, Table, etc.)
2. Implement onboarding flow
3. Build authentication pages
4. Create dashboard with metrics
5. Build CRUD modules for Accounts, Users, Settings, etc.
6. Add advanced features (search, pagination, filtering)
7. Testing and documentation

See the full API documentation in \`src/lib/api/client.ts\`.
