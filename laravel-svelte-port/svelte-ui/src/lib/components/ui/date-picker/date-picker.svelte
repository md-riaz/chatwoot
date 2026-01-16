<script lang="ts" module>
  import type { DateValue } from '@internationalized/date';
  
  export type DatePickerProps = {
    value?: DateValue;
    placeholder?: string;
    disabled?: boolean;
    readonly?: boolean;
    minValue?: DateValue;
    maxValue?: DateValue;
    class?: string;
  };
</script>

<script lang="ts">
  import { Popover, PopoverTrigger, PopoverContent } from '../popover/index.js';
  import { Button } from '../button/index.js';
  import { Calendar } from '../calendar/index.js';
  import { cn } from '$lib/utils';
  
  let {
    value = $bindable(),
    placeholder = 'Pick a date',
    disabled = false,
    readonly = false,
    minValue,
    maxValue,
    class: className,
    ...restProps
  }: DatePickerProps = $props();
  
  let open = $state(false);
  
  // Close popover when value changes
  $effect(() => {
    if (value !== undefined) {
      open = false;
    }
  });
  
  let displayValue = $derived(
    value
      ? new Intl.DateTimeFormat('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }).format(new Date(value.toString()))
      : placeholder
  );
</script>

<div class={cn('grid gap-2', className)} {...restProps}>
  <Popover bind:open>
    <PopoverTrigger>
      {#snippet child({ props }: { props: any })}
        <Button
          {...props}
          variant="outline"
          class={cn(
            'w-full justify-start text-left font-normal',
            !value && 'text-muted-foreground'
          )}
          {disabled}
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="mr-2 size-4"
          >
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          {displayValue}
        </Button>
      {/snippet}
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" align="start">
      <Calendar
        bind:value
        {minValue}
        {maxValue}
        {disabled}
        {readonly}
      />
    </PopoverContent>
  </Popover>
</div>
