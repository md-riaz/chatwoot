<script lang="ts">
  /**
   * ConversationFilters - Filter controls for conversations
   * Status tabs, inbox selector, sort options
   */

  import { ArrowRightToLine, ArrowUpDown, Check, Funnel, X } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Popover from '$lib/components/ui/popover';
  import * as Tabs from '$lib/components/ui/tabs';
  import type {
    ConversationFilterOptions,
    ConversationSortOptions,
  } from './types';
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
    {
      value: 'mine',
      label: $_('conversation.filters.status.mine'),
      count: statusCounts.mine || 0,
    },
    {
      value: 'unassigned',
      label: $_('conversation.filters.status.unassigned'),
      count: statusCounts.unassigned || 0,
    },
    {
      value: 'all',
      label: $_('conversation.filters.status.all'),
      count: statusCounts.all || 0,
    },
  ]);

  const conversationStatusOptions = $derived([
    { value: 'all', label: 'All statuses' },
    { value: 'open', label: $_('conversation.filters.status.open') },
    { value: 'resolved', label: $_('conversation.filters.status.resolved') },
    { value: 'pending', label: 'Pending' },
    { value: 'snoozed', label: 'Snoozed' },
  ]);

  const sortOptions = $derived([
    { value: 'latest', label: $_('conversation.filters.sort.latest') },
    { value: 'oldest', label: $_('conversation.filters.sort.oldest') },
    { value: 'priority', label: $_('conversation.filters.sort.priority') },
    { value: 'unread', label: $_('conversation.filters.sort.unread') },
  ]);

  let selectedStatus = $state('all');
  let selectedSort = $state('latest');
  let selectedConversationStatus = $state('all');

  const sortLabel = $derived(
    sortOptions.find(opt => opt.value === selectedSort)?.label ||
      $_('conversation.filters.sort.select_order')
  );

  const activeStatusLabel = $derived(
    conversationStatusOptions.find(
      option => option.value === selectedConversationStatus
    )?.label || $_('conversation.filters.status.open')
  );

  $effect(() => {
    if (filters.assigneeType === 'me') {
      selectedStatus = 'mine';
      return;
    }

    if (filters.assigneeType === 'unassigned') {
      selectedStatus = 'unassigned';
      return;
    }

    selectedStatus = 'all';
  });

  $effect(() => {
    if (filters.status) {
      selectedConversationStatus = filters.status;
      return;
    }

    selectedConversationStatus = 'all';
  });

  // Sync selectedSort with sort prop
  $effect(() => {
    selectedSort = sort.sortBy;
  });

  function handleStatusChange(value: string) {
    selectedStatus = value;

    const newFilters: ConversationFilterOptions = { ...filters };

    delete newFilters.assigneeType;

    if (value === 'mine') {
      newFilters.assigneeType = 'me';
    } else if (value === 'unassigned') {
      newFilters.assigneeType = 'unassigned';
    }

    onFiltersChange?.(newFilters);
  }

  function handleConversationStatusChange(value: string) {
    selectedConversationStatus = value;

    const newFilters: ConversationFilterOptions = { ...filters };

    if (value === 'all') {
      delete newFilters.status;
    } else {
      newFilters.status = value as any;
    }

    onFiltersChange?.(newFilters);
  }

  function clearConversationStatusFilter() {
    handleConversationStatusChange('all');
  }

  function handleSortChange(value: string) {
    selectedSort = value;
    onSortChange?.({ sortBy: value as any });
  }

  function resetView() {
    handleStatusChange('all');
    handleConversationStatusChange('all');
    handleSortChange('latest');
  }
</script>

<div class="flex flex-col border-b border-slate-200 bg-white">
  <div class="flex items-center justify-between gap-3 px-5 pb-4 pt-5">
    <div class="flex min-w-0 items-center gap-2">
      <h1 class="truncate text-[1.75rem] font-semibold tracking-[-0.02em] text-slate-900">
        Conversations
      </h1>
      {#if selectedConversationStatus !== 'all'}
        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-sm font-medium text-slate-700">
          {activeStatusLabel}
        </span>
      {/if}
    </div>

    <div class="flex items-center gap-2">
      <Popover.Root>
        <Popover.Trigger>
          {#snippet child({ props })}
            <Button
              {...props}
              variant="ghost"
              size="icon"
              class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50"
            >
              <Funnel class="h-4 w-4" />
            </Button>
          {/snippet}
        </Popover.Trigger>
        <Popover.Content class="w-56 rounded-2xl border-slate-200 bg-white p-3 shadow-lg">
          <div class="mb-3 flex items-center justify-between">
            <div class="text-sm font-semibold text-slate-900">Conversation status</div>
            {#if selectedConversationStatus !== 'all'}
              <Button
                variant="ghost"
                size="icon"
                class="h-7 w-7"
                onclick={clearConversationStatusFilter}
              >
                <X class="h-4 w-4" />
              </Button>
            {/if}
          </div>
          <div class="space-y-1">
            {#each conversationStatusOptions as option}
              <button
                type="button"
                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-100"
                class:bg-slate-100={selectedConversationStatus === option.value}
                class:text-slate-900={selectedConversationStatus === option.value}
                class:text-slate-600={selectedConversationStatus !== option.value}
                onclick={() => handleConversationStatusChange(option.value)}
              >
                <span>{option.label}</span>
                {#if selectedConversationStatus === option.value}
                  <Check class="h-4 w-4" />
                {/if}
              </button>
            {/each}
          </div>
        </Popover.Content>
      </Popover.Root>

      <Popover.Root>
        <Popover.Trigger>
          {#snippet child({ props })}
            <Button
              {...props}
              variant="ghost"
              size="icon"
              class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50"
            >
              <ArrowUpDown class="h-4 w-4" />
            </Button>
          {/snippet}
        </Popover.Trigger>
        <Popover.Content class="w-48 rounded-2xl border-slate-200 bg-white p-2 shadow-lg">
          <div class="mb-1 px-2 py-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">
            Sort
          </div>
          <div class="space-y-1">
            {#each sortOptions as option}
              <button
                type="button"
                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition hover:bg-slate-100"
                class:bg-slate-100={selectedSort === option.value}
                class:text-slate-900={selectedSort === option.value}
                class:text-slate-600={selectedSort !== option.value}
                onclick={() => handleSortChange(option.value)}
              >
                <span>{option.label}</span>
                {#if selectedSort === option.value}
                  <Check class="h-4 w-4" />
                {/if}
              </button>
            {/each}
          </div>
        </Popover.Content>
      </Popover.Root>

      <Button
        variant="ghost"
        size="icon"
        class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm hover:bg-slate-50"
        onclick={resetView}
      >
        <ArrowRightToLine class="h-4 w-4" />
      </Button>
    </div>
  </div>

  <Tabs.Root value={selectedStatus} onValueChange={handleStatusChange}>
    <Tabs.List class="flex w-full items-center gap-6 border-b border-slate-200 px-5">
      {#each statusTabs as tab}
        <Tabs.Trigger
          value={tab.value}
          class="flex min-w-0 items-center justify-center gap-2 border-b-2 border-transparent px-0 pb-4 pt-1 text-sm font-medium text-slate-600 transition-colors data-[state=active]:border-blue-500 data-[state=active]:text-blue-600"
        >
          <span>{tab.label}</span>
          <span
            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
            class:bg-blue-50={selectedStatus === tab.value}
            class:text-blue-600={selectedStatus === tab.value}
            class:bg-slate-100={selectedStatus !== tab.value}
            class:text-slate-700={selectedStatus !== tab.value}
          >
            {tab.count > 99 ? $_('common.badges.overflow') : tab.count}
          </span>
        </Tabs.Trigger>
      {/each}
    </Tabs.List>
  </Tabs.Root>
</div>
