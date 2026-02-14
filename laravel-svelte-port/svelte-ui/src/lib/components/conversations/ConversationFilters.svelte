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
  import { _ } from '$lib/i18n';
  
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
    { value: 'all', label: $_('conversation.filters.status.all'), count: statusCounts.all || 0 },
    { value: 'mine', label: $_('conversation.filters.status.mine'), count: statusCounts.mine || 0 },
    { value: 'unassigned', label: $_('conversation.filters.status.unassigned'), count: statusCounts.unassigned || 0 },
    { value: 'open', label: $_('conversation.filters.status.open'), count: statusCounts.open || 0 },
    { value: 'resolved', label: $_('conversation.filters.status.resolved'), count: statusCounts.resolved || 0 },
  ]);
  
  const sortOptions = [
    { value: 'latest', label: $_('conversation.filters.sort.latest') },
    { value: 'oldest', label: $_('conversation.filters.sort.oldest') },
    { value: 'priority', label: $_('conversation.filters.sort.priority') },
    { value: 'unread', label: $_('conversation.filters.sort.unread') },
  ];
  
  let selectedStatus = $state('all');
  let selectedSort = $state('latest');
  
  // Derived label for sort select display
  const sortLabel = $derived(
    sortOptions.find(opt => opt.value === selectedSort)?.label || $_('conversation.filters.sort.select_order')
  );
  
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
              {tab.count > 99 ? $_('common.badges.overflow') : tab.count}
            </Badge>
          {/if}
        </Tabs.Trigger>
      {/each}
    </Tabs.List>
  </Tabs.Root>
  
  <!-- Sort Selector -->
  <div class="flex items-center gap-2">
    <span class="text-sm text-muted-foreground">{$_('conversation.filters.sort_by')}</span>
    <Select.Root bind:value={selectedSort} onValueChange={handleSortChange} type="single">
      <Select.Trigger class="w-[180px]">
        {sortLabel}
      </Select.Trigger>
      <Select.Content>
        {#each sortOptions as option}
          <Select.Item value={option.value} label={option.label}>
            {option.label}
          </Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
  </div>
</div>
