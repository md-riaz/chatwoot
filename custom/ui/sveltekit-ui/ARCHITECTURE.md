# Architecture & Design Decisions

## Overview

This document outlines the key architectural decisions made for the Chatwoot SvelteKit Super Admin SPA.

## Technology Choices

### SvelteKit 5
- **Reason**: Latest version with improved performance, Svelte 5 runes, and better TypeScript support
- **SPA Mode**: Configured with `adapter-static` and `fallback: 'index.html'` for client-side routing only
- **No SSR**: Super admin interface doesn't benefit from SSR; SPA mode provides better performance

### TypeScript
- **Reason**: Type safety, better IDE support, reduced runtime errors
- **Configuration**: Strict mode enabled for maximum type safety
- **API Types**: All API responses should be properly typed (to be added incrementally)

### Tailwind CSS + shadcn-svelte
- **Reason**: 
  - Rapid development with utility-first CSS
  - Accessible, customizable components from shadcn-svelte
  - Consistent design system with CSS variables
  - Dark mode support out of the box
  - Smaller bundle size compared to full component libraries

### ky HTTP Client
- **Reason**:
  - Modern fetch wrapper with better error handling
  - Automatic JSON parsing
  - Request/response interceptors for auth
  - TypeScript-friendly
  - Smaller than axios

### Authentication Strategy

#### Token-Based Auth (JWT)
- **Storage**: localStorage for auth token
- **Injection**: Automatic via ky beforeRequest hook
- **Expiration**: Handled by Laravel backend
- **Refresh**: 401 responses trigger auto-redirect to login
- **Security**: HTTPS required in production

#### Flow:
1. User logs in → receives JWT token from Laravel
2. Token stored in localStorage
3. All API requests automatically include `Authorization: Bearer {token}` header
4. On 401 response, clear token and redirect to login

### State Management

#### Approach: Svelte Stores + Local Component State
- **Reason**: 
  - Svelte's built-in stores are lightweight and powerful
  - No need for complex state management (Redux, Vuex, etc.)
  - Keep state close to where it's used

#### Store Categories:
- `auth.ts` - Authentication state (user, token, permissions)
- `ui.ts` - UI state (sidebar open/closed, theme, notifications)
- `cache.ts` - Client-side data caching (optional)

### Routing Structure

```
/                           # Redirect based on auth state
/onboarding                 # First-time setup (if no super admin exists)
/login                      # Super admin login
/app/super_admin/           # Protected super admin routes
  ├── dashboard             # System overview
  ├── accounts              # Account management
  ├── users                 # User management
  ├── settings              # System settings
  ├── agent-bots            # Bot management
  ├── platform-apps         # App management
  ├── access-tokens         # Token management
  ├── installation-configs  # Config management
  ├── account-users         # Account user relationships
  ├── audit-logs            # Audit trail
  └── cache                 # Cache management
```

### API Client Design

#### Centralized Client (`src/lib/api/client.ts`)
- **Single Source of Truth**: All API endpoints defined in one place
- **Typed Responses**: Return types should be added incrementally
- **Error Handling**: Centralized error handling with hooks
- **Interceptors**: 
  - Request: Add auth token
  - Response: Handle 401, parse errors

#### Endpoint Organization:
- `superAdminApi` - All super admin operations
- `authApi` - Login, logout, current user
- `onboardingApi` - First-time setup

### Component Architecture

#### Structure:
```
src/lib/components/
├── ui/                    # shadcn-svelte components (Button, Input, etc.)
├── layout/                # Layout components (Sidebar, Header, Nav)
├── features/              # Feature-specific components
│   ├── accounts/         # Account-related components
│   ├── users/            # User-related components
│   └── ...
└── shared/               # Shared components (DataTable, SearchBar, etc.)
```

#### Principles:
- **Composition over Inheritance**: Use components as building blocks
- **Single Responsibility**: Each component has one clear purpose
- **Props vs Stores**: Pass data via props when possible, use stores for global state
- **TypeScript Props**: Always type component props

### Form Handling

#### Strategy: superforms + Zod
- **Validation**: Client-side with Zod schemas matching Laravel validation rules
- **Server Validation**: Laravel validates on backend, errors returned to form
- **Flow**:
  1. Define Zod schema
  2. Create form with superforms
  3. Handle submit → send to API
  4. Display Laravel validation errors if any

### Error Handling

#### Levels:
1. **API Client Level**: Catch network errors, log, show generic message
2. **Component Level**: Catch specific errors, show contextual messages
3. **Global Error Boundary**: Catch unhandled errors, show fallback UI

#### User Feedback:
- **Toast Notifications**: Use svelte-sonner for success/error messages
- **Inline Errors**: Show field-level validation errors
- **Error Pages**: 404, 500 pages for routing errors

### Performance Optimizations

#### Bundle Optimization:
- **Tree Shaking**: Enabled by default with Vite
- **Code Splitting**: Automatic route-based splitting by SvelteKit
- **Lazy Loading**: Import heavy components dynamically

#### Runtime Optimization:
- **Virtual Scrolling**: For long lists (users, accounts)
- **Pagination**: All list endpoints use pagination
- **Debouncing**: Search inputs debounced (300ms)
- **Caching**: Cache API responses client-side (5-15 minutes)

### Accessibility (a11y)

#### Requirements:
- **Keyboard Navigation**: All interactive elements keyboard accessible
- **ARIA Labels**: Proper ARIA labels on all components
- **Focus Management**: Logical focus order, visible focus indicators
- **Screen Reader**: Test with screen readers
- **Color Contrast**: WCAG AA compliance minimum

#### shadcn-svelte provides accessible components by default

### Testing Strategy

#### Test Types:
1. **Unit Tests**: Utilities, pure functions (Vitest)
2. **Component Tests**: Individual component logic (Vitest + Testing Library)
3. **Integration Tests**: Feature flows (Playwright)
4. **E2E Tests**: Critical user journeys (Playwright)

#### Priority:
- Authentication flow
- CRUD operations
- Form validation
- Error handling

### Security Considerations

#### Client-Side:
- **XSS Prevention**: Use Svelte's built-in escaping (no `{@html}` unless necessary)
- **CSRF**: Not needed for token-based auth
- **Sensitive Data**: Never log tokens or passwords
- **Dependencies**: Regular security audits (`pnpm audit`)

#### API Communication:
- **HTTPS Only**: In production
- **Token Expiration**: Short-lived tokens (1-4 hours)
- **No Sensitive Data in URL**: Use POST body for sensitive data
- **CORS**: Configured on Laravel backend

### Deployment Strategy

#### Build Process:
1. `pnpm build` - Creates static SPA in `build/` directory
2. All assets hashed for cache busting
3. Single `index.html` entry point

#### Deployment Options:
1. **Separate Hosting**: Deploy to CDN/static host, API on separate domain
2. **Laravel Serve**: Copy build files to Laravel `public/` directory
3. **Hybrid**: CDN for assets, Laravel serves HTML

#### Recommended: Separate Hosting
- **Pros**: Better caching, CDN benefits, independent scaling
- **Cons**: CORS configuration needed

### Development Workflow

#### Recommended Flow:
1. Run Laravel API locally (`php artisan serve`)
2. Run SvelteKit dev server (`pnpm dev`)
3. Set `VITE_API_URL` to Laravel API URL
4. Develop features iteratively
5. Test against real API responses

#### Hot Module Replacement (HMR):
- Enabled by default with Vite
- Fast refresh on code changes
- State preserved across refreshes

### Migration from Vue

#### Conversion Strategy:
1. **Start Fresh**: Don't port Vue code directly
2. **Feature Parity**: Match Vue functionality, not implementation
3. **Improve UX**: Use opportunity to enhance user experience
4. **Progressive**: Build incrementally, test thoroughly

#### Key Differences:
- **Reactivity**: Svelte 5 runes vs Vue 3 Composition API
- **Components**: Svelte single-file components (different syntax)
- **State**: Svelte stores vs Vue Pinia/Vuex
- **Routing**: SvelteKit file-based routing vs Vue Router

### Future Enhancements

#### Planned:
- **i18n**: Multi-language support with svelte-i18n
- **Real-time Updates**: WebSocket for live notifications
- **Offline Support**: Service worker for offline functionality
- **Progressive Web App**: Install as desktop/mobile app
- **Advanced Search**: Elasticsearch integration
- **Bulk Operations**: Multi-select and bulk actions
- **Export/Import**: CSV/JSON data export
- **Audit Trail**: Detailed change history

### Conventions

#### File Naming:
- Components: `PascalCase.svelte`
- Routes: `kebab-case/+page.svelte`
- Utilities: `camelCase.ts`
- Stores: `camelCase.ts`

#### Code Style:
- Use Prettier (configured)
- Use ESLint (to be configured)
- 2-space indentation
- Single quotes for strings
- Semicolons optional (Prettier will format)

#### TypeScript:
- Prefer `interface` over `type` for objects
- Use `unknown` instead of `any`
- Add return types to functions
- Avoid type assertions unless necessary

### Documentation Standards

#### Component Documentation:
- JSDoc comments for props
- Usage examples in comments
- Complex logic explained

#### API Documentation:
- Endpoint descriptions
- Request/response examples
- Error cases documented

---

**Last Updated**: 2026-01-01
**Version**: 1.0.0
**Status**: Foundation Complete, Features In Progress
