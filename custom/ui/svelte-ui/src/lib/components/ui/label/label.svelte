<script lang="ts">
  import { Label as LabelPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Props } from './index.js';
  import type { Snippet } from 'svelte';

  type $$Props = Props & { children?: Snippet };

  let {
    class: className,
    children,
    ...restProps
  }: $$Props = $props();
</script>

<LabelPrimitive.Root
  class={cn(
    'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</LabelPrimitive.Root>
