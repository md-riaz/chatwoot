/**
 * TypeScript types for Svelte stores
 */

/**
 * Base store interface
 */
export interface Store<T> {
  current: T;
  loading: boolean | 'idle' | 'loading' | 'success' | 'error';
  error: string | null;
  set: (value: T) => void;
  update: (updater: (current: T) => T) => void;
  reset: () => void;
  setLoading: (loading: boolean | 'idle' | 'loading' | 'success' | 'error') => void;
  setError: (error: string | null) => void;
  clearError: () => void;
  save: () => void;
  clear: () => void;
}

/**
 * Async store interface
 */
export interface AsyncStore<T> extends Store<T> {
  load: () => Promise<T>;
  reload: () => Promise<T>;
}

/**
 * Paginated store state
 */
export interface PaginatedState<T> {
  items: T[];
  page: number;
  perPage: number;
  totalPages: number;
  totalCount: number;
  hasMore: boolean;
}

/**
 * Paginated store interface
 */
export interface PaginatedStore<T> {
  current: PaginatedState<T>;
  loading: boolean;
  error: string | null;
  loadPage: (page?: number) => Promise<PaginatedState<T>>;
  loadMore: () => Promise<PaginatedState<T>>;
  reset: () => void;
}

/**
 * Store with optimistic updates
 */
export interface OptimisticStore<T> extends Store<T> {
  optimisticUpdate: (updater: (current: T) => T, action: () => Promise<void>) => Promise<void>;
  rollback: () => void;
}

/**
 * Action status for tracking async operations
 */
export type ActionStatus = 'idle' | 'pending' | 'success' | 'error';

/**
 * Action state for tracking individual async actions
 */
export interface ActionState {
  status: ActionStatus;
  error: string | null;
  timestamp: number;
}
