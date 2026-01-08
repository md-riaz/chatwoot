<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    mode?: 'reply' | 'private' | 'note';
    children?: Snippet;
  };

  let { class: className, mode = 'reply', children, ...restProps }: Props = $props();
</script>

<div
  class={cn(
    'rounded-lg border bg-card p-3',
    mode === 'private' && 'border-warning bg-warning/5',
    mode === 'note' && 'border-info bg-info/5',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
