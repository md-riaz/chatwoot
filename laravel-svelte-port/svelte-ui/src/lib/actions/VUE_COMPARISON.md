# Svelte 5 Actions vs Vue 3 Composition API

This document provides a detailed comparison between our Svelte 5 Action pattern and Vue 3's Composition API, demonstrating how Svelte 5 runes provide superior developer experience and performance.

## Side-by-Side Code Comparison

### 1. Basic Reactive State

#### Vue 3 Composition API
```typescript
import { ref, computed, watch, onMounted } from 'vue';

export function useContacts(accountId: Ref<number>) {
  const contacts = ref<Contact[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);
  
  // Computed values need explicit dependencies
  const hasContacts = computed(() => contacts.value.length > 0);
  const contactCount = computed(() => contacts.value.length);
  
  // Watch for changes
  watch(accountId, async (newId) => {
    await fetchContacts(newId);
  });
  
  const fetchContacts = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.getContacts(id);
      contacts.value = response.data;
    } catch (err: any) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  onMounted(() => {
    fetchContacts(accountId.value);
  });
  
  return {
    contacts: readonly(contacts),
    loading: readonly(loading),
    error: readonly(error),
    hasContacts,
    contactCount,
    fetchContacts
  };
}
```

#### Svelte 5 Actions
```typescript
export class ContactsQuery extends QueryAction<Contact[], { accountId: number }> {
  constructor() {
    super(async ({ accountId }) => {
      const response = await api.getContacts(accountId);
      return response.data;
    }, {
      retry: { attempts: 3, delay: 1000, backoff: 'exponential' }
    });
  }
  
  // Computed values with automatic dependency tracking
  hasContacts = $derived((this.data || []).length > 0);
  contactCount = $derived((this.data || []).length);
}

// Usage in component
const contactsQuery = new ContactsQuery();

// Auto-execute when accountId changes
$effect(() => {
  contactsQuery.execute({ accountId });
});

// Direct reactive access (no .value needed)
const contacts = contactsQuery.data || [];
const loading = contactsQuery.loading;
const hasContacts = contactsQuery.hasContacts;
```

### 2. Form Handling with Validation

#### Vue 3 Composition API
```typescript
export function useContactForm() {
  const form = reactive({
    name: '',
    email: '',
    phone: ''
  });
  
  const errors = ref<Record<string, string>>({});
  const submitting = ref(false);
  const submitted = ref(false);
  
  const isValid = computed(() => {
    return form.name.length > 0 && 
           form.email.includes('@') && 
           Object.keys(errors.value).length === 0;
  });
  
  const validateField = (field: string, value: string) => {
    const newErrors = { ...errors.value };
    
    switch (field) {
      case 'name':
        if (!value.trim()) {
          newErrors.name = 'Name is required';
        } else {
          delete newErrors.name;
        }
        break;
      case 'email':
        if (!value.includes('@')) {
          newErrors.email = 'Invalid email';
        } else {
          delete newErrors.email;
        }
        break;
    }
    
    errors.value = newErrors;
  };
  
  // Watch form changes for validation
  watch(() => form.name, (value) => validateField('name', value));
  watch(() => form.email, (value) => validateField('email', value));
  
  const submit = async () => {
    submitting.value = true;
    errors.value = {};
    
    try {
      await api.createContact(toRaw(form));
      submitted.value = true;
      
      // Reset form
      Object.assign(form, { name: '', email: '', phone: '' });
    } catch (err: any) {
      if (err.isValidationError?.()) {
        errors.value = err.data.errors;
      }
    } finally {
      submitting.value = false;
    }
  };
  
  return {
    form,
    errors: readonly(errors),
    submitting: readonly(submitting),
    submitted: readonly(submitted),
    isValid,
    submit
  };
}
```

#### Svelte 5 Actions
```typescript
export class ContactFormMutation extends MutationAction<Contact, CreateContactParams> {
  // Form state
  form = $state({
    name: '',
    email: '',
    phone: ''
  });
  
  // Validation errors
  validationErrors = $state<Record<string, string>>({});
  
  // Computed validation
  isValid = $derived(
    this.form.name.trim().length > 0 && 
    this.form.email.includes('@') && 
    Object.keys(this.validationErrors).length === 0
  );
  
  constructor(accountId: number) {
    super(
      async (params) => api.createContact(accountId, params),
      {
        onSuccess: (contact) => {
          console.log('Contact created:', contact.name);
          this.resetForm();
        },
        onError: (error) => {
          if (error.isValidationError?.()) {
            this.validationErrors = error.data.errors;
          }
        }
      }
    );
    
    // Auto-validation on form changes
    $effect(() => {
      this.validateForm();
    });
  }
  
  validateForm() {
    const errors: Record<string, string> = {};
    
    if (!this.form.name.trim()) {
      errors.name = 'Name is required';
    }
    
    if (!this.form.email.includes('@')) {
      errors.email = 'Invalid email';
    }
    
    this.validationErrors = errors;
  }
  
  async submit() {
    if (!this.isValid) return;
    
    await this.execute(this.form);
  }
  
  resetForm() {
    this.form = { name: '', email: '', phone: '' };
    this.validationErrors = {};
  }
}
```

### 3. Optimistic Updates

#### Vue 3 Composition API
```typescript
export function useContactUpdate() {
  const updating = ref(false);
  const error = ref<string | null>(null);
  const originalContact = ref<Contact | null>(null);
  
  const updateContact = async (contact: Contact, updates: Partial<Contact>) => {
    updating.value = true;
    error.value = null;
    
    // Store original for rollback
    originalContact.value = { ...contact };
    
    // Optimistic update
    Object.assign(contact, updates);
    
    try {
      const updatedContact = await api.updateContact(contact.id, updates);
      
      // Update with server response
      Object.assign(contact, updatedContact);
    } catch (err: any) {
      // Rollback on error
      if (originalContact.value) {
        Object.assign(contact, originalContact.value);
      }
      error.value = err.message;
    } finally {
      updating.value = false;
      originalContact.value = null;
    }
  };
  
  return {
    updating: readonly(updating),
    error: readonly(error),
    updateContact
  };
}
```

#### Svelte 5 Actions
```typescript
export class UpdateContactMutation extends MutationAction<Contact, UpdateContactParams> {
  constructor(private originalContact: Contact) {
    super(
      async (params) => api.updateContact(originalContact.id, params),
      {
        // Automatic optimistic update
        optimisticUpdate: (params) => ({
          ...this.originalContact,
          ...params,
          updatedAt: new Date().toISOString()
        }),
        
        onSuccess: (contact) => {
          console.log('Contact updated:', contact.name);
        },
        
        onRollback: () => {
          console.log('Rolling back optimistic update');
        }
      }
    );
  }
}

// Usage - optimistic updates happen automatically
const updateMutation = new UpdateContactMutation(contact);
await updateMutation.execute({ name: 'New Name' });
```

### 4. Complex State Management

#### Vue 3 Composition API
```typescript
export function useContactManagement(accountId: Ref<number>) {
  // Multiple reactive states
  const contacts = ref<Contact[]>([]);
  const selectedContacts = ref<number[]>([]);
  const searchQuery = ref('');
  const currentPage = ref(1);
  const loading = ref(false);
  const error = ref<string | null>(null);
  
  // Multiple computed values
  const filteredContacts = computed(() => {
    if (!searchQuery.value) return contacts.value;
    
    return contacts.value.filter(contact =>
      contact.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      contact.email?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  });
  
  const selectedContactsData = computed(() => {
    return contacts.value.filter(c => selectedContacts.value.includes(c.id));
  });
  
  const canBulkDelete = computed(() => selectedContacts.value.length > 0);
  
  // Multiple watchers
  watch(accountId, async (newId) => {
    await fetchContacts(newId);
  });
  
  watch(searchQuery, useDebounceFn(async (query) => {
    if (query.trim()) {
      await searchContacts(query);
    } else {
      await fetchContacts(accountId.value);
    }
  }, 300));
  
  // Multiple async functions
  const fetchContacts = async (id: number) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.getContacts(id, { page: currentPage.value });
      contacts.value = response.data;
    } catch (err: any) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  const searchContacts = async (query: string) => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await api.searchContacts(accountId.value, query);
      contacts.value = response.data;
    } catch (err: any) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  const bulkDelete = async () => {
    if (selectedContacts.value.length === 0) return;
    
    loading.value = true;
    error.value = null;
    
    try {
      await api.bulkDeleteContacts(accountId.value, selectedContacts.value);
      
      // Remove deleted contacts
      contacts.value = contacts.value.filter(
        c => !selectedContacts.value.includes(c.id)
      );
      selectedContacts.value = [];
    } catch (err: any) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };
  
  return {
    contacts: readonly(contacts),
    selectedContacts,
    searchQuery,
    currentPage,
    loading: readonly(loading),
    error: readonly(error),
    filteredContacts,
    selectedContactsData,
    canBulkDelete,
    fetchContacts,
    searchContacts,
    bulkDelete
  };
}
```

#### Svelte 5 Actions
```typescript
export class ContactManagement {
  // Actions handle their own state
  list = new ContactListQuery();
  search = new ContactSearchQuery();
  bulkDelete = new BulkDeleteMutation();
  
  // Local UI state
  selectedContacts = $state<number[]>([]);
  searchQuery = $state('');
  
  // Computed values with automatic dependency tracking
  displayContacts = $derived(
    this.searchQuery.trim() 
      ? this.search.data?.data || []
      : this.list.data?.data || []
  );
  
  selectedContactsData = $derived(
    this.displayContacts.filter(c => this.selectedContacts.includes(c.id))
  );
  
  canBulkDelete = $derived(this.selectedContacts.length > 0);
  
  isAnyLoading = $derived(
    this.list.loading || this.search.loading || this.bulkDelete.loading
  );
  
  constructor(private accountId: number) {
    // Auto-fetch on creation
    $effect(() => {
      this.list.execute({ accountId });
    });
    
    // Auto-search with debouncing
    $effect(() => {
      if (this.searchQuery.trim()) {
        this.search.searchDebounced({ 
          accountId, 
          query: this.searchQuery 
        });
      }
    });
  }
  
  async bulkDeleteSelected() {
    if (this.selectedContacts.length === 0) return;
    
    await this.bulkDelete.execute({
      accountId: this.accountId,
      contactIds: this.selectedContacts
    });
    
    if (this.bulkDelete.success) {
      this.selectedContacts = [];
      // Refresh list
      await this.list.execute({ accountId: this.accountId });
    }
  }
}
```

## Key Advantages of Svelte 5 Actions

### 1. **No `.value` Syntax**
- **Vue**: `loading.value`, `contacts.value`, `error.value`
- **Svelte**: `loading`, `contacts`, `error`

### 2. **Automatic Dependency Tracking**
- **Vue**: Must explicitly declare dependencies in `computed()` and `watch()`
- **Svelte**: `$derived` and `$effect` automatically track what you read

### 3. **Cleaner Class-Based Architecture**
- **Vue**: Functions returning objects with reactive refs
- **Svelte**: Classes with reactive properties and methods

### 4. **Built-in Advanced Features**
- **Vue**: Must implement optimistic updates, retries, cancellation manually
- **Svelte**: Built into the action classes with simple configuration

### 5. **Better TypeScript Integration**
- **Vue**: Complex generic types for refs and computed values
- **Svelte**: Native class properties with automatic type inference

### 6. **Simpler State Management**
- **Vue**: Multiple `ref()`, `reactive()`, `computed()` calls
- **Svelte**: Single class instance with reactive properties

### 7. **Automatic Cleanup**
- **Vue**: Manual `onUnmounted()` calls
- **Svelte**: `$effect` cleanup functions run automatically

### 8. **Performance Benefits**
- **Vue**: Runtime reactivity system with proxy overhead
- **Svelte**: Compile-time optimizations with zero runtime overhead

## Migration Path

### Step 1: Replace Composables with Actions
```typescript
// Before (Vue)
const { contacts, loading, fetchContacts } = useContacts(accountId);

// After (Svelte)
const contactsQuery = new ContactsQuery();
const contacts = contactsQuery.data || [];
const loading = contactsQuery.loading;
```

### Step 2: Combine Related Actions
```typescript
// Before (Vue) - Multiple composables
const contactsState = useContacts(accountId);
const contactForm = useContactForm();
const contactSearch = useContactSearch(accountId);

// After (Svelte) - Single action class
const contacts = new ContactActions(accountId);
```

### Step 3: Leverage Built-in Features
```typescript
// Before (Vue) - Manual implementation
const updateWithOptimism = async (contact, updates) => {
  const original = { ...contact };
  Object.assign(contact, updates);
  
  try {
    await api.updateContact(contact.id, updates);
  } catch (err) {
    Object.assign(contact, original);
    throw err;
  }
};

// After (Svelte) - Built-in optimistic updates
const updateMutation = new MutationAction(apiCall, {
  optimisticUpdate: (params) => ({ ...original, ...params })
});
```

## Conclusion

Svelte 5 Actions provide a superior developer experience compared to Vue 3's Composition API:

- **Cleaner syntax** without `.value` everywhere
- **Automatic reactivity** without manual dependency management
- **Built-in advanced features** like optimistic updates and retries
- **Better performance** with compile-time optimizations
- **Simpler architecture** with class-based patterns
- **Superior TypeScript support** with native class properties

The migration from Vue patterns to Svelte 5 Actions results in less code, better performance, and improved developer experience while maintaining the same functionality.