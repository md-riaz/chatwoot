<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Props } from './index.js';

  type $$Props = Props & { ref?: HTMLInputElement | null };

  let {
    class: className,
    type = 'text',
    value = $bindable(''),
    ref = $bindable(null),
    ...restProps
  }: $$Props = $props();
</script>

<input
  bind:this={ref}
  bind:value
  {type}
  class={cn(
    'border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
  {...restProps}
/>
