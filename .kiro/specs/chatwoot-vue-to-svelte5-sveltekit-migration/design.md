# Architecture and Design: Chatwoot Vue to Svelte 5 SvelteKit Migration

## Overview

This document outlines the architecture, design patterns, and technical decisions for migrating Chatwoot from Vue.js to Svelte 5 SvelteKit. The goal is 100% UI/UX parity while leveraging modern Svelte 5 features for improved performance and developer experience.

## Technology Stack

### Core Framework
- **SvelteKit 2.x**: Full-stack framework with SPA adapter
- **Svelte 5**: Latest version with runes reactive system
- **TypeScript**: Strict mode for type safety
- **Vite 6**: Build tool with HMR

### UI Components
- **shadcn-svelte**: Accessible component primitives (69/69 complete)
- **Tailwind CSS 3.4**: Utility-first styling
- **TipTap (Svelte)**: Rich text editor
- **layerchart**: SVG-based charts for analytics

### State Management
- **Svelte 5 Runes**: Native reactive primitives ($state, $derived, $effect)
- **Context API**: For component tree state sharing
- **LocalStorage**: For persistence where needed

### Data Fetching
- **ky**: Modern HTTP client (replacement for axios)
- **Native WebSocket**: For real-time communication (replacement for ActionCable)

### Internationalization
- **svelte-i18n**: Multi-language support
- **date-fns**: Date/time formatting

### Testing
- **Vitest**: Unit and integration testing
- **@testing-library/svelte**: Component testing
- **Playwright**: E2E testing

### Build and Deployment
- **@sveltejs/adapter-static**: SPA build output
- **pnpm**: Package manager
- **Vite**: Bundle optimization

## Architecture Patterns

### 1. File-Based Routing (SvelteKit)

SvelteKit uses file-based routing that replaces Vue Router:

```
src/routes/
├── (auth)/          # Authentication layout group
│   ├── login/
│   └── signup/
├── (app)/           # Authenticated app layout group
│   └── accounts/
│       └── [accountId]/
│           ├── conversations/
│           ├── contacts/
│           └── settings/
└── +layout.svelte   # Root layout
```

**Route Guards**: Implemented via `+layout.server.ts` or `src/hooks.server.ts`
**Loading States**: Using `+page.ts` load functions
**Error Handling**: Using `+error.svelte` pages

### 2. State Management with Svelte 5 Runes

Replace Vuex/Pinia with Svelte stores using runes:

**Pattern: Domain Store**
```typescript
// src/lib/stores/conversations.svelte.ts
export function createConversationsStore() {
  let conversations = $state<Conversation[]>([]);
  let selectedId = $state<number | null>(null);
  let loading = $state(false);
  
  // Derived state (replaces Vuex getters)
  const selected = $derived(
    conversations.find(c => c.id === selectedId)
  );
  
  const filtered = $derived(
    conversations.filter(c => /* filter logic */)
  );
  
  // Actions (replaces Vuex actions)
  async function fetchConversations() {
    loading = true;
    try {
      const data = await api.conversations.list();
      conversations = data;
    } finally {
      loading = false;
    }
  }
  
  function selectConversation(id: number) {
    selectedId = id;
  }
  
  // Real-time updates (replaces Vuex mutations from WebSocket)
  $effect(() => {
    const unsubscribe = websocket.subscribe('conversations', (event) => {
      if (event.type === 'conversation_created') {
        conversations = [...conversations, event.data];
      }
    });
    return unsubscribe;
  });
  
  return {
    get conversations() { return conversations; },
    get selected() { return selected; },
    get filtered() { return filtered; },
    get loading() { return loading; },
    fetchConversations,
    selectConversation
  };
}

// Usage in component
import { conversationsStore } from '$lib/stores';
const { conversations, selected } = conversationsStore;
```

### 3. API Client Architecture

Replace axios with ky for better TypeScript support:

```typescript
// src/lib/api/client.ts
import ky from 'ky';
import { transformKeys } from './transformers';
import { authStore } from '$lib/stores/auth.svelte';

const api = ky.create({
  prefixUrl: import.meta.env.VITE_API_BASE_URL,
  timeout: 30000,
  hooks: {
    beforeRequest: [
      (request) => {
        // Add auth header
        const token = authStore.token;
        if (token) {
          request.headers.set('Authorization', `Bearer ${token}`);
        }
        // Transform to snake_case
        if (request.body) {
          const data = transformKeys(request.body, 'snake');
          request.body = JSON.stringify(data);
        }
      }
    ],
    afterResponse: [
      async (_request, _options, response) => {
        // Transform to camelCase
        if (response.ok) {
          const data = await response.json();
          return new Response(
            JSON.stringify(transformKeys(data, 'camel')),
            response
          );
        }
      }
    ],
    beforeError: [
      (error) => {
        // Handle common errors
        if (error.response?.status === 401) {
          authStore.logout();
          goto('/login');
        }
        return error;
      }
    ]
  }
});

export default api;
```

### 4. WebSocket Client Architecture

Replace Rails ActionCable with native WebSocket:

```typescript
// src/lib/websocket/client.ts
export class WebSocketClient {
  private ws: WebSocket | null = null;
  private channels = new Map<string, Set<EventCallback>>();
  private reconnectAttempts = 0;
  
  connect(token: string) {
    const url = `${import.meta.env.VITE_WS_URL}?token=${token}`;
    this.ws = new WebSocket(url);
    
    this.ws.onopen = () => {
      this.reconnectAttempts = 0;
      this.subscribeToChannels();
    };
    
    this.ws.onmessage = (event) => {
      const { channel, type, data } = JSON.parse(event.data);
      this.dispatch(channel, { type, data });
    };
    
    this.ws.onclose = () => {
      this.reconnect();
    };
  }
  
  subscribe(channel: string, callback: EventCallback) {
    if (!this.channels.has(channel)) {
      this.channels.set(channel, new Set());
      this.send({ command: 'subscribe', identifier: channel });
    }
    this.channels.get(channel)!.add(callback);
    
    return () => {
      const listeners = this.channels.get(channel);
      listeners?.delete(callback);
      if (listeners?.size === 0) {
        this.send({ command: 'unsubscribe', identifier: channel });
        this.channels.delete(channel);
      }
    };
  }
  
  private reconnect() {
    const delay = Math.min(1000 * 2 ** this.reconnectAttempts, 30000);
    setTimeout(() => {
      this.reconnectAttempts++;
      this.connect(/* get token */);
    }, delay);
  }
}
```

### 5. Component Architecture

**Primitive Components** (already complete):
- Button, Input, Card, Badge, Avatar, etc.
- Located in `src/lib/components/ui/`
- Based on shadcn-svelte patterns

**Application Components**:
- Conversation list, message composer, contact sidebar, etc.
- Located in `src/lib/components/`
- Composed from primitive components

**Page Components**:
- Full page layouts
- Located in `src/routes/`
- Use application components

**Example Component Structure**:
```svelte
<!-- src/lib/components/conversations/ConversationListItem.svelte -->
<script lang="ts">
  import { Avatar, Badge } from '$lib/components/ui';
  import type { Conversation } from '$lib/types';
  
  interface Props {
    conversation: Conversation;
    selected?: boolean;
    onclick?: () => void;
  }
  
  let { conversation, selected = false, onclick }: Props = $props();
  
  // Derived state
  const unreadCount = $derived(conversation.unreadCount);
  const lastMessage = $derived(conversation.lastMessage);
</script>

<button
  class="conversation-item"
  class:selected
  onclick={onclick}
>
  <Avatar src={conversation.contact.avatar} />
  <div class="content">
    <div class="header">
      <span class="name">{conversation.contact.name}</span>
      {#if unreadCount > 0}
        <Badge variant="default">{unreadCount}</Badge>
      {/if}
    </div>
    <div class="preview">{lastMessage?.content}</div>
  </div>
</button>
```

### 6. Form Handling

Use Formsnap + Zod for form validation:

```typescript
import { superForm } from 'sveltekit-superforms';
import { zod } from 'sveltekit-superforms/adapters';
import { z } from 'zod';

const schema = z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email'),
});

const { form, errors, enhance } = superForm(data.form, {
  validators: zod(schema),
  onUpdate({ form }) {
    if (form.valid) {
      // Submit to API
    }
  }
});
```

### 7. Real-Time Updates Pattern

Integrate WebSocket updates into stores:

```typescript
// In store
$effect(() => {
  const unsubscribe = websocket.subscribe('conversations', (event) => {
    switch (event.type) {
      case 'message.created':
        // Update messages for conversation
        updateMessages(event.conversationId, event.data);
        break;
      case 'conversation.status_changed':
        // Update conversation status
        updateConversation(event.data);
        break;
      case 'presence.update':
        // Update agent presence
        updateAgentStatus(event.data);
        break;
    }
  });
  
  return () => unsubscribe();
});
```

## Key Design Decisions

### 1. SPA vs SSR

**Decision**: Use SPA mode with `@sveltejs/adapter-static`

**Rationale**:
- Current Vue app is SPA
- No need for SSR (authenticated app behind login)
- Simpler deployment (static files on CDN)
- Better match for WebSocket-heavy application

### 2. State Management

**Decision**: Use Svelte 5 runes instead of external store library

**Rationale**:
- Native to Svelte 5, no external dependency
- Simpler mental model than Vuex
- Better TypeScript support
- Automatic cleanup with $effect
- Matches Svelte philosophy of compile-time optimization

### 3. Styling Approach

**Decision**: Continue using Tailwind CSS with shadcn-svelte

**Rationale**:
- Already established in primitive components
- Excellent developer experience
- Consistent with Vue app design system
- Easy to maintain design tokens

### 4. TypeScript Strict Mode

**Decision**: Use strict TypeScript throughout

**Rationale**:
- Catch errors at compile time
- Better IDE support
- Self-documenting code
- Easier refactoring

### 5. Component Documentation

**Decision**: Use Histoire for component documentation

**Rationale**:
- Svelte-native (unlike Storybook)
- Faster build times
- Interactive component playground
- Already set up for primitive components

### 6. Testing Strategy

**Decision**: Vitest for unit/integration, Playwright for E2E

**Rationale**:
- Vitest is Vite-native, fast
- Better TypeScript support than Jest
- Playwright supports all browsers
- Active community and good docs

### 7. Bundle Size Optimization

**Decision**: Aggressive code splitting and tree shaking

**Targets**:
- Initial bundle: <500KB gzipped
- Route-based code splitting
- Lazy load heavy dependencies (TipTap, chart library)
- Virtual scrolling for large lists

**Techniques**:
- Dynamic imports for routes
- Lazy component loading
- Virtual scrolling (svelte-virtual)
- Tree shaking (automatic with Vite)
- Minimize dependencies

### 8. Accessibility

**Decision**: WCAG 2.1 AA compliance required

**Implementation**:
- Use shadcn-svelte (accessible by default)
- Add ARIA labels where needed
- Keyboard navigation for all features
- Screen reader testing in QA
- Automated testing with axe

## Migration Strategy

### Phase-by-Phase Approach

1. **Foundation**: Set up project structure, API client, state management patterns
2. **Core Stores**: Migrate Vuex stores to Svelte stores
3. **Core UI**: Build main layout and conversation components
4. **Features**: Migrate dashboard pages one by one
5. **Other Apps**: Migrate widget, portal, survey, superadmin
6. **Polish**: Performance, accessibility, animations
7. **Testing**: Comprehensive testing at all levels
8. **Deploy**: Production deployment with monitoring

### Parallel Running (if needed)

- Feature flags for gradual rollout
- Run Vue and Svelte in parallel during transition
- A/B testing for performance comparison
- Gradual user migration

### Rollback Plan

- Keep Vue codebase intact during migration
- Feature flags to switch between implementations
- Database schema unchanged (no migration needed)
- API remains stable (Laravel backend)

## Performance Targets

- **Initial Load**: <2s on 3G
- **Time to Interactive**: <3s
- **Route Navigation**: <200ms
- **Bundle Size**: <500KB gzipped
- **Lighthouse Score**: >90
- **Memory Usage**: <150MB per session

## Security Considerations

- JWT token storage in memory (not localStorage for sensitive data)
- Content Security Policy (CSP)
- XSS prevention (DOMPurify for user content)
- CSRF protection (API tokens)
- Secure WebSocket connections (wss://)
- Input validation and sanitization

## Monitoring and Observability

- **Error Tracking**: Sentry integration
- **Analytics**: PostHog for user behavior
- **Performance**: Web Vitals tracking
- **Bundle Size**: CI/CD monitoring
- **API Metrics**: Request/response times

## Success Metrics

- **Functional**: 100% feature parity with Vue app
- **Performance**: 30% bundle size reduction
- **Accessibility**: WCAG 2.1 AA compliant
- **Test Coverage**: >80% coverage
- **Developer Experience**: Faster build times, better TypeScript support

## Conclusion

This architecture provides a solid foundation for migrating from Vue to Svelte 5 while maintaining feature parity and improving performance. The use of modern Svelte 5 runes simplifies state management, and the component-based architecture ensures maintainability and scalability.
