/**
 * Base store pattern using Svelte 5 runes
 * Replaces Vuex/Pinia with reactive Svelte stores
 */

import { loadFromStorage, saveToStorage, clearStorage } from './persistence';

/**
 * Store options
 */
export interface StoreOptions {
  /** LocalStorage key for persistence */
  persist?: string;
  /** Auto-save to localStorage on state changes */
  autoSave?: boolean;
}

/**
 * Loading state type
 */
export type LoadingState = boolean | 'idle' | 'loading' | 'success' | 'error';

/**
 * Create a reactive store with Svelte 5 runes
 * 
 * @example
 * ```typescript
 * interface User {
 *   id: number;
 *   name: string;
 * }
 * 
 * const userStore = createStore<User | null>(null, {
 *   persist: 'current_user',
 *   autoSave: true
 * });
 * 
 * // In component
 * const { current, loading } = userStore;
 * console.log(current); // Reactive value
 * ```
 */
export function createStore<T>(
  initialState: T,
  options: StoreOptions = {}
) {
  // Load persisted state if available
  const persistedState = options.persist 
    ? loadFromStorage<T>(options.persist) 
    : null;
  
  // Initialize reactive state
  let state = $state<T>(persistedState ?? initialState);
  let loading = $state<LoadingState>(false);
  let error = $state<string | null>(null);
  
  // Auto-save to localStorage when state changes
  if (options.persist && options.autoSave) {
    $effect(() => {
      saveToStorage(options.persist!, state);
    });
  }
  
  return {
    // Getters - reactive values
    get current() { 
      return state; 
    },
    
    get loading() { 
      return loading; 
    },
    
    get error() { 
      return error; 
    },
    
    // Setters - mutation methods
    set(newState: T) { 
      state = newState;
      error = null;
    },
    
    update(updater: (current: T) => T) { 
      state = updater(state);
      error = null;
    },
    
    reset() {
      state = initialState;
      error = null;
      loading = false;
    },
    
    // Loading state management
    setLoading(isLoading: LoadingState) {
      loading = isLoading;
    },
    
    setError(err: string | null) {
      error = err;
      loading = false;
    },
    
    clearError() {
      error = null;
    },
    
    // Persistence methods
    save() {
      if (options.persist) {
        saveToStorage(options.persist, state);
      }
    },
    
    clear() {
      state = initialState;
      error = null;
      loading = false;
      if (options.persist) {
        clearStorage(options.persist);
      }
    }
  };
}

/**
 * Create a derived store that computes from another store
 * 
 * @example
 * ```typescript
 * const userStore = createStore({ name: 'John', age: 30 });
 * const userNameStore = createDerivedStore(() => userStore.current.name);
 * ```
 */
export function createDerivedStore<T>(compute: () => T) {
  const value = $derived(compute());
  
  return {
    get current() {
      return value;
    }
  };
}

/**
 * Create an async store for handling API calls
 * 
 * @example
 * ```typescript
 * const usersStore = createAsyncStore<User[]>(
 *   [],
 *   async () => api.get('users').json()
 * );
 * 
 * await usersStore.load();
 * ```
 */
export function createAsyncStore<T>(
  initialState: T,
  fetcher: () => Promise<T>,
  options: StoreOptions = {}
) {
  const store = createStore<T>(initialState, options);
  
  return {
    ...store,
    
    async load() {
      store.setLoading(true);
      store.clearError();
      
      try {
        const data = await fetcher();
        store.set(data);
        store.setLoading(false);
        return data;
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Failed to load data';
        store.setError(message);
        throw err;
      }
    },
    
    async reload() {
      return this.load();
    }
  };
}

/**
 * Create a paginated store for list data
 */
export interface PaginatedState<T> {
  items: T[];
  page: number;
  perPage: number;
  totalPages: number;
  totalCount: number;
  hasMore: boolean;
}

export function createPaginatedStore<T>(
  fetcher: (page: number, perPage: number) => Promise<PaginatedState<T>>,
  initialPerPage: number = 20
) {
  let state = $state<PaginatedState<T>>({
    items: [],
    page: 1,
    perPage: initialPerPage,
    totalPages: 0,
    totalCount: 0,
    hasMore: false
  });
  
  let loading = $state(false);
  let error = $state<string | null>(null);
  
  return {
    get current() {
      return state;
    },
    
    get loading() {
      return loading;
    },
    
    get error() {
      return error;
    },
    
    async loadPage(page: number = 1) {
      loading = true;
      error = null;
      
      try {
        const data = await fetcher(page, state.perPage);
        state = data;
        loading = false;
        return data;
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Failed to load page';
        error = message;
        loading = false;
        throw err;
      }
    },
    
    async loadMore() {
      if (!state.hasMore || loading) return;
      
      loading = true;
      error = null;
      
      try {
        const nextPage = state.page + 1;
        const data = await fetcher(nextPage, state.perPage);
        state = {
          ...data,
          items: [...state.items, ...data.items]
        };
        loading = false;
        return data;
      } catch (err) {
        const message = err instanceof Error ? err.message : 'Failed to load more';
        error = message;
        loading = false;
        throw err;
      }
    },
    
    reset() {
      state = {
        items: [],
        page: 1,
        perPage: initialPerPage,
        totalPages: 0,
        totalCount: 0,
        hasMore: false
      };
      error = null;
      loading = false;
    }
  };
}
