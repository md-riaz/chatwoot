<script lang="ts">
  import { Button as ButtonPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import { buttonVariants, type Props, type Events } from './index.js';

  type $$Props = Props;
  type $$Events = Events;

  let {
    class: className,
    variant = 'default',
    size = 'default',
    ref = $bindable(null),
    ...restProps
  }: $$Props = $props();
</script>

<ButtonPrimitive.Root
  bind:ref
  class={cn(buttonVariants({ variant, size, className }))}
  {...restProps}
/>
