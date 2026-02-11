<script lang="ts">
  import { onMount } from 'svelte';
  import { useLiveRefresh } from '$lib/composables/useLiveRefresh.svelte';
  import MetricCard from '../overview/MetricCard.svelte';
  import BaseHeatmap from './BaseHeatmap.svelte';
  import HeatmapDateRangeSelector from './HeatmapDateRangeSelector.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Download, ChevronDown } from 'lucide-svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  
  interface Props {
    metric: string;
    title: string;
    downloadTitle: string;
    storeGetter: 'accountConversationHeatmapData' | 'accountResolutionHeatmapData';
    storeAction: 'fetchAccountConversationHeatmap' | 'fetchAccountResolutionHeatmap';
    downloadAction?: string;
    uiFlagKey: string;
    colorScheme?: 'blue' | 'green';
  }
  
  let {
    metric,
    title,
    downloadTitle,
    storeGetter,
    storeAction,
    downloadAction = '',
    uiFlagKey,
    colorScheme = 'blue'
  }: Props = $props();
  
  // Get reactive data from store
  const uiFlags = $derived(reportsStore.overviewUIFlags);
  const heatmapData = $derived(reportsStore[storeGetter]);
  
  // Date range state
  let selectedFrom = $state<Date | null>(null);
  let selectedTo = $state<Date | null>(null);
  let selectedDaysBefore = $state<number | null>(null);
  let selectedInbox = $state<any>(null);
  let isMonthFilter = $state(false);
  let currentMonthOffset = $state(0);
  
  // Mock inboxes data - TODO: Get from inboxes store
  let inboxes = $state<Array<{ id: number; name: string }>>([]);
  let showInboxDropdown = $state(false);
  
  // Computed values
  const selectedRange = $derived.by(() => {
    if (!selectedFrom || !selectedTo) return null;
    return { from: selectedFrom, to: selectedTo };
  });
  
  const numberOfRows = $derived.by(() => {
    const range = selectedRange;
    if (!range) return 7;
    const diffTime = range.to.getTime() - range.from.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return Math.min(diffDays + 1, 14); // Max 14 days for performance
  });
  
  const selectedInboxFilter = $derived(
    !selectedInbox ? { label: 'All Inboxes' } : { label: selectedInbox.name }
  );
  
  const isLoading = $derived(
    // @ts-ignore - Dynamic property access
    uiFlags[uiFlagKey] || false
  );
  
  // Resolve active range for live refresh alignment
  function resolveActiveRange() {
    if (isMonthFilter && currentMonthOffset === 0) {
      const now = new Date();
      const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
      return {
        from: monthStart,
        to: now
      };
    }
    
    if (!isMonthFilter && selectedDaysBefore !== null) {
      const to = new Date();
      const from = new Date();
      from.setDate(from.getDate() - selectedDaysBefore);
      return { from, to };
    }
    
    return selectedRange;
  }
  
  // Fetch heatmap data
  async function fetchHeatmapData() {
    if (isLoading) return;
    
    const range = resolveActiveRange();
    if (!range) return;
    
    console.log('🔄 BaseHeatmapContainer: Fetching heatmap data', {
      metric,
      range,
      selectedInbox
    });
    
    const params = {
      metric,
      from: Math.floor(range.from.getTime() / 1000),
      to: Math.floor(range.to.getTime() / 1000),
      groupBy: 'hour',
      businessHours: false,
      ...(selectedInbox && {
        type: 'inbox',
        id: selectedInbox.id
      })
    };
    
    console.log('📊 Heatmap API params:', params);
    
    // @ts-ignore - Dynamic method access
    await reportsStore[storeAction](params);
    
    console.log('✅ Heatmap data fetched successfully');
  }
  
  // Download heatmap data
  async function downloadHeatmapData() {
    const range = resolveActiveRange();
    if (!range) return;
    
    const shouldUseBackendDownload = !isMonthFilter && !selectedInbox && downloadAction;
    
    if (shouldUseBackendDownload) {
      // Use backend CSV download
      await reportsStore.downloadAccountConversationHeatmap({
        daysBefore: selectedDaysBefore || 6,
        to: Math.floor(range.to.getTime() / 1000)
      });
    } else {
      // Generate CSV from store data
      if (!heatmapData || heatmapData.length === 0) return;
      
      const headers = ['Date', 'Hour', title];
      const rows = [headers];
      
      heatmapData.forEach(item => {
        const date = new Date(item.timestamp * 1000);
        const dateStr = date.toISOString().split('T')[0];
        const hour = date.getHours();
        rows.push([dateStr, `${hour}:00 - ${hour + 1}:00`, item.value.toString()]);
      });
      
      const csvContent = rows.map(row => row.join(',')).join('\n');
      const inboxName = selectedInbox ? `_${selectedInbox.name.replace(/[^a-z0-9]/gi, '_')}` : '';
      const fileName = `${downloadTitle}${inboxName}_${new Date().toISOString().split('T')[0]}.csv`;
      
      // Download file
      const blob = new Blob([csvContent], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
  }
  
  // Handle inbox selection
  function handleInboxSelect(inbox: any) {
    console.log('🎯 Inbox selected:', inbox);
    selectedInbox = inbox;
    showInboxDropdown = false;
    if (selectedRange) {
      fetchHeatmapData();
    }
  }
  
  // Handle date range changes
  function handleRangeTypeChange(type: 'preset' | 'month' | 'custom') {
    isMonthFilter = type === 'month';
  }
  
  function handleMonthOffsetChange(offset: number) {
    currentMonthOffset = offset;
  }
  
  // Watch for date changes
  $effect(() => {
    if (selectedFrom && selectedTo) {
      fetchHeatmapData();
    }
  });
  
  // Live refresh setup
  const { startRefetching } = useLiveRefresh(fetchHeatmapData, { interval: 60000 });
  
  onMount(async () => {
    // TODO: Fetch inboxes from inboxes store
    inboxes = [
      { id: 1, name: 'Website Chat' },
      { id: 2, name: 'Email Support' },
      { id: 3, name: 'Facebook Messenger' }
    ];
    
    startRefetching();
  });
</script>

<div class="flex flex-row flex-wrap max-w-full">
  <MetricCard 
    header={title}
    isLive={true}
    class="w-full"
  >
    {#snippet control()}
      <div class="flex items-center gap-2">
        <!-- Date Range Selector -->
        <HeatmapDateRangeSelector
          bind:from={selectedFrom}
          bind:to={selectedTo}
          bind:daysNum={selectedDaysBefore}
          onRangeTypeChange={handleRangeTypeChange}
          onMonthOffsetChange={handleMonthOffsetChange}
        />
        
        <!-- Inbox Filter -->
        <DropdownMenu.Root bind:open={showInboxDropdown}>
          <DropdownMenu.Trigger>
            <Button
              variant="outline"
              size="sm"
              class="max-w-[200px]"
            >
              <span class="truncate">{selectedInboxFilter.label}</span>
              <ChevronDown class="ml-2 h-4 w-4 flex-shrink-0" />
            </Button>
          </DropdownMenu.Trigger>
          <DropdownMenu.Content class="w-56 max-h-96 overflow-y-auto">
            <DropdownMenu.Item onclick={() => handleInboxSelect(null)}>
              All Inboxes
            </DropdownMenu.Item>
            <DropdownMenu.Separator />
            {#each inboxes as inbox}
              <DropdownMenu.Item onclick={() => handleInboxSelect(inbox)}>
                {inbox.name}
              </DropdownMenu.Item>
            {/each}
          </DropdownMenu.Content>
        </DropdownMenu.Root>
        
        <!-- Download Button -->
        <Button
          variant="outline"
          size="sm"
          onclick={downloadHeatmapData}
          class="p-2"
          title="Download heatmap data as CSV"
        >
          <Download class="h-4 w-4" />
        </Button>
      </div>
    {/snippet}
    
    {#snippet children()}
      <BaseHeatmap
        {heatmapData}
        {numberOfRows}
        {isLoading}
        {colorScheme}
      />
    {/snippet}
  </MetricCard>
</div>