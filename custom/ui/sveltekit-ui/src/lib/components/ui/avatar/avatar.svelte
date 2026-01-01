<script lang="ts">
  import { cn } from '$lib/utils';
  import { avatarVariants, type Props } from './index.js';
  import type { Snippet } from 'svelte';

  type $$Props = Props & { children?: Snippet };

  let {
    class: className,
    size = 'default',
    children,
    ...restProps
  }: $$Props = $props();
</script>

<span class={cn(avatarVariants({ size }), className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</span>
