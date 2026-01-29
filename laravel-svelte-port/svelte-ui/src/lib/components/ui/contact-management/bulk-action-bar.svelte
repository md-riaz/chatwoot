<script lang="ts">
  /**
   * BulkActionBar Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\routes\dashboard\contacts\components\ContactsBulkActionBar.vue
   *
   * Handles bulk actions for contacts (Select All, Assign Labels, Delete)
   */
  import { Trash, Tags, X } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import * as Popover from '$lib/components/ui/popover';
  import * as Tooltip from '$lib/components/ui/tooltip';
  import LabelActionsPopover from './label-actions-popover.svelte';

  interface Props {
    visibleContactIds: number[];
    selectedContactIds: number[];
    isLoading?: boolean;
    onClearSelection: () => void;
    onToggleAll: (selectAll: boolean) => void;
    onAssignLabels: (labels: string[]) => void;
    onDeleteSelected: () => void;
  }

  let {
    visibleContactIds = [],
    selectedContactIds = $bindable([]),
    isLoading = false,
    onClearSelection,
    onToggleAll,
    onAssignLabels,
    onDeleteSelected,
  }: Props = $props();

  // State
  let showLabelSelector = $state(false);

  // Derived values
  const selectedCount = $derived(selectedContactIds.length);
  const totalVisibleContacts = $derived(visibleContactIds.length);
  const hasSelected = $derived(selectedCount > 0);

  const allSelected = $derived(
    totalVisibleContacts > 0 && selectedCount === totalVisibleContacts
  );

  const isIndeterminate = $derived(
    hasSelected && selectedCount < totalVisibleContacts
  );

  const selectAllLabel = $derived.by(() => {
    if (!totalVisibleContacts) return '';
    return `Select all ${totalVisibleContacts} items`; // Parity text: CONTACTS_BULK_ACTIONS.SELECT_ALL
  });

  const selectedCountLabel = $derived(
    `${selectedCount} selected` // Parity text: CONTACTS_BULK_ACTIONS.SELECTED_COUNT
  );

  // Handlers
  function handleCheckboxChange(checked: boolean | 'indeterminate') {
    // If indeterminate, treating as "select all" or "deselect all" depending on UX, usually "select all"
    // But logically, clicking main checkbox when some selected -> select all? Or deselect all?
    // Vue implementation:
    // set: shouldSelectAll => toggleAll(shouldSelectAll)
    // If we click it, it passes the NEW checked state.

    // If it was indeterminate, checked becomes true -> select all
    // If it was checked, checked becomes false -> deselect all
    // If it was unchecked, checked becomes true -> select all

    const shouldSelectAll = checked === true;
    onToggleAll(shouldSelectAll);
  }

  function handleAssignLabels(labels: string[]) {
    onAssignLabels(labels);
    showLabelSelector = false;
  }
</script>

{#if hasSelected}
  <!-- Bulk Select Bar Container -->
  <div
    class="sticky top-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 px-6 pt-1 pb-2"
  >
    <div
      class="flex items-center gap-3 py-1 pl-3 pr-4 rounded-lg bg-card border shadow-sm h-12 transition-all duration-300 ease-out"
    >
      <!-- Selection Info & Checkbox -->
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5 min-w-0">
          <Checkbox
            checked={allSelected
              ? true
              : isIndeterminate
                ? 'indeterminate'
                : false}
            onCheckedChange={handleCheckboxChange}
          />
          <span
            class="text-sm font-medium truncate text-foreground tabular-nums"
          >
            {selectAllLabel}
          </span>
        </div>

        <span class="text-sm text-muted-foreground truncate tabular-nums">
          {selectedCountLabel}
        </span>

        <div class="h-4 w-px bg-border mx-1"></div>

        <!-- Clear Selection -->
        <Button
          variant="ghost"
          size="sm"
          class="h-8 px-2 text-muted-foreground hover:text-foreground"
          on:click={onClearSelection}
        >
          Clear selection
        </Button>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2 ml-auto">
        <!-- Assign Labels -->
        <Popover.Root bind:open={showLabelSelector}>
          <Popover.Trigger asChild let:builder>
            <Button
              builders={[builder]}
              variant="outline"
              size="sm"
              class="h-8 gap-2 text-muted-foreground"
              disabled={isLoading}
            >
              <Tags class="h-4 w-4" />
              <span class="hidden sm:inline">Assign Labels</span>
            </Button>
          </Popover.Trigger>
          <Popover.Content
            class="p-0 w-auto border-none bg-transparent shadow-none"
            align="end"
          >
            <LabelActionsPopover
              onClose={() => (showLabelSelector = false)}
              onAssign={handleAssignLabels}
            />
          </Popover.Content>
        </Popover.Root>

        <!-- Delete -->
        <Tooltip.Root>
          <Tooltip.Trigger asChild let:builder>
            <Button
              builders={[builder]}
              variant="outline"
              size="sm"
              class="h-8 w-8 px-0 text-destructive hover:text-destructive hover:bg-destructive/10"
              disabled={isLoading}
              on:click={onDeleteSelected}
            >
              <Trash class="h-4 w-4" />
              <span class="sr-only">Delete</span>
            </Button>
          </Tooltip.Trigger>
          <Tooltip.Content>Delete contacts</Tooltip.Content>
        </Tooltip.Root>
      </div>
    </div>
  </div>
{/if}
