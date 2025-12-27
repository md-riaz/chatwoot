<script lang="ts">
  import { Select as SelectPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = SelectPrimitive.ItemProps & {
    class?: string;
    children?: Snippet;
  };

  let { class: className, value, children, ...restProps }: Props = $props();
</script>

<SelectPrimitive.Item
  {value}
  class={cn(
    'relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50',
    className
  )}
  {...restProps}
>
  <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
    <SelectPrimitive.ItemIndicator>
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4"><polyline points="20 6 9 17 4 12"/></svg>
    </SelectPrimitive.ItemIndicator>
  </span>
  {#if children}
    {@render children()}
  {/if}
</SelectPrimitive.Item>
