<script lang="ts">
  import { Progress as ProgressPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Props } from './index.js';

  let {
    class: className,
    value = 0,
    max = 100,
    ...restProps
  }: Props = $props();
</script>

<ProgressPrimitive.Root
  class={cn(
    'relative h-4 w-full overflow-hidden rounded-full bg-secondary',
    className
  )}
  {value}
  {max}
  {...restProps}
>
  <div
    class="h-full w-full flex-1 bg-primary transition-all"
    style="transform: translateX(-{100 - (100 * (value ?? 0)) / max}%)"
  />
</ProgressPrimitive.Root>
