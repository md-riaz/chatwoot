<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Popover, PopoverTrigger, PopoverContent } from '$lib/components/ui/popover';
  import { Calendar } from '$lib/components/ui/calendar';
  import { parseDate, type DateValue } from '@internationalized/date';
  
  let {
    value = $bindable<string>(''),
    disabled = false,
    readonly = false
  } = $props<{
    value?: string;
    disabled?: boolean;
    readonly?: boolean;
  }>();
  
  let dateValue = $state<DateValue | undefined>(undefined);
  let isOpen = $state(false);
  
  // Parse string to DateValue when value changes
  $effect(() => {
    if (value) {
      try {
        const parsed = parseDate(value);
        if (parsed.toString() !== dateValue?.toString()) {
          dateValue = parsed;
        }
      } catch {
        dateValue = undefined;
      }
    } else {
      dateValue = undefined;
    }
  });
  
  // Update string value when DateValue changes
  $effect(() => {
    const newStr = dateValue?.toString() || '';
    if (newStr !== value) {
      value = newStr;
    }
  });
  
  // Format date for display
  function formatDate(dateStr: string): string {
    if (!dateStr) return '';
    try {
      const dv = parseDate(dateStr);
      return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).format(new Date(dv.toString()));
    } catch {
      return dateStr;
    }
  }
</script>

<Popover bind:open={isOpen}>
  <PopoverTrigger>
    {#snippet child({ props }: { props: any })}
      <Button
        {...props}
        variant="outline"
        class="w-full justify-start text-left font-normal"
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
        {value ? formatDate(value) : 'Select a date'}
      </Button>
    {/snippet}
  </PopoverTrigger>
  <PopoverContent class="w-auto p-0" align="start">
    <Calendar
      type="single"
      bind:value={dateValue as any}
      {disabled}
      {readonly}
    />
  </PopoverContent>
</Popover>
