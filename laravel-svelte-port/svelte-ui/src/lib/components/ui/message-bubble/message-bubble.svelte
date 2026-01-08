<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    variant?: 'incoming' | 'outgoing' | 'private' | 'bot';
    children?: Snippet;
  };

  let { class: className, variant = 'incoming', children, ...restProps }: Props = $props();
</script>

<div
  class={cn(
    'flex gap-2',
    variant === 'outgoing' ? 'flex-row-reverse' : 'flex-row',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
