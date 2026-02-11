<script lang="ts">
  /**
   * DateRangePicker Component
   * Simple date range picker for reports filtering
   * Vue Parity: Replaces DateRangePicker from Vue dashboard
   */
  import { createEventDispatcher } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import { Calendar as CalendarIcon } from 'lucide-svelte';
  import { cn } from '$lib/utils';

  interface Props {
    class?: string;
  }

  let { class: className }: Props = $props();

  const dispatch = createEventDispatcher<{
    change: { from: number; to: number };
  }>();

  // Default to last 30 days
  const now = new Date();
  const thirtyDaysAgo = new Date(now);
  thirtyDaysAgo.setDate(now.getDate() - 30);

  let fromDate = $state<Date>(thirtyDaysAgo);
  let toDate = $state<Date>(now);

  // Format date for display
  function formatDate(date: Date): string {
    return new Intl.DateTimeFormat('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    }).format(date);
  }

  const displayValue = $derived(
    `${formatDate(fromDate)} - ${formatDate(toDate)}`
  );

  // Emit change event when dates change
  function handleFromChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const newDate = new Date(input.value);
    if (!isNaN(newDate.getTime())) {
      fromDate = newDate;
      emitChange();
    }
  }

  function handleToChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const newDate = new Date(input.value);
    if (!isNaN(newDate.getTime())) {
      toDate = newDate;
      emitChange();
    }
  }

  function emitChange() {
    // Convert to Unix timestamps (seconds)
    const from = Math.floor(fromDate.getTime() / 1000);
    const to = Math.floor(toDate.getTime() / 1000);
    dispatch('change', { from, to });
  }

  // Format date for input value (YYYY-MM-DD)
  function formatInputDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // Emit initial values on mount
  $effect(() => {
    emitChange();
  });
</script>

<div class={cn('flex items-center gap-2', className)}>
  <div class="relative">
    <Button
      variant="outline"
      class={cn(
        'w-[280px] justify-start text-left font-normal',
        !fromDate && !toDate && 'text-muted-foreground'
      )}
      type="button"
    >
      <CalendarIcon class="mr-2 h-4 w-4" />
      {displayValue}
    </Button>
    
    <!-- Hidden date inputs for native date picker -->
    <div class="absolute inset-0 opacity-0 cursor-pointer">
      <input
        type="date"
        value={formatInputDate(fromDate)}
        onchange={handleFromChange}
        class="absolute left-0 top-0 w-1/2 h-full cursor-pointer"
        aria-label="From date"
      />
      <input
        type="date"
        value={formatInputDate(toDate)}
        onchange={handleToChange}
        class="absolute right-0 top-0 w-1/2 h-full cursor-pointer"
        aria-label="To date"
      />
    </div>
  </div>
</div>
