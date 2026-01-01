<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button as ButtonPrimitive } from 'bits-ui';
  import { buttonVariants, type Events, type Props } from './index.js';

  type $$Props = Props;
  type $$Events = Events;

  let ref: ButtonPrimitive.Root | null = null;

  let {
    class: className,
    variant = 'default',
    size = 'default',
    children,
    ...restProps
  }: $$Props = $props();
</script>

<ButtonPrimitive.Root
  bind:this={ref}
  class={cn(buttonVariants({ variant, size, className: className ?? '' }))}
  {...restProps}
>
  {@render children?.()}
</ButtonPrimitive.Root>
