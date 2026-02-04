/**
 * Base Action Pattern using Svelte 5 Runes
 * Provides Vue-like composable functionality with reactive classes
 * 
 * Inspired by Vue's useMutation and useQuery patterns but leveraging
 * Svelte 5's superior reactivity system without .value syntax
 */

import { ApiError, NetworkError } from '$lib/api/errors';

/**
 * Action state interface
 */
export interface ActionState<TData = any, TError = any> {
  data: TData | null;
  loading: boolean;
  error: TError | null;
  success: boolean;
}

/**
 * Action options
 */
export interface ActionOptions<TData = any, TVariables = any> {
  /** Auto-execute on creation */
  immediate?: boolean;
  /** Retry configuration */
  retry?: {
    attempts: number;
    delay: number;
    backoff?: 'linear' | 'exponential';
  };
  /** Success callback */
  onSuccess?: (data: TData, variables: TVariables) => void;
  /** Error callback */
  onError?: (error: any, variables: TVariables) => void;
  /** Finally callback */
  onFinally?: (variables: TVariables) => void;
  /** Transform response data */
  transform?: (data: any) => TData;
}

/**
 * Base Action class using Svelte 5 runes
 * Provides reactive state management for async operations
 * 
 * @example
 * ```typescript
 * class CreateContactAction extends BaseAction<Contact, CreateContactParams> {
 *   constructor(accountId: number) {
 *     super(async (params) => {
 *       return contactsApi.createContact(accountId, params);
 *     });
 *   }
 * }
 * 
 * // Usage in component
 * const createContact = new CreateContactAction(accountId);
 * await createContact.execute({ name: 'John', email: 'john@example.com' });
 * ```
 */
export class BaseAction<TData = any, TVariables = any, TError = any> {
  // Reactive state using $state rune
  data = $state<TData | null>(null);
  loading = $state<boolean>(false);
  error = $state<TError | null>(null);
  
  // Derived state using $derived rune
  success = $derived(this.data !== null && !this.error && !this.loading);
  idle = $derived(!this.loading && !this.error && this.data === null);
  
  // Private state
  private retryCount = $state<number>(0);
  private abortController: AbortController | null = null;
  
  constructor(
    private executor: (variables: TVariables, signal?: AbortSignal) => Promise<TData>,
    private options: ActionOptions<TData, TVariables> = {}
  ) {
    // Auto-execute if immediate option is set
    if (options.immediate) {
      $effect(() => {
        this.execute({} as TVariables);
      });
    }
  }
  
  /**
   * Execute the action with given variables
   */
  async execute(variables: TVariables): Promise<TData | null> {
    // Cancel any ongoing request
    this.cancel();
    
    // Reset state
    this.loading = true;
    this.error = null;
    this.retryCount = 0;
    
    // Create new abort controller
    this.abortController = new AbortController();
    
    try {
      const result = await this.executeWithRetry(variables, this.abortController.signal);
      
      // Transform data if transformer provided
      const transformedData = this.options.transform 
        ? this.options.transform(result)
        : result;
      
      this.data = transformedData;
      this.loading = false;
      
      // Call success callback
      this.options.onSuccess?.(transformedData, variables);
      
      return transformedData;
    } catch (err: any) {
      // Don't set error if request was cancelled
      if (err.name === 'AbortError') {
        return null;
      }
      
      this.error = err;
      this.loading = false;
      
      // Call error callback
      this.options.onError?.(err, variables);
      
      return null;
    } finally {
      // Call finally callback
      this.options.onFinally?.(variables);
      this.abortController = null;
    }
  }
  
  /**
   * Execute with retry logic
   */
  private async executeWithRetry(variables: TVariables, signal: AbortSignal): Promise<TData> {
    const maxAttempts = this.options.retry?.attempts || 1;
    
    for (let attempt = 0; attempt < maxAttempts; attempt++) {
      try {
        return await this.executor(variables, signal);
      } catch (err: any) {
        // Don't retry if request was cancelled
        if (signal.aborted || err.name === 'AbortError') {
          throw err;
        }
        
        // Don't retry validation errors (4xx)
        if (err instanceof ApiError && err.status >= 400 && err.status < 500) {
          throw err;
        }
        
        // If this is the last attempt, throw the error
        if (attempt === maxAttempts - 1) {
          throw err;
        }
        
        // Wait before retry
        const delay = this.calculateRetryDelay(attempt);
        await this.sleep(delay);
        this.retryCount++;
      }
    }
    
    throw new Error('Max retry attempts reached');
  }
  
  /**
   * Calculate retry delay with backoff
   */
  private calculateRetryDelay(attempt: number): number {
    const baseDelay = this.options.retry?.delay || 1000;
    const backoff = this.options.retry?.backoff || 'linear';
    
    switch (backoff) {
      case 'exponential':
        return baseDelay * Math.pow(2, attempt);
      case 'linear':
      default:
        return baseDelay * (attempt + 1);
    }
  }
  
  /**
   * Sleep utility
   */
  private sleep(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  
  /**
   * Cancel ongoing request
   */
  cancel(): void {
    if (this.abortController) {
      this.abortController.abort();
      this.abortController = null;
    }
  }
  
  /**
   * Reset action state
   */
  reset(): void {
    this.cancel();
    this.data = null;
    this.error = null;
    this.loading = false;
    this.retryCount = 0;
  }
  
  /**
   * Mutate data without re-executing
   */
  mutate(data: TData | null): void {
    this.data = data;
    this.error = null;
  }
  
  /**
   * Get current state snapshot
   */
  getState(): ActionState<TData, TError> {
    return {
      data: this.data,
      loading: this.loading,
      error: this.error,
      success: this.success
    };
  }
}

/**
 * Query Action for data fetching (read operations)
 * Extends BaseAction with caching and refetch capabilities
 */
export class QueryAction<TData = any, TVariables = any> extends BaseAction<TData, TVariables> {
  private lastVariables: TVariables | null = null;
  private cacheKey: string | null = null;
  
  constructor(
    executor: (variables: TVariables, signal?: AbortSignal) => Promise<TData>,
    options: ActionOptions<TData, TVariables> & {
      /** Cache key generator */
      cacheKey?: (variables: TVariables) => string;
      /** Cache duration in ms */
      cacheDuration?: number;
    } = {}
  ) {
    super(executor, options);
  }
  
  /**
   * Refetch with last used variables
   */
  async refetch(): Promise<TData | null> {
    if (this.lastVariables === null) {
      throw new Error('Cannot refetch: no previous variables');
    }
    return this.execute(this.lastVariables);
  }
  
  /**
   * Execute and cache variables
   */
  async execute(variables: TVariables): Promise<TData | null> {
    this.lastVariables = variables;
    return super.execute(variables);
  }
}

/**
 * Mutation Action for data modification (write operations)
 * Extends BaseAction with optimistic updates
 */
export class MutationAction<TData = any, TVariables = any> extends BaseAction<TData, TVariables> {
  private optimisticData: TData | null = null;
  
  constructor(
    executor: (variables: TVariables, signal?: AbortSignal) => Promise<TData>,
    options: ActionOptions<TData, TVariables> & {
      /** Optimistic update function */
      optimisticUpdate?: (variables: TVariables) => TData;
      /** Rollback function for failed optimistic updates */
      onRollback?: (variables: TVariables) => void;
    } = {}
  ) {
    super(executor, options);
  }
  
  /**
   * Execute with optimistic update
   */
  async execute(variables: TVariables): Promise<TData | null> {
    // Apply optimistic update if provided
    if (this.options.optimisticUpdate) {
      this.optimisticData = this.data;
      this.data = this.options.optimisticUpdate(variables);
    }
    
    try {
      const result = await super.execute(variables);
      this.optimisticData = null;
      return result;
    } catch (err) {
      // Rollback optimistic update on error
      if (this.optimisticData !== null) {
        this.data = this.optimisticData;
        this.optimisticData = null;
        (this.options as any).onRollback?.(variables);
      }
      throw err;
    }
  }
}

/**
 * Create a query action (for data fetching)
 */
export function createQuery<TData = any, TVariables = any>(
  executor: (variables: TVariables, signal?: AbortSignal) => Promise<TData>,
  options?: ActionOptions<TData, TVariables>
): QueryAction<TData, TVariables> {
  return new QueryAction(executor, options);
}

/**
 * Create a mutation action (for data modification)
 */
export function createMutation<TData = any, TVariables = any>(
  executor: (variables: TVariables, signal?: AbortSignal) => Promise<TData>,
  options?: ActionOptions<TData, TVariables>
): MutationAction<TData, TVariables> {
  return new MutationAction(executor, options);
}