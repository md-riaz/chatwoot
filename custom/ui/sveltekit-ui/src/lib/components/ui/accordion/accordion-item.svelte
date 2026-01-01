<script lang="ts">
  import { cn } from '$lib/utils';
  import { Accordion as AccordionPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  type Props = AccordionPrimitive.ItemProps & {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<AccordionPrimitive.Item class={cn('border-b', className)} {...restProps}>
  {#if children}
    {@render children?.()}
  {/if}
</AccordionPrimitive.Item>
