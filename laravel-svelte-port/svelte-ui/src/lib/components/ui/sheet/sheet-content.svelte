<script lang="ts">
  import { Dialog as SheetPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { ComponentProps } from 'svelte';
  
  type Side = 'top' | 'right' | 'bottom' | 'left';
  
  let {
    ref = $bindable(null),
    class: className,
    side = 'right' as Side,
    portalProps,
    children,
    ...restProps
  }: SheetPrimitive.ContentProps & {
    portalProps?: ComponentProps<typeof SheetPrimitive.Portal>;
    side?: Side;
    children?: import('svelte').Snippet;
  } = $props();
  
  const sideClasses: Record<Side, string> = {
    top: 'inset-x-0 top-0 border-b',
    right: 'inset-y-0 right-0 border-l',
    bottom: 'inset-x-0 bottom-0 border-t',
    left: 'inset-y-0 left-0 border-r'
  };
</script>

<SheetPrimitive.Portal {...portalProps}>
  <SheetPrimitive.Overlay />
  <SheetPrimitive.Content
    bind:ref
    class={cn(
      'fixed z-50 bg-background',
      'w-[var(--sidebar-width)] p-0',
      sideClasses[side],
      className
    )}
    {...restProps}
  >
    {@render children?.()}
    <SheetPrimitive.Close class="absolute end-4 top-4 rounded-xs opacity-70 transition-opacity hover:opacity-100">
      <span class="sr-only">Close</span>
    </SheetPrimitive.Close>
  </SheetPrimitive.Content>
</SheetPrimitive.Portal>

