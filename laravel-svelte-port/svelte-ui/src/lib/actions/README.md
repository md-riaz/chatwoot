# Svelte 5 Mutation/Action Pattern

This directory implements a comprehensive Mutation/Action pattern using Svelte 5 runes that provides Vue-like composable functionality for the Chatwoot migration project.

## Overview

The pattern leverages Svelte 5's reactive classes and runes to create a powerful state management system that rivals Vue's Composition API while maintaining Svelte's simplicity and performance advantages.

## Key Features

### 🚀 **Vue Parity with Svelte Advantages**
- **No `.value` syntax** - Direct reactive access like `action.loading` instead of `action.loading.value`
- **Automatic dependency tracking** - `$effect` automatically tracks what you read
- **Class-based reactive state** - Public fields become reactive automatically
- **Compile-time optimizations** - Zero runtime overhead for reactivity

### 🎯 **Action Types**

#### **BaseAction** - Core reactive action class
```typescript
const action = new BaseAction(async (params) => api.call(params));
console.log(action.loading); // Reactive boolean
console.log(action.data);    // Reactive data
console.log(action.error);   // Reactive error state
```

#### **QueryAction** - For data fetching (read operations)
```typescript
const query = new QueryAction(async (params) => api.getUsers(params));
await query.execute({ page: 1 });
await query.refetch(); // Re-run with last parameters
```

#### **MutationAction** - For data modification (write operations)
```typescript
const mutation = new MutationAction(
  async (params) => api.createUser(params),
  {
    optimisticUpdate: (params) => ({ ...params, id: 'temp' }),
    onSuccess: (data) => console.log('Created:', data),
    onError: (error) => console.error('Failed:', error)
  }
);
```

### 🔄 **Reactive State Management**

All actions provide reactive state using Svelte 5 runes:

```typescript
// Reactive primitives using $state
loading = $state<boolean>(false);
error = $state<string | null>(null);
data = $state<T | null>(null);

// Computed values using $derived  
success = $derived(this.data !== null && !this.error && !this.loading);
idle = $derived(!this.loading && !this.error && this.data === null);
```

### ⚡ **Advanced Features**

#### **Optimistic Updates**
```typescript
const updateContact = new MutationAction(
  async ({ id, ...data }) => api.updateContact(id, data),
  {
    optimisticUpdate: (params) => ({ ...originalContact, ...params }),
    onRollback: () => console.log('Rolling back changes')
  }
);
```

#### **Automatic Retries with Backoff**
```typescript
const action = new BaseAction(apiCall, {
  retry: {
    attempts: 3,
    delay: 1000,
    backoff: 'exponential' // 1s, 2s, 4s delays
  }
});
```

#### **Request Cancellation**
```typescript
const action = new BaseAction(apiCall);
await action.execute(params);
action.cancel(); // Cancels ongoing request
```

#### **Validation Error Handling**
```typescript
class CreateContactMutation extends MutationAction {
  validationErrors = $state<Record<string, string>>({});
  
  constructor() {
    super(apiCall, {
      onError: (error) => {
        if (error.isValidationError?.()) {
          this.validationErrors = error.data.errors;
        }
      }
    });
  }
}
```

## Architecture Comparison

### Vue 3 Composition API vs Svelte 5 Actions

| Feature | Vue 3 | Svelte 5 Actions |
|---------|-------|------------------|
| **Reactive State** | `const loading = ref(false)` | `loading = $state(false)` |
| **Access Value** | `loading.value` | `loading` |
| **Computed** | `const success = computed(() => ...)` | `success = $derived(...)` |
| **Side Effects** | `watch(data, callback)` | `$effect(() => ...)` |
| **Cleanup** | `onUnmounted(cleanup)` | `$effect(() => cleanup)` |
| **Class Support** | Limited | Native with auto-getters |

### Benefits Over Vue

1. **No `.value` Everywhere** - Cleaner, more readable code
2. **Automatic Dependency Tracking** - No manual dependency arrays
3. **Better TypeScript Integration** - Native class support
4. **Compile-time Optimizations** - Zero runtime reactivity overhead
5. **Unified Reactivity Model** - Same patterns for primitives and objects

## Usage Patterns

### 1. **Simple Query Action**
```typescript
// Create action
const usersQuery = new QueryAction(
  async ({ page }) => api.getUsers({ page })
);

// In component
$effect(() => {
  usersQuery.execute({ page: 1 });
});

// Reactive access
const users = $derived(usersQuery.data?.data || []);
const isLoading = $derived(usersQuery.loading);
```

### 2. **Mutation with Optimistic Update**
```typescript
const updateUser = new MutationAction(
  async ({ id, ...data }) => api.updateUser(id, data),
  {
    optimisticUpdate: (params) => ({ ...currentUser, ...params }),
    onSuccess: (user) => {
      notifications.success(`Updated ${user.name}`);
    }
  }
);

// Execute with optimistic update
await updateUser.execute({ id: 1, name: 'New Name' });
```

### 3. **Composable Actions Factory**
```typescript
export class ContactActions {
  list = new QueryAction(/* ... */);
  create = new MutationAction(/* ... */);
  update = new MutationAction(/* ... */);
  delete = new MutationAction(/* ... */);
  
  // Derived state combining all actions
  isAnyLoading = $derived(
    this.list.loading || this.create.loading || 
    this.update.loading || this.delete.loading
  );
  
  constructor(private accountId: number) {
    // Auto-cleanup on component unmount
    $effect(() => {
      return () => this.cleanup();
    });
  }
}

// Factory function (Vue-like composable)
export function useContactActions(accountId: number) {
  return new ContactActions(accountId);
}
```

### 4. **Component Usage**
```svelte
<script>
  import { useContactActions } from '$lib/actions/contacts.svelte.ts';
  
  const accountId = parseInt($page.params.accountId);
  const contacts = useContactActions(accountId);
  
  // Derived state from actions
  const contactList = $derived(contacts.list.data?.data || []);
  const isLoading = $derived(contacts.isAnyLoading);
  
  // Load initial data
  $effect(() => {
    contacts.fetchContacts({ page: 1 });
  });
  
  // Handle form submission
  async function handleCreate(formData) {
    const contact = await contacts.createContact(formData);
    if (contact) {
      // Success handled automatically
      closeModal();
    }
    // Errors handled automatically
  }
</script>

<!-- Reactive template -->
{#if isLoading}
  <div>Loading...</div>
{:else}
  {#each contactList as contact}
    <ContactCard {contact} />
  {/each}
{/if}
```

## File Structure

```
src/lib/actions/
├── base.svelte.ts           # Core action classes
├── contacts.svelte.ts       # Contact-specific actions
├── conversations.svelte.ts  # Conversation actions
├── agents.svelte.ts         # Agent management actions
├── examples/
│   └── ContactsPage.svelte  # Usage example
└── README.md               # This file
```

## Migration from Vue Patterns

### Before (Vue 3 Composition API)
```typescript
// Vue composable
export function useContacts(accountId: Ref<number>) {
  const contacts = ref<Contact[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  
  const fetchContacts = async () => {
    loading.value = true;
    try {
      const response = await api.getContacts(accountId.value);
      contacts.value = response.data;
    } catch (err) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  return { contacts, loading, error, fetchContacts };
}
```

### After (Svelte 5 Actions)
```typescript
// Svelte action
export class ContactsQuery extends QueryAction<Contact[], { accountId: number }> {
  constructor() {
    super(async ({ accountId }) => {
      const response = await api.getContacts(accountId);
      return response.data;
    });
  }
}

// Usage
const contactsQuery = new ContactsQuery();
await contactsQuery.execute({ accountId });

// Reactive access (no .value needed)
const contacts = contactsQuery.data || [];
const loading = contactsQuery.loading;
const error = contactsQuery.error;
```

## Best Practices

### 1. **Use Appropriate Action Types**
- **QueryAction** for data fetching (GET requests)
- **MutationAction** for data modification (POST/PUT/DELETE)
- **BaseAction** for custom logic

### 2. **Leverage Optimistic Updates**
```typescript
const updateAction = new MutationAction(apiCall, {
  optimisticUpdate: (params) => {
    // Return optimistic data immediately
    return { ...currentData, ...params };
  },
  onRollback: () => {
    // Handle rollback on error
    showErrorMessage('Update failed, changes reverted');
  }
});
```

### 3. **Combine Actions in Composables**
```typescript
export class FeatureActions {
  query = new QueryAction(/* ... */);
  create = new MutationAction(/* ... */);
  update = new MutationAction(/* ... */);
  
  // Derived state
  hasData = $derived(!!this.query.data);
  canCreate = $derived(!this.create.loading);
}
```

### 4. **Handle Errors Gracefully**
```typescript
const action = new MutationAction(apiCall, {
  onError: (error, variables) => {
    if (error.isValidationError?.()) {
      // Handle validation errors
      showValidationErrors(error.data.errors);
    } else {
      // Handle other errors
      showErrorToast(error.message);
    }
  }
});
```

### 5. **Use Automatic Cleanup**
```typescript
export class ComponentActions {
  constructor() {
    // Auto-cleanup on component unmount
    $effect(() => {
      return () => {
        this.cancelAllRequests();
        this.clearCache();
      };
    });
  }
}
```

## Performance Benefits

1. **Compile-time Optimizations** - Svelte compiles runes to efficient JavaScript
2. **Fine-grained Reactivity** - Only updates what actually changed
3. **No Virtual DOM** - Direct DOM updates
4. **Smaller Bundle Size** - No runtime reactivity system
5. **Better Tree Shaking** - Unused actions are eliminated

## Conclusion

This Mutation/Action pattern provides Vue-like composable functionality while leveraging Svelte 5's superior reactivity system. It offers:

- **Better Developer Experience** - No `.value` syntax, cleaner code
- **Superior Performance** - Compile-time optimizations, fine-grained updates
- **Type Safety** - Full TypeScript support with class-based patterns
- **Familiar Patterns** - Easy migration from Vue Composition API
- **Advanced Features** - Optimistic updates, retries, cancellation

The pattern maintains the simplicity that makes Svelte great while providing the power and flexibility needed for complex applications like Chatwoot.