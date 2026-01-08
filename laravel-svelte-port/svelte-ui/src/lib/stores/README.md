# Svelte Store Patterns with Runes

This directory contains store patterns using Svelte 5 runes to replace Vuex/Pinia from the Vue application.

## Store Types

### 1. Basic Store (`createStore`)

Simple reactive store with loading and error states.

```typescript
import { createStore } from './base.svelte';

interface User {
  id: number;
  name: string;
  email: string;
}

const userStore = createStore<User | null>(null, {
  persist: 'current_user',  // Save to localStorage
  autoSave: true            // Auto-save on changes
});

// In component
const { current, loading, error } = userStore;

// Mutations
userStore.set({ id: 1, name: 'John', email: 'john@example.com' });
userStore.update(user => ({ ...user, name: 'Jane' }));
userStore.reset(); // Reset to initial state
```

### 2. Async Store (`createAsyncStore`)

Store that handles async data fetching.

```typescript
import { createAsyncStore } from './base.svelte';
import api from '$lib/api/client';

const usersStore = createAsyncStore<User[]>(
  [],
  async () => api.get('api/v1/users').json()
);

// Load data
await usersStore.load();

// Access data
const { current, loading, error } = usersStore;
```

### 3. Paginated Store (`createPaginatedStore`)

Store for paginated list data with infinite scroll support.

```typescript
import { createPaginatedStore } from './base.svelte';

const conversationsStore = createPaginatedStore<Conversation>(
  async (page, perPage) => {
    const response = await api.get(`api/v1/conversations?page=${page}&per_page=${perPage}`).json();
    return {
      items: response.data,
      page: response.meta.currentPage,
      perPage: response.meta.perPage,
      totalPages: response.meta.totalPages,
      totalCount: response.meta.totalCount,
      hasMore: response.meta.currentPage < response.meta.totalPages
    };
  },
  20 // Items per page
);

// Load first page
await conversationsStore.loadPage(1);

// Load more (for infinite scroll)
await conversationsStore.loadMore();

// Access data
const { current, loading, error } = conversationsStore;
console.log(current.items); // Array of conversations
console.log(current.hasMore); // Boolean
```

### 4. Derived Store (`createDerivedStore`)

Computed value based on other stores.

```typescript
import { createDerivedStore } from './base.svelte';

const userStore = createStore({ firstName: 'John', lastName: 'Doe' });

const fullNameStore = createDerivedStore(() => {
  const user = userStore.current;
  return `${user.firstName} ${user.lastName}`;
});

console.log(fullNameStore.current); // "John Doe"
```

## Svelte 5 Runes Used

### `$state` - Reactive State

Replaces Vuex `state`. Creates reactive values that trigger updates when changed.

```typescript
let count = $state(0);
count++; // Triggers reactivity
```

### `$derived` - Computed Values

Replaces Vuex `getters`. Creates values that automatically recompute when dependencies change.

```typescript
const doubled = $derived(count * 2);
```

### `$effect` - Side Effects

Replaces Vuex `actions` for side effects. Runs code when dependencies change.

```typescript
$effect(() => {
  console.log(`Count changed to: ${count}`);
  // Save to localStorage, make API call, etc.
});
```

## Persistence

Stores can automatically persist to localStorage:

```typescript
const store = createStore(initialValue, {
  persist: 'store_key',  // localStorage key
  autoSave: true         // Save automatically on change
});

// Manual persistence
store.save();  // Save current state
store.clear(); // Clear localStorage and reset
```

## Best Practices

### 1. Keep Stores Focused

Create separate stores for different domains:

```typescript
// ✅ Good
const authStore = createStore(...);
const conversationsStore = createStore(...);
const messagesStore = createStore(...);

// ❌ Bad
const everythingStore = createStore(...);
```

### 2. Use Derived Stores for Computed Values

```typescript
// ✅ Good - automatically updates
const filteredUsers = createDerivedStore(() => {
  return usersStore.current.filter(u => u.active);
});

// ❌ Bad - manual updates needed
let filteredUsers = $state([]);
```

### 3. Handle Loading and Error States

```typescript
async function loadUsers() {
  usersStore.setLoading(true);
  try {
    const data = await api.get('users').json();
    usersStore.set(data);
  } catch (err) {
    usersStore.setError(err.message);
  }
}
```

### 4. Use Persistence Wisely

Only persist necessary data:

```typescript
// ✅ Good - persist auth token
const authStore = createStore(null, { persist: 'auth' });

// ❌ Bad - don't persist large datasets
const conversationsStore = createStore([], { persist: 'all_conversations' });
```

## Migration from Vuex

### Vuex State → $state

```javascript
// Vuex
state: {
  user: null
}

// Svelte
let user = $state(null);
```

### Vuex Getters → $derived

```javascript
// Vuex
getters: {
  fullName: state => `${state.firstName} ${state.lastName}`
}

// Svelte
const fullName = $derived(`${firstName} ${lastName}`);
```

### Vuex Mutations → Direct Updates

```javascript
// Vuex
mutations: {
  SET_USER(state, user) {
    state.user = user;
  }
}

// Svelte
userStore.set(user);
```

### Vuex Actions → Async Functions

```javascript
// Vuex
actions: {
  async fetchUser({ commit }) {
    const user = await api.get('user');
    commit('SET_USER', user);
  }
}

// Svelte
async function fetchUser() {
  const user = await api.get('user').json();
  userStore.set(user);
}
```

## Real-World Example

```typescript
// stores/conversations.svelte.ts
import { createStore } from './base.svelte';
import api from '$lib/api/client';

interface Conversation {
  id: number;
  status: string;
  messages: Message[];
}

const conversationsStore = createStore<Conversation[]>([], {
  persist: 'conversations_cache',
  autoSave: false // Don't auto-save large data
});

let selectedId = $state<number | null>(null);

// Derived store for selected conversation
const selectedConversation = $derived(
  conversationsStore.current.find(c => c.id === selectedId)
);

// Derived store for open conversations
const openConversations = $derived(
  conversationsStore.current.filter(c => c.status === 'open')
);

export const conversations = {
  get all() { return conversationsStore.current; },
  get selected() { return selectedConversation; },
  get open() { return openConversations; },
  get loading() { return conversationsStore.loading; },
  get error() { return conversationsStore.error; },
  
  async load() {
    conversationsStore.setLoading(true);
    try {
      const data = await api.get('api/v1/conversations').json();
      conversationsStore.set(data);
    } catch (err) {
      conversationsStore.setError(err.message);
    }
  },
  
  select(id: number) {
    selectedId = id;
  },
  
  async updateStatus(id: number, status: string) {
    // Optimistic update
    const original = conversationsStore.current;
    conversationsStore.update(convs =>
      convs.map(c => c.id === id ? { ...c, status } : c)
    );
    
    try {
      await api.patch(`api/v1/conversations/${id}`, {
        json: { status }
      }).json();
    } catch (err) {
      // Rollback on error
      conversationsStore.set(original);
      throw err;
    }
  }
};
```

## Testing Stores

```typescript
import { describe, it, expect } from 'vitest';
import { createStore } from './base.svelte';

describe('Store', () => {
  it('initializes with default value', () => {
    const store = createStore(0);
    expect(store.current).toBe(0);
  });
  
  it('updates value', () => {
    const store = createStore(0);
    store.set(5);
    expect(store.current).toBe(5);
  });
  
  it('updates with function', () => {
    const store = createStore(0);
    store.update(n => n + 1);
    expect(store.current).toBe(1);
  });
});
```

## See Also

- [Svelte 5 Runes Documentation](https://svelte-5-preview.vercel.app/docs/runes)
- [SvelteKit Documentation](https://kit.svelte.dev/docs)
- Vuex migration guide in `/app/javascript/dashboard/store/` (Vue source)
