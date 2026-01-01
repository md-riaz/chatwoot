<script lang="ts">
  import { cn } from '$lib/utils';
  import { badgeVariants, type Props } from './index.js';
  import type { Snippet } from 'svelte';

  type $$Props = Props & { children?: Snippet };

  let {
    class: className,
    variant = 'default',
    children,
    ...restProps
  }: $$Props = $props();
</script>

<div class={cn(badgeVariants({ variant }), className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</div>
