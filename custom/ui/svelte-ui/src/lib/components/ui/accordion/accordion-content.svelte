<script lang="ts">
  import { Accordion as AccordionPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<AccordionPrimitive.Content
  class={cn(
    'overflow-hidden text-sm transition-all data-[state=closed]:animate-accordion-up data-[state=open]:animate-accordion-down',
    className
  )}
  {...restProps}
>
  <div class="pb-4 pt-0">
    {#if children}
      {@render children()}
    {/if}
  </div>
</AccordionPrimitive.Content>
