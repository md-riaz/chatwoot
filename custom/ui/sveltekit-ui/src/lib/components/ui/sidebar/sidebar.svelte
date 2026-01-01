<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    collapsed?: boolean;
    children?: Snippet;
  };

  let { class: className, collapsed = false, children, ...restProps }: Props = $props();
</script>

<aside
  class={cn(
    'flex flex-col h-full bg-sidebar border-r border-sidebar-border transition-all duration-300',
    collapsed ? 'w-16' : 'w-64',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</aside>
