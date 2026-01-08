# SvelteKit Routing Guide

This directory contains routing utilities for the Chatwoot Svelte application, replacing Vue Router functionality with SvelteKit's file-based routing system.

## Directory Structure

```
src/lib/routing/
├── guards.ts       # Authentication and authorization guards
├── navigation.ts   # Navigation helpers and URL builders
├── params.ts       # Parameter extraction and validation
├── types.ts        # TypeScript types
└── ROUTING.md      # This file
```

## File-Based Routing

SvelteKit uses file-based routing. Here's the planned structure:

```
src/routes/
├── (auth)/                          # Auth layout group (no sidebar)
│   ├── login/+page.svelte
│   ├── signup/+page.svelte
│   ├── reset-password/+page.svelte
│   └── +layout.svelte
│
├── (app)/                           # App layout group (with sidebar)
│   ├── accounts/
│   │   └── [accountId]/
│   │       ├── dashboard/+page.svelte
│   │       ├── conversations/
│   │       │   ├── +page.svelte
│   │       │   └── [conversationId]/+page.svelte
│   │       ├── contacts/
│   │       │   ├── +page.svelte
│   │       │   └── [contactId]/+page.svelte
│   │       ├── settings/
│   │       │   ├── +layout.svelte
│   │       │   ├── general/+page.svelte
│   │       │   ├── inboxes/+page.svelte
│   │       │   └── teams/+page.svelte
│   │       └── reports/+page.svelte
│   ├── +layout.svelte               # App layout
│   └── +layout.ts                   # Auth guard
│
├── +layout.svelte                   # Root layout
└── +page.svelte                     # Home (redirects)
```

## Route Guards

### Authentication Guards

```typescript
import { createAuthGuard } from '$lib/routing/guards';

// In +page.ts or +layout.ts
export const load = createAuthGuard();
```

### Guest Guards (login/signup pages)

```typescript
import { createGuestGuard } from '$lib/routing/guards';

// Redirect to dashboard if already authenticated
export const load = createGuestGuard('/accounts/1/dashboard');
```

### Role-Based Guards

```typescript
import { createAuthGuard } from '$lib/routing/guards';

// Require specific role
export const load = createAuthGuard({
  requireRole: 'administrator'
});

// Require any of these roles
export const load = createAuthGuard({
  requireAnyRole: ['administrator', 'agent']
});
```

### Manual Guards

```typescript
import { requireAuth, requireRole } from '$lib/routing/guards';

export async function load() {
  requireAuth();
  requireRole('administrator');
  
  // Your load logic
  return { /* data */ };
}
```

## Navigation

### Basic Navigation

```typescript
import { navigate } from '$lib/routing/navigation';

// Simple navigation
await navigate('/accounts/1/conversations');

// With query parameters
await navigate('/contacts', {
  query: {
    page: 2,
    status: 'active'
  }
});

// Replace state (no history entry)
await navigate('/settings', {
  replaceState: true
});

// Preserve existing query params
await navigate('/contacts', {
  query: { page: 2 },
  preserveQuery: true
});
```

### URL Builders

```typescript
import { 
  frontendURL, 
  conversationURL, 
  contactURL, 
  settingsURL 
} from '$lib/routing/navigation';

// Build URLs with account context
const url1 = frontendURL('/conversations', 1);
// => '/accounts/1/conversations'

const url2 = conversationURL(1, 123);
// => '/accounts/1/conversations/123'

const url3 = contactURL(1, 456);
// => '/accounts/1/contacts/456'

const url4 = settingsURL(1, 'inboxes');
// => '/accounts/1/settings/inboxes'
```

### History Navigation

```typescript
import { goBack, goForward, navigationHistory } from '$lib/routing/navigation';

// Navigate back
goBack();

// Navigate forward
goForward();

// Check if can go back
if (navigationHistory.canGoBack()) {
  goBack();
}

// Go to specific position
navigationHistory.go(-2); // Go back 2 pages
```

### Route State Checking

```typescript
import { isCurrentRoute, isRouteActive } from '$lib/routing/navigation';

// Check exact match
if (isCurrentRoute('/accounts/1/conversations')) {
  // Current route is exactly this
}

// Check if route starts with path
if (isRouteActive('/accounts/1')) {
  // Current route is under /accounts/1
}
```

## Route Parameters

### Extract Parameters

```typescript
import { getParam, getNumericParam, requireParam } from '$lib/routing/params';

export async function load({ params }) {
  // Optional string parameter
  const id = getParam(params, 'id');
  
  // Optional numeric parameter
  const accountId = getNumericParam(params, 'accountId');
  
  // Required parameter (throws if missing)
  const conversationId = requireParam(params, 'conversationId');
  
  // Required numeric parameter
  const contactId = requireNumericParam(params, 'contactId');
  
  return { id, accountId, conversationId, contactId };
}
```

### Type-Safe Parameter Extraction

```typescript
import { extractRouteParams } from '$lib/routing/params';

export async function load({ params }) {
  const { accountId, conversationId } = extractRouteParams(params, {
    accountId: 'number',
    conversationId: 'number'
  });
  
  // accountId and conversationId are typed as numbers
  return { accountId, conversationId };
}
```

### Query Parameters

```typescript
import { 
  getQueryParam, 
  getQueryParamAsNumber, 
  getQueryParamAsBoolean,
  getPaginationParams,
  getFilterParams
} from '$lib/routing/params';

export async function load({ url }) {
  const searchParams = url.searchParams;
  
  // Get individual params
  const search = getQueryParam(searchParams, 'search');
  const page = getQueryParamAsNumber(searchParams, 'page');
  const active = getQueryParamAsBoolean(searchParams, 'active');
  
  // Get pagination params
  const { page, perPage } = getPaginationParams(searchParams);
  
  // Get filter params
  const filters = getFilterParams(searchParams);
  
  return { search, page, active, filters };
}
```

### Update Query Parameters

```typescript
import { updateQueryParam, updateQueryParams } from '$lib/routing/params';
import { navigate } from '$lib/routing/navigation';

// Update single parameter
function updatePage(page: number) {
  const newParams = updateQueryParam(searchParams, 'page', page);
  navigate(`?${newParams.toString()}`);
}

// Update multiple parameters
function updateFilters(updates: Record<string, any>) {
  const newParams = updateQueryParams(searchParams, updates);
  navigate(`?${newParams.toString()}`);
}
```

## Component Usage

### In Svelte Components

```svelte
<script lang="ts">
  import { navigate, frontendURL } from '$lib/routing/navigation';
  import { page } from '$app/stores';
  
  // Access current route
  $: currentPath = $page.url.pathname;
  $: accountId = $page.params.accountId;
  
  // Navigate programmatically
  async function goToConversations() {
    await navigate(frontendURL('/conversations', accountId));
  }
  
  // Handle form submission with navigation
  async function handleSubmit() {
    // ... submit logic
    await navigate('/success');
  }
</script>

<button onclick={goToConversations}>
  Go to Conversations
</button>
```

### With Links

```svelte
<script lang="ts">
  import { conversationURL } from '$lib/routing/navigation';
  import { page } from '$app/stores';
  
  const accountId = $page.params.accountId;
  const conversations = [/* ... */];
</script>

{#each conversations as conversation}
  <a href={conversationURL(accountId, conversation.id)}>
    {conversation.title}
  </a>
{/each}
```

## Migration from Vue Router

### Vue Router → SvelteKit

| Vue Router | SvelteKit |
|-----------|-----------|
| `router.push('/path')` | `navigate('/path')` |
| `router.replace('/path')` | `navigate('/path', { replaceState: true })` |
| `router.back()` | `goBack()` |
| `router.forward()` | `goForward()` |
| `$route.params.id` | `$page.params.id` |
| `$route.query.search` | `$page.url.searchParams.get('search')` |
| `$route.path` | `$page.url.pathname` |
| `beforeEach` guard | `createAuthGuard()` in +layout.ts |
| `<router-link>` | `<a href={...}>` |

### Vue Router Guards → SvelteKit Load Functions

```javascript
// Vue Router
router.beforeEach((to, from, next) => {
  if (!isAuthenticated()) {
    next('/login');
  } else {
    next();
  }
});

// SvelteKit
// In +layout.ts
export const load = createAuthGuard();
```

### Vue Dynamic Routes → SvelteKit Parameters

```javascript
// Vue Router
{
  path: '/conversations/:id',
  component: Conversation
}

// SvelteKit
// File: routes/conversations/[id]/+page.svelte
// Access: $page.params.id
```

## Best Practices

### 1. Use Layout Groups

Group related routes under layout groups:

```
(auth)/         # Auth pages without sidebar
(app)/          # App pages with sidebar
```

### 2. Centralize URL Building

Always use URL builder functions instead of hardcoding:

```typescript
// ✅ Good
const url = conversationURL(accountId, conversationId);

// ❌ Bad
const url = `/accounts/${accountId}/conversations/${conversationId}`;
```

### 3. Type-Safe Parameters

Use type-safe parameter extraction:

```typescript
// ✅ Good
const { accountId } = extractRouteParams(params, {
  accountId: 'number'
});

// ❌ Bad
const accountId = parseInt(params.accountId);
```

### 4. Handle Navigation Errors

```typescript
try {
  await navigate('/path');
} catch (err) {
  // Handle navigation cancellation
  console.error('Navigation failed:', err);
}
```

### 5. Preserve Query Parameters When Needed

```typescript
// Preserve existing filters when changing page
await navigate('/contacts', {
  query: { page: 2 },
  preserveQuery: true
});
```

## Testing Routes

```typescript
import { describe, it, expect } from 'vitest';
import { frontendURL, conversationURL } from './navigation';

describe('URL Builders', () => {
  it('builds conversation URL', () => {
    const url = conversationURL(1, 123);
    expect(url).toBe('/accounts/1/conversations/123');
  });
  
  it('builds frontend URL with account', () => {
    const url = frontendURL('/settings', 1);
    expect(url).toBe('/accounts/1/settings');
  });
});
```

## See Also

- [SvelteKit Routing Documentation](https://kit.svelte.dev/docs/routing)
- [SvelteKit Load Functions](https://kit.svelte.dev/docs/load)
- Vue Router implementation in `chatwoot/app/javascript/dashboard/routes/`
