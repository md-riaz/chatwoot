<script lang="ts">
  /**
   * Contacts Filter Dialog
   * Advanced filtering for contacts by labels, inboxes, date ranges
   */
  import { createEventDispatcher } from 'svelte';
  import { Filter, X, Calendar, Tag, Inbox } from 'lucide-svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import type { Label } from '$lib/api/labels';

  // Props
  interface Props {
    open?: boolean;
    labels?: Label[];
    appliedFilters?: FilterState;
  }

  let {
    open = $bindable(false),
    labels = [],
    appliedFilters = {},
  }: Props = $props();

  // Filter state interface
  export interface FilterState {
    labels?: string[];
    dateFrom?: string;
    dateTo?: string;
    hasEmail?: boolean;
    hasPhone?: boolean;
    onlineOnly?: boolean;
  }

  // Local state
  let selectedLabels = $state<Set<string>>(
    new Set(appliedFilters.labels || [])
  );
  let dateFrom = $state(appliedFilters.dateFrom || '');
  let dateTo = $state(appliedFilters.dateTo || '');
  let hasEmail = $state(appliedFilters.hasEmail || false);
  let hasPhone = $state(appliedFilters.hasPhone || false);
  let onlineOnly = $state(appliedFilters.onlineOnly || false);

  // Computed: active filter count
  const activeFilterCount = $derived(() => {
    let count = selectedLabels.size;
    if (dateFrom) count++;
    if (dateTo) count++;
    if (hasEmail) count++;
    if (hasPhone) count++;
    if (onlineOnly) count++;
    return count;
  });

  const dispatch = createEventDispatcher<{
    apply: FilterState;
    clear: void;
  }>();

  function toggleLabel(label: string) {
    const newSet = new Set(selectedLabels);
    if (newSet.has(label)) {
      newSet.delete(label);
    } else {
      newSet.add(label);
    }
    selectedLabels = newSet;
  }

  function applyFilters() {
    dispatch('apply', {
      labels: selectedLabels.size > 0 ? Array.from(selectedLabels) : undefined,
      dateFrom: dateFrom || undefined,
      dateTo: dateTo || undefined,
      hasEmail: hasEmail || undefined,
      hasPhone: hasPhone || undefined,
      onlineOnly: onlineOnly || undefined,
    });
    open = false;
  }

  function clearFilters() {
    selectedLabels = new Set();
    dateFrom = '';
    dateTo = '';
    hasEmail = false;
    hasPhone = false;
    onlineOnly = false;
    dispatch('clear');
    open = false;
  }
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-[500px]">
    <Dialog.Header>
      <Dialog.Title class="flex items-center gap-2">
        <Filter class="h-5 w-5" />
        Filter Contacts
      </Dialog.Title>
      <Dialog.Description>
        Apply filters to narrow down your contact list
      </Dialog.Description>
    </Dialog.Header>

    <div class="py-4 space-y-6">
      <!-- Labels filter -->
      <div class="space-y-2">
        <label class="text-sm font-medium flex items-center gap-2">
          <Tag class="h-4 w-4" />
          Labels
        </label>
        {#if labels.length === 0}
          <p class="text-sm text-muted-foreground">No labels available</p>
        {:else}
          <div class="flex flex-wrap gap-2">
            {#each labels as label}
              <button
                type="button"
                onclick={() => toggleLabel(label.title)}
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs transition-colors {selectedLabels.has(
                  label.title
                )
                  ? 'ring-2 ring-primary ring-offset-1'
                  : 'hover:bg-muted'}"
                style="background-color: {label.color}20; color: {label.color}"
              >
                <div
                  class="w-2 h-2 rounded-full"
                  style="background-color: {label.color}"
                ></div>
                {label.title}
              </button>
            {/each}
          </div>
        {/if}
      </div>

      <!-- Date range filter -->
      <div class="space-y-2">
        <label class="text-sm font-medium flex items-center gap-2">
          <Calendar class="h-4 w-4" />
          Created Date Range
        </label>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-muted-foreground">From</label>
            <Input type="date" bind:value={dateFrom} />
          </div>
          <div>
            <label class="text-xs text-muted-foreground">To</label>
            <Input type="date" bind:value={dateTo} />
          </div>
        </div>
      </div>

      <!-- Quick filters -->
      <div class="space-y-3">
        <label class="text-sm font-medium">Quick Filters</label>
        <div class="space-y-2">
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox bind:checked={hasEmail} />
            <span class="text-sm">Has email address</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox bind:checked={hasPhone} />
            <span class="text-sm">Has phone number</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer">
            <Checkbox bind:checked={onlineOnly} />
            <span class="text-sm">Currently online</span>
          </label>
        </div>
      </div>
    </div>

    <Dialog.Footer class="flex justify-between">
      <Button variant="ghost" onclick={clearFilters}>
        <X class="h-4 w-4 mr-1" />
        Clear All
      </Button>
      <div class="flex gap-2">
        <Button variant="outline" onclick={() => (open = false)}>Cancel</Button>
        <Button onclick={applyFilters}>
          Apply Filters
          {#if activeFilterCount() > 0}
            <Badge variant="secondary" class="ml-1 px-1.5">
              {activeFilterCount()}
            </Badge>
          {/if}
        </Button>
      </div>
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
