<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Input } from '$lib/components/ui/input';
  import { ChevronDown, ChevronLeft, ChevronRight, Calendar } from 'lucide-svelte';
  
  interface Props {
    from?: Date | null;
    to?: Date | null;
    daysNum?: number | null;
    onRangeTypeChange?: (type: 'preset' | 'month' | 'custom') => void;
    onMonthOffsetChange?: (offset: number) => void;
  }
  
  let { 
    from = null, 
    to = null, 
    daysNum = null,
    onRangeTypeChange,
    onMonthOffsetChange
  }: Props = $props();
  
  // Range type state
  let rangeType = $state<'preset' | 'month' | 'custom'>('preset');
  let selectedPreset = $state<'last_7_days' | 'this_month'>('last_7_days');
  let monthOffset = $state(0); // 0 = current month, -1 = previous month, etc.
  let showRangeDropdown = $state(false);
  let showCustomInputs = $state(false);
  
  // Local date state for custom range (ISO format for input[type="date"])
  let customFrom = $state('');
  let customTo = $state('');
  
  // Preset options
  const presetOptions = [
    { value: 'last_7_days', label: 'Last 7 days', days: 6 },
    { value: 'this_month', label: 'This month', days: null }
  ];
  
  // Helper functions for date formatting
  function formatDateForInput(date: Date): string {
    return date.toISOString().split('T')[0];
  }
  
  function parseDateFromInput(dateStr: string): Date {
    return new Date(dateStr + 'T00:00:00');
  }
  
  // Current selection label
  const selectionLabel = $derived.by(() => {
    if (rangeType === 'preset') {
      const preset = presetOptions.find(p => p.value === selectedPreset);
      return preset?.label || 'Last 7 days';
    } else if (rangeType === 'month') {
      const date = new Date();
      date.setMonth(date.getMonth() + monthOffset);
      return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    } else {
      if (customFrom && customTo) {
        const fromDate = new Date(customFrom);
        const toDate = new Date(customTo);
        return `${fromDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${toDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
      }
      return 'Custom range';
    }
  });
  
  // Handle preset selection
  function handlePresetSelect(preset: 'last_7_days' | 'this_month') {
    selectedPreset = preset;
    rangeType = preset === 'this_month' ? 'month' : 'preset';
    showRangeDropdown = false;
    showCustomInputs = false;
    
    if (preset === 'last_7_days') {
      const to = new Date();
      const from = new Date();
      from.setDate(from.getDate() - 6);
      
      updateDates(from, to, 6);
      onRangeTypeChange?.('preset');
    } else {
      const now = new Date();
      const from = new Date(now.getFullYear(), now.getMonth(), 1);
      const to = new Date();
      
      updateDates(from, to, null);
      onRangeTypeChange?.('month');
      onMonthOffsetChange?.(0);
    }
  }
  
  // Handle month navigation
  function handleMonthChange(direction: 'prev' | 'next') {
    const newOffset = direction === 'prev' ? monthOffset - 1 : monthOffset + 1;
    monthOffset = newOffset;
    
    const date = new Date();
    date.setMonth(date.getMonth() + newOffset);
    
    const from = new Date(date.getFullYear(), date.getMonth(), 1);
    const to = newOffset === 0 ? new Date() : new Date(date.getFullYear(), date.getMonth() + 1, 0);
    
    updateDates(from, to, null);
    onMonthOffsetChange?.(newOffset);
  }
  
  // Handle custom date selection
  function handleCustomDates() {
    if (customFrom && customTo) {
      const fromDate = parseDateFromInput(customFrom);
      const toDate = parseDateFromInput(customTo);
      
      if (fromDate <= toDate) {
        rangeType = 'custom';
        updateDates(fromDate, toDate, null);
        onRangeTypeChange?.('custom');
        showRangeDropdown = false;
      }
    }
  }
  
  function handleShowCustomInputs() {
    showCustomInputs = !showCustomInputs;
    showRangeDropdown = false;
    
    // Initialize with current dates if available
    if (from && to) {
      customFrom = formatDateForInput(from);
      customTo = formatDateForInput(to);
    }
  }
  
  // Update parent component dates
  function updateDates(fromDate: Date, toDate: Date, days: number | null) {
    // Emit to parent component
    from = fromDate;
    to = toDate;
    daysNum = days;
  }
  
  // Initialize with default range
  $effect(() => {
    if (!from && !to) {
      handlePresetSelect('last_7_days');
    }
  });
</script>

<div class="flex flex-col gap-2">
  <div class="flex items-center gap-2">
    <!-- Range selector dropdown -->
    <DropdownMenu.Root bind:open={showRangeDropdown}>
      <DropdownMenu.Trigger asChild>
        <Button
          variant="outline"
          size="sm"
          class="min-w-[140px] justify-between"
        >
          <span class="truncate">{selectionLabel}</span>
          <ChevronDown class="ml-2 h-4 w-4 flex-shrink-0" />
        </Button>
      </DropdownMenu.Trigger>
      <DropdownMenu.Content class="w-56">
        <DropdownMenu.Item onclick={() => handlePresetSelect('last_7_days')}>
          Last 7 days
        </DropdownMenu.Item>
        <DropdownMenu.Item onclick={() => handlePresetSelect('this_month')}>
          This month
        </DropdownMenu.Item>
        <DropdownMenu.Separator />
        <DropdownMenu.Item onclick={handleShowCustomInputs}>
          <Calendar class="mr-2 h-4 w-4" />
          Custom range...
        </DropdownMenu.Item>
      </DropdownMenu.Content>
    </DropdownMenu.Root>
    
    <!-- Month navigation (only show for month view) -->
    {#if rangeType === 'month'}
      <div class="flex items-center gap-1">
        <Button
          variant="outline"
          size="sm"
          onclick={() => handleMonthChange('prev')}
          class="p-2"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="sm"
          onclick={() => handleMonthChange('next')}
          disabled={monthOffset >= 0}
          class="p-2"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    {/if}
  </div>
  
  <!-- Custom date inputs (inline, not modal) -->
  {#if showCustomInputs}
    <div class="flex items-center gap-2 p-3 bg-muted rounded-md">
      <Input
        type="date"
        bind:value={customFrom}
        class="w-40"
        placeholder="Start date"
      />
      <span class="text-sm text-muted-foreground">to</span>
      <Input
        type="date"
        bind:value={customTo}
        class="w-40"
        placeholder="End date"
      />
      <Button 
        size="sm" 
        onclick={handleCustomDates} 
        disabled={!customFrom || !customTo}
      >
        Apply
      </Button>
      <Button 
        size="sm" 
        variant="ghost" 
        onclick={() => showCustomInputs = false}
      >
        Cancel
      </Button>
    </div>
  {/if}
</div>