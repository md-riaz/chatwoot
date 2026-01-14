<script lang="ts">
  import { Checkbox as CheckboxPrimitive } from 'bits-ui';
  import { cn } from '$lib/utils';
  import type { Props } from './index.js';

  type $$Props = Props;

  let {
    class: className,
    checked = $bindable(false),
    ...restProps
  }: $$Props = $props();
</script>

<CheckboxPrimitive.Root
  bind:checked={checked as any}
  class={cn(
    'peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground',
    className
  )}
  {...restProps}
>
  <!-- CheckboxPrimitive.Indicator removed - not exported in current bits-ui version -->
  <!-- Show indicator when checked using conditional rendering -->
  {#if checked === 'indeterminate'}
    <div class="flex items-center justify-center text-current">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="h-3 w-3"
      >
        <line x1="5" y1="12" x2="19" y2="12" />
      </svg>
    </div>
  {:else if checked}
    <div class="flex items-center justify-center text-current">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="h-3 w-3"
      >
        <polyline points="20 6 9 17 4 12" />
      </svg>
    </div>
  {/if}
</CheckboxPrimitive.Root>
