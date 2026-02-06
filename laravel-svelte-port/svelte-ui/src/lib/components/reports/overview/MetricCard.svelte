<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import LiveBadge from '../shared/LiveBadge.svelte';
  import { cn } from '$lib/utils';
  
  interface Props {
    header: string;
    isLive?: boolean;
    isLoading?: boolean;
    loadingMessage?: string;
    class?: string;
    children?: any;
    control?: any;
  }
  
  let { 
    header, 
    isLive = false,
    isLoading = false,
    loadingMessage = '',
    class: className,
    children,
    control
  }: Props = $props();
</script>

<div class={cn(
  'flex flex-col m-0.5 px-6 py-5 rounded-xl flex-grow text-slate-900 dark:text-slate-100 shadow outline-1 outline outline-slate-200 dark:outline-slate-700 bg-white dark:bg-slate-900 min-h-[10rem]',
  className
)}>
  <div class="card-header grid w-full mb-6 grid-cols-[repeat(auto-fit,minmax(max-content,50%))] gap-y-2">
    <div class="flex items-center gap-2 flex-row">
      <h5 class="mb-0 text-slate-900 dark:text-slate-100 font-medium text-lg">
        {header}
      </h5>
      {#if isLive}
        <LiveBadge />
      {/if}
    </div>
    {#if control}
      <div class="flex flex-row items-center justify-end gap-2">
        {@render control()}
      </div>
    {/if}
  </div>
  
  {#if !isLoading}
    <div class="card-body max-w-full w-full ml-auto mr-auto justify-between flex">
      {#if children}
        {@render children()}
      {/if}
    </div>
  {:else}
    <div class="items-center flex text-base justify-center px-12 py-6">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-slate-600 dark:border-slate-400 mr-3"></div>
      <span class="text-slate-600 dark:text-slate-400">
        {loadingMessage}
      </span>
    </div>
  {/if}
</div>