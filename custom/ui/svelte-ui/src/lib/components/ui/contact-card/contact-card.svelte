<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    variant?: 'default' | 'compact' | 'detailed';
    children?: Snippet;
  };

  let { class: className, variant = 'default', children, ...restProps }: Props = $props();
</script>

<div
  class={cn(
    'rounded-lg border bg-card text-card-foreground',
    variant === 'compact' ? 'p-3' : 'p-4',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
