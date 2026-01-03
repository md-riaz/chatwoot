<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';
  import { alertVariants, type Props } from './index.js';

  type $$Props = Props & { children?: Snippet };

  let {
    class: className,
    variant = 'default',
    children,
    ...restProps
  }: $$Props = $props();
</script>

<div
  role="alert"
  class={cn(alertVariants({ variant }), className)}
  {...restProps}
>
  {#if children}
    {@render children?.()}
  {/if}
</div>
