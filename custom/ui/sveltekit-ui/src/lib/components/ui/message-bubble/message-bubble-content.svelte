<script lang="ts">
  import { cn } from '$lib/utils';
  import { messageBubbleVariants, type Variant } from './index.js';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    variant?: Variant;
    children?: Snippet;
  };

  let { class: className, variant = 'incoming', children, ...restProps }: Props = $props();
</script>

<div class={cn(messageBubbleVariants({ variant }), className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</div>
