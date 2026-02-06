<script lang="ts">
  import { AlertTriangle, RefreshCw } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  
  interface Props {
    error: string | null;
    onRetry?: () => void;
    children?: any;
  }
  
  let { error, onRetry, children }: Props = $props();
</script>

{#if error}
  <div class="flex flex-col items-center justify-center p-8 text-center bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
    <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-4">
      <AlertTriangle class="w-8 h-8 text-red-500 dark:text-red-400" />
    </div>
    
    <h3 class="text-lg font-medium text-red-900 dark:text-red-100 mb-2">
      Something went wrong
    </h3>
    
    <p class="text-sm text-red-700 dark:text-red-300 mb-4 max-w-md">
      {error}
    </p>
    
    {#if onRetry}
      <Button
        onclick={onRetry}
        variant="outline"
        size="sm"
        class="border-red-300 text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30"
      >
        <RefreshCw class="w-4 h-4 mr-2" />
        Try Again
      </Button>
    {/if}
  </div>
{:else if children}
  {@render children()}
{/if}