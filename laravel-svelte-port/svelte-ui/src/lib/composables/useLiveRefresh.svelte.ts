/**
 * Live Refresh Composable
 * Provides auto-refresh functionality for live data updates
 * Matches Vue useLiveRefresh.js functionality
 */

interface LiveRefreshOptions {
  interval?: number;
  immediate?: boolean;
  onError?: (error: Error) => void;
}

export function useLiveRefresh(
  callback: () => Promise<void> | void,
  options: LiveRefreshOptions = {}
) {
  const {
    interval = 60000, // 60 seconds default (matches Vue)
    immediate = false,
    onError
  } = options;

  let timeoutId = $state<number | null>(null);
  let isActive = $state(false);
  let lastRefresh = $state<Date | null>(null);

  async function executeCallback() {
    try {
      await callback();
      lastRefresh = new Date();
    } catch (error) {
      console.error('Live refresh callback failed:', error);
      if (onError && error instanceof Error) {
        onError(error);
      }
    }
  }

  function startRefetching() {
    if (isActive) return; // Already running
    
    isActive = true;
    
    // Execute immediately if requested
    if (immediate) {
      executeCallback();
    }
    
    // Set up recursive timeout
    function scheduleNext() {
      timeoutId = setTimeout(async () => {
        if (!isActive) return; // Check if still active
        
        await executeCallback();
        
        if (isActive) {
          scheduleNext(); // Schedule next execution
        }
      }, interval);
    }
    
    scheduleNext();
  }

  function stopRefetching() {
    isActive = false;
    if (timeoutId) {
      clearTimeout(timeoutId);
      timeoutId = null;
    }
  }

  function restartRefetching() {
    stopRefetching();
    startRefetching();
  }

  // Calculate time until next refresh
  const timeUntilNext = $derived(() => {
    if (!isActive || !lastRefresh) return null;
    const elapsed = Date.now() - lastRefresh.getTime();
    const remaining = Math.max(0, interval - elapsed);
    return remaining;
  });

  // Cleanup on component unmount
  $effect(() => {
    return () => {
      stopRefetching();
    };
  });

  return {
    startRefetching,
    stopRefetching,
    restartRefetching,
    get isActive() { return isActive; },
    get lastRefresh() { return lastRefresh; },
    get timeUntilNext() { return timeUntilNext; }
  };
}

/**
 * Utility hook for components that need live refresh with loading states
 */
export function useLiveRefreshWithLoading(
  callback: () => Promise<void>,
  options: LiveRefreshOptions = {}
) {
  let isLoading = $state(false);
  let error = $state<string | null>(null);

  const wrappedCallback = async () => {
    isLoading = true;
    error = null;
    try {
      await callback();
    } catch (err) {
      error = err instanceof Error ? err.message : 'Unknown error';
      throw err;
    } finally {
      isLoading = false;
    }
  };

  const liveRefresh = useLiveRefresh(wrappedCallback, {
    ...options,
    onError: (err) => {
      error = err.message;
      options.onError?.(err);
    }
  });

  return {
    ...liveRefresh,
    get isLoading() { return isLoading; },
    get error() { return error; },
    clearError: () => { error = null; }
  };
}