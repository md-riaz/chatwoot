<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { DateInput } from '$lib/components/custom';
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
  let showCustomDates = $state(false);
  
  // Local date state for custom range
  let customFrom = $state('');
  let customTo = $state('');
  
  // Preset options
  const presetOptions = [
    { value: 'last_7_days', label: 'Last 7 days', days: 6 },
    { value: 'this_month', label: 'This month', days: null }
  ];
  
  // Current selection label
  const selectionLabel = $derived(() => {
    if (rangeType === 'preset') {
      const preset = presetOptions.find(p => p.value === selectedPreset);
      return preset?.label || 'Last 7 days';
    } else if (rangeType === 'month') {
      const date = new Date();
      date.setMonth(date.getMonth() + monthOffset);
      return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    } else {
      if (customFrom && customTo) {
        return `${customFrom} to ${customTo}`;
      }
      return 'Custom range';
    }
  });
  
  // Handle preset selection
  function handlePresetSelect(preset: 'last_7_days' | 'this_month') {
    selectedPreset = preset;
    rangeType = preset === 'this_month' ? 'month' : 'preset';
    showRangeDropdown = false;
    
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
      const fromDate = new Date(customFrom);
      const toDate = new Date(customTo);
      
      if (fromDate <= toDate) {
        rangeType = 'custom';
        updateDates(fromDate, toDate, null);
        onRangeTypeChange?.('custom');
        showCustomDates = false;
      }
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
      <DropdownMenu.Item on:click={() => handlePresetSelect('last_7_days')}>
        Last 7 days
      </DropdownMenu.Item>
      <DropdownMenu.Item on:click={() => handlePresetSelect('this_month')}>
        This month
      </DropdownMenu.Item>
      <DropdownMenu.Separator />
      <DropdownMenu.Item on:click={() => showCustomDates = true}>
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

<!-- Custom date range modal/popup -->
{#if showCustomDates}
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
      <h3 class="text-lg font-semibold mb-4">Select Custom Date Range</h3>
      
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">From Date</label>
          <DateInput bind:value={customFrom} placeholder="Start date" />
        </div>
        
        <div>
          <label class="block text-sm font-medium mb-1">To Date</label>
          <DateInput bind:value={customTo} placeholder="End date" />
        </div>
      </div>
      
      <div class="flex justify-end gap-2 mt-6">
        <Button
          variant="outline"
          onclick={() => showCustomDates = false}
        >
          Cancel
        </Button>
        <Button
          onclick={handleCustomDates}
          disabled={!customFrom || !customTo}
        >
          Apply
        </Button>
      </div>
    </div>
  </div>
{/if}