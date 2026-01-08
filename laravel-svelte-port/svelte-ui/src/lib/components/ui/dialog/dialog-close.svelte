<script lang="ts">
  import { cn } from '$lib/utils';
  import { Dialog as DialogPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<DialogPrimitive.Close
  class={cn(
    'rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children?.()}
  {/if}
</DialogPrimitive.Close>
