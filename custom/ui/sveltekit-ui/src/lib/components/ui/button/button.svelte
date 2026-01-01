<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button as ButtonPrimitive } from 'bits-ui';
  import { buttonVariants, type Events, type Props } from './index.js';

  type $$Props = Props;
  type $$Events = Events;

  let {
    class: className,
    variant = 'default',
    size = 'default',
    ref = $bindable(null),
    children,
    ...restProps
  }: $$Props = $props();
</script>

<ButtonPrimitive.Root
  bind:ref
  class={cn(buttonVariants({ variant, size, className }))}
  {...restProps}
>
  {@render children?.()}
</ButtonPrimitive.Root>
