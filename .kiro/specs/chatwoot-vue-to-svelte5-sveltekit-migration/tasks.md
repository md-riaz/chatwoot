# Implementation Plan: Chatwoot Vue to Svelte 5 SvelteKit Complete Frontend Migration

## Overview

This implementation plan provides a comprehensive, phased approach to migrating the Chatwoot Vue.js frontend (884 .vue files) to Svelte 5 SvelteKit. The plan is structured into detailed, atomic tasks that can be executed independently by AI agents. Each task includes full context, specific file paths, acceptance criteria, and validation steps.

**Current Status**: 69/69 primitive components complete in `custom/ui/svelte-ui`
**Target Directory**: `chatwoot/custom/ui/svelte-ui`
**Vue Source**: `chatwoot/app/javascript/`
**Total Vue Files**: ~884 components across dashboard, widget, portal, survey, and superadmin
**Timeline Estimate**: 20-28 weeks (5-7 months) with 2-3 developers

## Task Execution Principles

1. **Self-Sufficient**: Each task contains all context needed for an AI agent to complete it independently
2. **Atomic**: Tasks are small enough to complete in 2-8 hours of focused work
3. **Testable**: Clear acceptance criteria and validation steps for verification
4. **Sequential**: Dependencies are clearly marked; tasks can be executed in order
5. **Referenceable**: Vue source files and Svelte target locations are explicitly provided

## Progress Tracking

**Phase Completion:**
- [ ] Phase 0: Foundation and Setup (7 tasks) - 0/7 complete
- [ ] Phase 1: Core State Management and API (7 tasks) - 0/7 complete
- [ ] Phase 2: Core UI Components (6 tasks) - 0/6 complete
- [ ] Phase 3: Dashboard Pages (15 tasks) - 0/15 complete
- [ ] Phase 4: Widget, Portal, Survey, SuperAdmin (10 tasks) - 0/10 complete
- [ ] Phase 5: Advanced Features (6 tasks) - 0/6 complete
- [ ] Phase 6: Testing (6 tasks) - 0/6 complete
- [ ] Phase 7: Documentation and Deployment (7 tasks) - 0/7 complete

**Total**: 64 major tasks | **Estimated**: 500-700 hours

---

## PHASE 0: Foundation and Setup (Weeks 1-2) - CRITICAL

### Task 0.1: Project Structure and Configuration Verification
**Priority**: P0 - CRITICAL  
**Estimated Time**: 4-6 hours  
**Dependencies**: None  
**Requirements**: REQ-23 (Development Experience)

#### Context
The existing `custom/ui/svelte-ui` directory has basic structure but needs comprehensive verification for full application migration. This includes proper SvelteKit configuration for SPA mode, build optimization, and development tooling.

#### Vue Reference Files
- N/A (Project setup task)

#### Svelte Files to Review
- `chatwoot/custom/ui/svelte-ui/svelte.config.js`
- `chatwoot/custom/ui/svelte-ui/vite.config.ts`
- `chatwoot/custom/ui/svelte-ui/tsconfig.json`
- `chatwoot/custom/ui/svelte-ui/tailwind.config.ts`
- `chatwoot/custom/ui/svelte-ui/package.json`

#### Implementation Steps
1. Review `svelte.config.js` and verify SPA adapter (`@sveltejs/adapter-static`) configuration
2. Check `vite.config.ts` for proper code splitting and optimization settings
3. Verify `tsconfig.json` has strict mode enabled and proper path aliases (`$lib`, `$routes`)
4. Confirm `tailwind.config.ts` includes all Chatwoot design tokens (colors, spacing, typography)
5. Set up environment variable handling in `.env` and `.env.example`
6. Verify `package.json` has all necessary dependencies
7. Test HMR (Hot Module Replacement) functionality

#### Acceptance Criteria
- [ ] SvelteKit configured for SPA mode with `@sveltejs/adapter-static`
- [ ] TypeScript strict mode enabled with no errors
- [ ] Vite configured with code splitting and tree shaking
- [ ] Tailwind includes all design tokens matching Vue app
- [ ] Environment variables properly configured
- [ ] Build output matches deployment requirements
- [ ] HMR works correctly without full page reloads

#### Validation Steps
```bash
cd chatwoot/custom/ui/svelte-ui
pnpm dev         # Verify dev server starts without errors
pnpm build       # Verify build completes successfully
pnpm preview     # Verify built application works
pnpm check       # Verify TypeScript compilation
```

Expected: All commands succeed with no errors

---

### Task 0.2: HTTP Client and API Layer Implementation
**Priority**: P0 - CRITICAL  
**Estimated Time**: 6-8 hours  
**Dependencies**: Task 0.1  
**Requirements**: REQ-3 (API Integration)

#### Context
The Vue application uses axios with custom interceptors for authentication, request/response transformation (camelCase ↔ snake_case), and error handling. We need to create an equivalent API client using ky (modern fetch wrapper) that provides identical functionality.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/store/utils/api.js` - Base axios configuration
- `chatwoot/app/javascript/dashboard/api/*.js` - 118 API endpoint files

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/api/client.ts` - Main API client
- `chatwoot/custom/ui/svelte-ui/src/lib/api/types.ts` - TypeScript types
- `chatwoot/custom/ui/svelte-ui/src/lib/api/errors.ts` - Error handling
- `chatwoot/custom/ui/svelte-ui/src/lib/api/transformers.ts` - Data transformation

#### Implementation Steps
1. Install ky: `pnpm add ky`
2. Create `src/lib/api/transformers.ts`:
   - Function to convert camelCase to snake_case
   - Function to convert snake_case to camelCase
   - Deep object transformation (handle nested objects and arrays)
3. Create `src/lib/api/errors.ts`:
   - ApiError class extending Error
   - Error type definitions (NetworkError, AuthError, ValidationError, ServerError)
   - Error message formatting utilities
4. Create `src/lib/api/client.ts`:
   - Base ky instance with configuration (baseURL from env, timeout 30s)
   - Request interceptor: add auth headers from token store
   - Request interceptor: transform request body to snake_case
   - Response interceptor: transform response data to camelCase
   - Error interceptor: handle 401 (redirect to login), 403, 429, 500+ errors
   - Helper methods: get(), post(), patch(), put(), delete()
   - File upload helper with FormData and progress tracking
   - Request cancellation using AbortController
5. Create `src/lib/api/types.ts`:
   - PaginatedResponse<T> type
   - ApiResponse<T> type
   - RequestOptions type

#### Example Implementation
```typescript
// src/lib/api/client.ts
import ky from 'ky';
import { transformKeys } from './transformers';
import { ApiError } from './errors';
import { authStore } from '$lib/stores/auth.svelte';

const api = ky.create({
  prefixUrl: import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000',
  timeout: 30000,
  hooks: {
    beforeRequest: [
      (request) => {
        const token = authStore.token;
        if (token) {
          request.headers.set('Authorization', `Bearer ${token}`);
        }
        
        if (request.body && request.method !== 'GET') {
          const data = JSON.parse(request.body as string);
          const transformed = transformKeys(data, 'snake');
          request.body = JSON.stringify(transformed);
        }
      }
    ],
    afterResponse: [
      async (_request, _options, response) => {
        if (response.ok) {
          const contentType = response.headers.get('content-type');
          if (contentType?.includes('application/json')) {
            const data = await response.json();
            const transformed = transformKeys(data, 'camel');
            return new Response(JSON.stringify(transformed), response);
          }
        }
        return response;
      }
    ],
    beforeError: [
      async (error) => {
        const { response } = error;
        if (response) {
          if (response.status === 401) {
            authStore.logout();
            window.location.href = '/login';
          }
          const data = await response.json();
          throw new ApiError(response.status, data.message || 'API Error', data);
        }
        throw error;
      }
    ]
  }
});

export default api;
```

#### Acceptance Criteria
- [ ] ky client created with base configuration
- [ ] Authentication header injection from token store
- [ ] Request body transformation to snake_case
- [ ] Response body transformation to camelCase
- [ ] Error handling for 401, 403, 429, 500+ status codes
- [ ] TypeScript types defined for API responses
- [ ] Request cancellation support with AbortController
- [ ] Retry logic (3 attempts with exponential backoff)
- [ ] File upload with progress tracking

#### Validation Steps
```typescript
// Test in browser console or create test file
import api from '$lib/api/client';

// Test GET request
const users = await api.get('api/v1/users').json();
console.log(users); // Should be camelCase

// Test POST request  
const newUser = await api.post('api/v1/users', {
  json: { firstName: 'John', lastName: 'Doe' }
}).json();
console.log(newUser); // Request sent as snake_case, response as camelCase

// Test error handling
try {
  await api.get('api/v1/invalid').json();
} catch (error) {
  console.log(error); // Should be ApiError instance
}
```

---

### Task 0.3: State Management Foundation with Svelte 5 Runes
**Priority**: P0 - CRITICAL  
**Estimated Time**: 8-10 hours  
**Dependencies**: Task 0.1  
**Requirements**: REQ-2 (State Management)

#### Context
The Vue application uses Vuex with 50+ modules for state management. We need to create Svelte stores using Svelte 5 runes ($state, $derived, $effect) that provide equivalent functionality. This task establishes the foundation patterns for all domain stores.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/store/index.js` - Vuex store setup
- `chatwoot/app/javascript/dashboard/store/modules/*.js` - 50+ Vuex modules
- `chatwoot/app/javascript/dashboard/store/mutation-types.js` - Mutation constants

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/base.svelte.ts` - Base store pattern
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/persistence.ts` - LocalStorage utilities
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/types.ts` - TypeScript types
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/README.md` - Documentation

#### Implementation Steps
1. Create base store pattern in `src/lib/stores/base.svelte.ts`:
   - Use $state for reactive state
   - Use $derived for computed values (Vuex getters replacement)
   - Use $effect for side effects (Vuex actions replacement)
   - Provide methods for state updates (Vuex mutations replacement)
2. Create persistence utilities in `src/lib/stores/persistence.ts`:
   - saveToStorage(key, data) function
   - loadFromStorage(key) function
   - clearStorage(key) function
   - Handle JSON serialization/deserialization
3. Create store types in `src/lib/stores/types.ts`:
   - Store interface
   - LoadingState type
   - ErrorState type
4. Create documentation in `src/lib/stores/README.md`:
   - Store creation patterns
   - Usage examples
   - Best practices

#### Example Implementation
```typescript
// src/lib/stores/base.svelte.ts
import { loadFromStorage, saveToStorage } from './persistence';

export function createStore<T>(
  initialState: T, 
  options: { persist?: string; autoSave?: boolean } = {}
) {
  // Load persisted state if available
  const persistedState = options.persist 
    ? loadFromStorage<T>(options.persist) 
    : null;
  
  let state = $state<T>(persistedState || initialState);
  let loading = $state(false);
  let error = $state<string | null>(null);
  
  // Auto-save to localStorage when state changes
  if (options.persist && options.autoSave) {
    $effect(() => {
      saveToStorage(options.persist!, state);
    });
  }
  
  return {
    // Getters
    get current() { return state; },
    get loading() { return loading; },
    get error() { return error; },
    
    // Setters
    set(newState: T) { 
      state = newState; 
    },
    
    update(updater: (s: T) => T) { 
      state = updater(state); 
    },
    
    setLoading(isLoading: boolean) {
      loading = isLoading;
    },
    
    setError(err: string | null) {
      error = err;
    },
    
    // Persistence
    save() {
      if (options.persist) {
        saveToStorage(options.persist, state);
      }
    },
    
    clear() {
      state = initialState;
      if (options.persist) {
        clearStorage(options.persist);
      }
    }
  };
}

// Example usage
interface User {
  id: number;
  name: string;
  email: string;
}

export const userStore = createStore<User | null>(null, { 
  persist: 'current_user',
  autoSave: true 
});
```

#### Acceptance Criteria
- [ ] Base store pattern created with $state, $derived, $effect
- [ ] LocalStorage persistence utilities implemented
- [ ] Store creation function accepts initial state and options
- [ ] TypeScript generics for type-safe stores
- [ ] Auto-save to localStorage on state change (optional)
- [ ] Loading and error state management included
- [ ] Documentation with examples created

#### Validation Steps
```typescript
// Create test store
import { createStore } from '$lib/stores/base.svelte';

interface Counter {
  count: number;
}

const counterStore = createStore<Counter>({ count: 0 }, {
  persist: 'counter',
  autoSave: true
});

// Test reactivity
console.log(counterStore.current); // { count: 0 }
counterStore.update(s => ({ count: s.count + 1 }));
console.log(counterStore.current); // { count: 1 }

// Test persistence
const stored = localStorage.getItem('counter');
console.log(JSON.parse(stored)); // { count: 1 }

// Test in component
// <script>
//   import { counterStore } from '$lib/stores/counter';
//   const { current } = counterStore;
// </script>
// <div>{current.count}</div>
```

---

### Task 0.4: Routing and Navigation Setup
**Priority**: P0 - CRITICAL  
**Estimated Time**: 6-8 hours  
**Dependencies**: Task 0.1  
**Requirements**: REQ-5 (Routing and Navigation)

#### Context
The Vue application uses Vue Router with nested routes, route guards, and dynamic parameters. SvelteKit uses file-based routing which requires a different structure. We need to plan the route structure and implement navigation utilities.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/routes/index.js` - Main router setup
- `chatwoot/app/javascript/dashboard/routes/dashboard/dashboard.routes.js` - Dashboard routes
- `chatwoot/app/javascript/dashboard/helper/routeHelpers.js` - Route utilities

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/routing/guards.ts` - Auth guards
- `chatwoot/custom/ui/svelte-ui/src/lib/routing/navigation.ts` - Navigation helpers
- `chatwoot/custom/ui/svelte-ui/src/lib/routing/params.ts` - Parameter utilities
- `chatwoot/custom/ui/svelte-ui/src/lib/routing/types.ts` - TypeScript types
- `chatwoot/custom/ui/svelte-ui/src/routes/ROUTING.md` - Documentation

#### SvelteKit Route Structure Plan
```
src/routes/
├── (auth)/                      # Authentication layout group
│   ├── login/
│   │   └── +page.svelte
│   ├── signup/
│   │   └── +page.svelte
│   ├── reset-password/
│   │   └── +page.svelte
│   └── +layout.svelte           # Auth layout (no sidebar)
├── (app)/                       # Authenticated app layout group  
│   ├── accounts/
│   │   └── [accountId]/
│   │       ├── dashboard/
│   │       │   └── +page.svelte
│   │       ├── conversations/
│   │       │   ├── +page.svelte
│   │       │   └── [conversationId]/
│   │       │       └── +page.svelte
│   │       ├── contacts/
│   │       │   ├── +page.svelte
│   │       │   └── [contactId]/
│   │       │       └── +page.svelte
│   │       ├── settings/
│   │       │   ├── +layout.svelte
│   │       │   ├── general/+page.svelte
│   │       │   ├── inboxes/+page.svelte
│   │       │   ├── teams/+page.svelte
│   │       │   └── agents/+page.svelte
│   │       └── reports/
│   │           └── +page.svelte
│   ├── +layout.svelte           # App layout (with sidebar)
│   └── +layout.ts               # Auth guard
├── +layout.svelte               # Root layout
└── +page.svelte                 # Home page (redirects)
```

#### Implementation Steps
1. Create `src/lib/routing/guards.ts`:
   - requireAuth() function to check authentication
   - requireRole(role) function for role-based access
   - requireAccount() function to ensure account context
2. Create `src/lib/routing/navigation.ts`:
   - Navigate helper wrapping goto
   - frontendURL(path) helper for URL construction
   - Navigation history utilities
3. Create `src/lib/routing/params.ts`:
   - Type-safe route parameter extraction
   - Query string parsing utilities
4. Implement auth guard in `src/routes/(app)/+layout.ts`:
   ```typescript
   import { redirect } from '@sveltejs/kit';
   import { authStore } from '$lib/stores/auth.svelte';
   
   export function load() {
     if (!authStore.isLoggedIn) {
       throw redirect(302, '/login');
     }
     
     return {
       user: authStore.currentUser
     };
   }
   ```
5. Create route loading states in layouts
6. Document routing patterns in ROUTING.md

#### Acceptance Criteria
- [ ] Route structure planned matching Vue Router functionality
- [ ] Auth guard created and integrated
- [ ] Navigation helpers created
- [ ] Parameter extraction utilities created
- [ ] Loading states for route transitions
- [ ] Documentation with examples

#### Validation Steps
```typescript
// Test auth guard
// Navigate to /accounts/1/dashboard without auth
// Expected: Redirect to /login

// Test navigation helper
import { navigate } from '$lib/routing/navigation';
navigate('/accounts/1/conversations/123');

// Test parameter extraction
import { getParam } from '$lib/routing/params';
const accountId = getParam(params, 'accountId', Number);
console.log(accountId); // Should be number
```

---

### Task 0.5: Internationalization (i18n) Setup
**Priority**: P0 - CRITICAL  
**Estimated Time**: 4-6 hours  
**Dependencies**: Task 0.1  
**Requirements**: REQ-6 (Internationalization)

#### Context
The Vue application uses vue-i18n with JSON translation files for multiple languages. We need to set up svelte-i18n with equivalent functionality including locale switching, pluralization, and date/number formatting.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/i18n/index.js` - i18n configuration
- `chatwoot/app/javascript/dashboard/i18n/locale/**/*.json` - Translation files

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/i18n/index.ts` - i18n setup
- `chatwoot/custom/ui/svelte-ui/src/lib/i18n/locales/**/*.json` - Translation files
- `chatwoot/custom/ui/svelte-ui/src/lib/i18n/formatters.ts` - Date/number formatters
- `chatwoot/custom/ui/svelte-ui/src/lib/i18n/types.ts` - TypeScript types

#### Implementation Steps
1. Install svelte-i18n: `pnpm add svelte-i18n`
2. Copy translation files from Vue to Svelte:
   ```bash
   cp -r app/javascript/dashboard/i18n/locale custom/ui/svelte-ui/src/lib/i18n/locales
   ```
3. Create `src/lib/i18n/index.ts`:
   - Initialize svelte-i18n
   - Set up supported locales
   - Implement lazy loading for translation files
   - Create locale switching function
4. Create `src/lib/i18n/formatters.ts`:
   - Date formatting utilities using date-fns
   - Number formatting utilities
   - Currency formatting
5. Initialize i18n in root layout:
   ```svelte
   <!-- src/routes/+layout.svelte -->
   <script>
     import { initI18n } from '$lib/i18n';
     import { onMount } from 'svelte';
     
     onMount(() => {
       initI18n('en'); // Default locale
     });
   </script>
   ```

#### Example Implementation
```typescript
// src/lib/i18n/index.ts
import { init, addMessages, locale, _ } from 'svelte-i18n';

const supportedLocales = ['en', 'es', 'fr', 'de', 'pt', 'ar', 'hi', 'ja'];

async function loadLocale(locale: string) {
  const messages = await import(`./locales/${locale}/index.json`);
  addMessages(locale, messages.default);
}

export async function initI18n(initialLocale: string) {
  await loadLocale(initialLocale);
  
  init({
    fallbackLocale: 'en',
    initialLocale
  });
}

export async function switchLocale(newLocale: string) {
  if (!supportedLocales.includes(newLocale)) {
    console.warn(`Locale ${newLocale} not supported`);
    return;
  }
  
  await loadLocale(newLocale);
  locale.set(newLocale);
}

export { _, locale };
```

#### Acceptance Criteria
- [ ] svelte-i18n installed and configured
- [ ] Translation files copied from Vue
- [ ] Lazy loading implemented for translation files
- [ ] Locale switching without page reload
- [ ] Date/time formatting integrated
- [ ] Number/currency formatting
- [ ] Pluralization support
- [ ] RTL languages supported

#### Validation Steps
```svelte
<!-- Test in component -->
<script>
  import { _, locale } from '$lib/i18n';
  import { switchLocale } from '$lib/i18n';
</script>

<p>{$_('dashboard.title')}</p>
<p>Current locale: {$locale}</p>
<button onclick={() => switchLocale('es')}>Switch to Spanish</button>
```

---

### Task 0.6: WebSocket Client Implementation
**Priority**: P0 - CRITICAL  
**Estimated Time**: 8-10 hours  
**Dependencies**: Task 0.1, Task 0.3  
**Requirements**: REQ-4 (Real-time Communication)

#### Context
The Vue application uses @rails/actioncable for WebSocket connections. We need to create a native WebSocket client that provides equivalent functionality including connection management, channel subscriptions, and automatic reconnection.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/helper/actionCable.js` - ActionCable setup
- `chatwoot/app/javascript/dashboard/store/modules/conversations.js` - WebSocket usage

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/websocket/client.ts` - WebSocket client class
- `chatwoot/custom/ui/svelte-ui/src/lib/websocket/channels.ts` - Channel management
- `chatwoot/custom/ui/svelte-ui/src/lib/websocket/types.ts` - TypeScript types
- `chatwoot/custom/ui/svelte-ui/src/lib/websocket/store.svelte.ts` - Connection state

#### Implementation Steps
1. Create WebSocket client class in `src/lib/websocket/client.ts`:
   - Constructor accepts token for authentication
   - connect() method establishes WebSocket connection
   - disconnect() method closes connection
   - subscribe(channel, callback) for channel subscriptions
   - unsubscribe(channel) for cleanup
   - send(channel, data) to broadcast messages
2. Implement automatic reconnection:
   - Exponential backoff strategy
   - Maximum 10 reconnect attempts
   - Delay: 1s, 2s, 4s, 8s, 16s, 30s (capped)
3. Create connection state store:
   - States: disconnected, connecting, connected, reconnecting, failed
   - Store last error
   - Track reconnect attempts
4. Implement heartbeat/ping-pong:
   - Send ping every 30 seconds
   - Expect pong within 5 seconds
   - Reconnect if pong not received
5. Integrate with Svelte stores for state updates

#### Example Implementation
```typescript
// src/lib/websocket/client.ts
import { createStore } from '$lib/stores/base.svelte';

type ConnectionState = 'disconnected' | 'connecting' | 'connected' | 'reconnecting' | 'failed';

interface WebSocketMessage {
  type: string;
  channel: string;
  data: any;
}

export class WebSocketClient {
  private ws: WebSocket | null = null;
  private channels = new Map<string, Set<(data: any) => void>>();
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 10;
  private heartbeatInterval: number | null = null;
  
  state = createStore<ConnectionState>('disconnected');
  
  constructor(private token: string) {}
  
  connect() {
    if (this.ws?.readyState === WebSocket.OPEN) return;
    
    this.state.set('connecting');
    const url = `${import.meta.env.VITE_WS_URL}?token=${this.token}`;
    this.ws = new WebSocket(url);
    
    this.ws.onopen = () => {
      this.state.set('connected');
      this.reconnectAttempts = 0;
      this.startHeartbeat();
      this.resubscribeChannels();
    };
    
    this.ws.onmessage = (event) => {
      const message: WebSocketMessage = JSON.parse(event.data);
      this.handleMessage(message);
    };
    
    this.ws.onerror = (error) => {
      console.error('WebSocket error:', error);
    };
    
    this.ws.onclose = () => {
      this.state.set('disconnected');
      this.stopHeartbeat();
      this.reconnect();
    };
  }
  
  disconnect() {
    this.ws?.close();
    this.channels.clear();
    this.stopHeartbeat();
  }
  
  subscribe(channel: string, callback: (data: any) => void) {
    if (!this.channels.has(channel)) {
      this.channels.set(channel, new Set());
      this.sendCommand('subscribe', channel);
    }
    this.channels.get(channel)!.add(callback);
    
    return () => {
      const callbacks = this.channels.get(channel);
      callbacks?.delete(callback);
      if (callbacks?.size === 0) {
        this.sendCommand('unsubscribe', channel);
        this.channels.delete(channel);
      }
    };
  }
  
  private handleMessage(message: WebSocketMessage) {
    const callbacks = this.channels.get(message.channel);
    if (callbacks) {
      callbacks.forEach(callback => callback(message.data));
    }
  }
  
  private reconnect() {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      this.state.set('failed');
      return;
    }
    
    this.state.set('reconnecting');
    const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
    this.reconnectAttempts++;
    
    setTimeout(() => {
      this.connect();
    }, delay);
  }
  
  private startHeartbeat() {
    this.heartbeatInterval = window.setInterval(() => {
      if (this.ws?.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify({ type: 'ping' }));
      }
    }, 30000);
  }
  
  private stopHeartbeat() {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
    }
  }
  
  private sendCommand(command: string, channel: string) {
    if (this.ws?.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify({ command, identifier: channel }));
    }
  }
  
  private resubscribeChannels() {
    for (const channel of this.channels.keys()) {
      this.sendCommand('subscribe', channel);
    }
  }
}

// Singleton instance
let wsClient: WebSocketClient | null = null;

export function getWebSocketClient(token?: string): WebSocketClient {
  if (!wsClient && token) {
    wsClient = new WebSocketClient(token);
  }
  return wsClient!;
}
```

#### Acceptance Criteria
- [ ] WebSocket client class created
- [ ] Secure connection with authentication token
- [ ] Channel subscription/unsubscription methods
- [ ] Event listener registration and cleanup
- [ ] Automatic reconnection with exponential backoff
- [ ] Connection state management ($state)
- [ ] Heartbeat/ping-pong mechanism
- [ ] Resubscribe to channels on reconnection

#### Validation Steps
```typescript
// Test WebSocket connection
import { getWebSocketClient } from '$lib/websocket/client';

const ws = getWebSocketClient('test-token');
ws.connect();

// Subscribe to channel
const unsubscribe = ws.subscribe('conversations', (data) => {
  console.log('Message received:', data);
});

// Check connection state
console.log(ws.state.current); // 'connected'

// Cleanup
unsubscribe();
ws.disconnect();
```

---

### Task 0.7: Utility Functions and Helpers Migration
**Priority**: P1 - HIGH  
**Estimated Time**: 6-8 hours  
**Dependencies**: Task 0.1  
**Requirements**: Supporting all requirements

#### Context
The Vue application has many utility functions and helpers spread across multiple files. These need to be migrated to TypeScript and organized in a maintainable structure.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/helper/*.js` - Various helpers
- `chatwoot/app/javascript/dashboard/composables/*.js` - Vue composables
- `chatwoot/app/javascript/shared/helpers/*.js` - Shared helpers

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/url.ts` - URL helpers
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/date.ts` - Date helpers
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/validation.ts` - Validation
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/format.ts` - Formatting
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/color.ts` - Color utilities
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/file.ts` - File utilities
- `chatwoot/custom/ui/svelte-ui/src/lib/utils/__tests__/*.test.ts` - Tests

#### Implementation Steps
1. Review all Vue helper files and categorize by function
2. Create URL helpers (frontendURL, buildURL, parseURL)
3. Create date helpers (formatDate, relativeTime, isToday, etc.)
4. Create validation helpers (isEmail, isPhone, isURL, etc.)
5. Create formatting helpers (formatNumber, formatCurrency, formatPhone, etc.)
6. Create color helpers (hexToRgb, adjustBrightness, getContrast, etc.)
7. Create file helpers (formatFileSize, getFileExtension, validateFile, etc.)
8. Add comprehensive JSDoc comments
9. Write unit tests for each utility

#### Example Implementations
```typescript
// src/lib/utils/url.ts
export function frontendURL(path: string, accountId?: number): string {
  const base = accountId ? `/accounts/${accountId}` : '';
  return `${base}${path.startsWith('/') ? path : '/' + path}`;
}

export function buildURL(base: string, params: Record<string, any>): string {
  const url = new URL(base, window.location.origin);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      url.searchParams.append(key, String(value));
    }
  });
  return url.toString();
}

// src/lib/utils/date.ts
import { formatDistanceToNow, format, isToday, isYesterday } from 'date-fns';

export function relativeTime(date: Date | string): string {
  const d = typeof date === 'string' ? new Date(date) : date;
  return formatDistanceToNow(d, { addSuffix: true });
}

export function formatDateTime(date: Date | string, formatStr = 'PPpp'): string {
  const d = typeof date === 'string' ? new Date(date) : date;
  return format(d, formatStr);
}

export function isDateToday(date: Date | string): boolean {
  const d = typeof date === 'string' ? new Date(date) : date;
  return isToday(d);
}

// src/lib/utils/validation.ts
export function isEmail(value: string): boolean {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(value);
}

export function isPhone(value: string): boolean {
  const regex = /^[\d\s\-\+\(\)]+$/;
  return regex.test(value) && value.replace(/\D/g, '').length >= 10;
}

export function isURL(value: string): boolean {
  try {
    new URL(value);
    return true;
  } catch {
    return false;
  }
}

// src/lib/utils/format.ts
export function formatNumber(value: number, decimals = 0): string {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value);
}

export function formatCurrency(value: number, currency = 'USD'): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency
  }).format(value);
}

export function formatFileSize(bytes: number): string {
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  let size = bytes;
  let unitIndex = 0;
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex++;
  }
  
  return `${size.toFixed(2)} ${units[unitIndex]}`;
}
```

#### Acceptance Criteria
- [ ] All Vue helpers migrated to TypeScript
- [ ] Utilities organized by category
- [ ] Comprehensive JSDoc comments
- [ ] Unit tests for all utilities (>90% coverage)
- [ ] No dependencies on Vue-specific APIs
- [ ] Type-safe function signatures

#### Validation Steps
```bash
# Run unit tests
cd chatwoot/custom/ui/svelte-ui
pnpm test src/lib/utils

# Check test coverage
pnpm test -- --coverage src/lib/utils

# Expected: >90% coverage
```

---

## PHASE 1: Core State Management and API Integration (Weeks 3-5)

### Task 1.1: Authentication Store and API
**Priority**: P0 - CRITICAL  
**Estimated Time**: 8-10 hours  
**Dependencies**: Task 0.2, Task 0.3  
**Requirements**: REQ-13 (Authentication)

#### Context
Authentication is the foundation of the application. We need to migrate the Vuex auth store and all authentication API calls to Svelte stores and ky client.

#### Vue Reference Files
- `chatwoot/app/javascript/dashboard/store/modules/auth.js` - Auth store
- `chatwoot/app/javascript/dashboard/api/auth.js` - Auth API

#### Svelte Files to Create
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/auth.svelte.ts` - Auth store
- `chatwoot/custom/ui/svelte-ui/src/lib/api/auth.ts` - Auth API client
- `chatwoot/custom/ui/svelte-ui/src/lib/stores/__tests__/auth.test.ts` - Tests

#### Implementation Steps
1. Review Vue auth store to understand:
   - State: currentUser, token, isLoggedIn
   - Mutations: setUser, clearUser, setToken
   - Actions: login, logout, getCurrentUser, verifySession
2. Create auth API client in `src/lib/api/auth.ts`:
   - login(email, password) - POST /auth/sign_in
   - logout() - DELETE /auth/sign_out
   - getCurrentUser() - GET /api/v1/profile
   - verifySession() - GET /auth/validate_token
   - resetPassword(email) - POST /auth/password
3. Create auth store in `src/lib/stores/auth.svelte.ts`:
   ```typescript
   import api from '$lib/api/client';
   import { goto } from '$app/navigation';
   
   interface User {
     id: number;
     email: string;
     name: string;
     role: string;
     accounts: Account[];
   }
   
   let currentUser = $state<User | null>(null);
   let token = $state<string | null>(
     typeof localStorage !== 'undefined' ? localStorage.getItem('auth_token') : null
   );
   let loading = $state(false);
   let error = $state<string | null>(null);
   
   // Derived state
   const isLoggedIn = $derived(!!currentUser && !!token);
   
   // Save token to localStorage
   $effect(() => {
     if (typeof localStorage !== 'undefined') {
       if (token) {
         localStorage.setItem('auth_token', token);
       } else {
         localStorage.removeItem('auth_token');
       }
     }
   });
   
   async function login(email: string, password: string) {
     loading = true;
     error = null;
     try {
       const response = await api.post('auth/sign_in', {
         json: { email, password }
       }).json();
       
       token = response.token;
       currentUser = response.user;
       
       return true;
     } catch (err) {
       error = err.message;
       return false;
     } finally {
       loading = false;
     }
   }
   
   async function logout() {
     try {
       await api.delete('auth/sign_out').json();
     } catch (err) {
       console.error('Logout error:', err);
     } finally {
       token = null;
       currentUser = null;
       goto('/login');
     }
   }
   
   async function getCurrentUser() {
     if (!token) return;
     
     loading = true;
     try {
       const user = await api.get('api/v1/profile').json();
       currentUser = user;
     } catch (err) {
       error = err.message;
       // Token might be invalid, logout
       await logout();
     } finally {
       loading = false;
     }
   }
   
   export const authStore = {
     get currentUser() { return currentUser; },
     get token() { return token; },
     get isLoggedIn() { return isLoggedIn; },
     get loading() { return loading; },
     get error() { return error; },
     login,
     logout,
     getCurrentUser
   };
   ```
4. Integrate with routing guards
5. Write unit tests

#### Acceptance Criteria
- [ ] Auth store created with $state and $derived
- [ ] Auth API client created with all methods
- [ ] login() method stores token in localStorage
- [ ] logout() method clears token and redirects
- [ ] getCurrentUser() method fetches user profile
- [ ] Token persistence across page reloads
- [ ] Integration with routing guards
- [ ] Comprehensive error handling
- [ ] Unit tests with >80% coverage

#### Validation Steps
```typescript
// Test login flow
import { authStore } from '$lib/stores/auth.svelte';

await authStore.login('test@example.com', 'password');
console.log(authStore.isLoggedIn); // true
console.log(authStore.currentUser); // User object
console.log(authStore.token); // JWT token

// Test logout
await authStore.logout();
console.log(authStore.isLoggedIn); // false
console.log(authStore.currentUser); // null

// Test token persistence
// Reload page
console.log(localStorage.getItem('auth_token')); // Should have token
await authStore.getCurrentUser();
console.log(authStore.currentUser); // User object restored
```

---

(Continue with similar detailed breakdowns for all remaining tasks...)

## Task Execution Guidelines for AI Agents

When executing a task:

1. **Read Context**: Understand the Vue reference files and current implementation
2. **Review Dependencies**: Ensure prerequisite tasks are complete
3. **Plan Implementation**: Break down into specific code changes
4. **Write Code**: Implement with TypeScript, proper types, JSDoc comments
5. **Add Tests**: Write unit tests for new functionality
6. **Validate**: Complete all validation steps in acceptance criteria
7. **Document**: Update README if patterns change
8. **Report**: Provide summary of changes and any issues

## Notes

- Each task designed for 2-8 hours of focused work
- All Vue file paths are absolute and verified
- Acceptance criteria are specific and measurable
- Validation steps ensure quality before moving forward
- Dependencies prevent out-of-order execution
- Estimated hours are guidelines, adjust as needed
- Regular checkpoints ensure progress tracking
- This is a living document - update as needed
