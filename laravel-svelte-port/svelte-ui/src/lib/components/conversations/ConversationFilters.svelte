<script lang="ts">
  /**
   * ConversationFilters - Filter controls for conversations
   * Status tabs, inbox selector, sort options
   */
  
  import { Button } from '$lib/components/ui/button';
  import * as Select from '$lib/components/ui/select';
  import { Badge } from '$lib/components/ui/badge';
  import * as Tabs from '$lib/components/ui/tabs';
  import type { ConversationFilterOptions, ConversationSortOptions } from './types';
  
  interface Props {
    filters?: ConversationFilterOptions;
    sort?: ConversationSortOptions;
    onFiltersChange?: (filters: ConversationFilterOptions) => void;
    onSortChange?: (sort: ConversationSortOptions) => void;
    statusCounts?: Record<string, number>;
  }
  
  let {
    filters = {},
    sort = { sortBy: 'latest' },
    onFiltersChange,
    onSortChange,
    statusCounts = {},
  }: Props = $props();
  
  const statusTabs = $derived([
    { value: 'all', label: 'All', count: statusCounts.all || 0 },
    { value: 'mine', label: 'Mine', count: statusCounts.mine || 0 },
    { value: 'unassigned', label: 'Unassigned', count: statusCounts.unassigned || 0 },
    { value: 'open', label: 'Open', count: statusCounts.open || 0 },
    { value: 'resolved', label: 'Resolved', count: statusCounts.resolved || 0 },
  ]);
  
  const sortOptions = [
    { value: 'latest', label: 'Latest' },
    { value: 'oldest', label: 'Oldest' },
    { value: 'priority', label: 'Priority' },
    { value: 'unread', label: 'Unread' },
  ];
  
  let selectedStatus = $state('all');
  let selectedSort = $state('latest');
  
  // Sync selectedSort with sort prop
  $effect(() => {
    selectedSort = sort.sortBy;
  });
  
  function handleStatusChange(value: string) {
    selectedStatus = value;
    
    const newFilters: ConversationFilterOptions = { ...filters };
    
    if (value === 'mine') {
      newFilters.assigneeType = 'me';
    } else if (value === 'unassigned') {
      newFilters.assigneeType = 'unassigned';
    } else if (value === 'all') {
      delete newFilters.assigneeType;
      delete newFilters.status;
    } else {
      newFilters.status = value as any;
    }
    
    onFiltersChange?.(newFilters);
  }
  
  function handleSortChange(value: string) {
    selectedSort = value;
    onSortChange?.({ sortBy: value as any });
  }
</script>

<div class="flex flex-col gap-4 border-b pb-4">
  <!-- Status Tabs -->
  <Tabs.Root value={selectedStatus} onValueChange={handleStatusChange}>
    <Tabs.List class="w-full grid grid-cols-5 gap-1">
      {#each statusTabs as tab}
        <Tabs.Trigger value={tab.value} class="flex items-center gap-2">
          <span>{tab.label}</span>
          {#if tab.count > 0}
            <Badge variant="secondary" class="text-xs">
              {tab.count > 99 ? '99+' : tab.count}
            </Badge>
          {/if}
        </Tabs.Trigger>
      {/each}
    </Tabs.List>
  </Tabs.Root>
  
  <!-- Sort Selector -->
  <div class="flex items-center gap-2">
    <span class="text-sm text-muted-foreground">Sort by:</span>
    <Select.Root value={selectedSort} onValueChange={handleSortChange}>
      <Select.Trigger class="w-[180px]">
        <Select.Value placeholder="Select sort order" />
      </Select.Trigger>
      <Select.Content>
        {#each sortOptions as option}
          <Select.Item value={option.value}>
            {option.label}
          </Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
  </div>
</div>
