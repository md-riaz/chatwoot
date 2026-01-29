<script lang="ts">
  /**
   * MultiSelect Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\components-next\filter\inputs\MultiSelect.vue
   *
   * A multi-select dropdown with chip display for filter values
   */
  import { Plus, Check } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import * as Tooltip from '$lib/components/ui/tooltip';
  import type { FilterOption } from '$lib/constants/filter-types';

  // Props matching Vue defineProps
  interface Props {
    options: FilterOption[];
    maxChips?: number;
    value?: FilterOption[];
  }

  let { options, maxChips = 3, value = $bindable([]) }: Props = $props();

  // State
  let open = $state(false);

  // Derived: has items
  const hasItems = $derived(() => {
    if (!value) return false;
    if (!Array.isArray(value)) return false;
    return value.length > 0;
  });

  // Derived: selected IDs
  const selectedIds = $derived(() => {
    if (!hasItems()) return [];
    return value.map(v => v.id);
  });

  // Derived: selected items (from options to get all properties)
  const selectedItems = $derived(() => {
    if (!hasItems()) return [];
    return options.filter(option => selectedIds().includes(option.id));
  });

  // Derived: visible items (limited by maxChips)
  const selectedVisibleItems = $derived(() => {
    if (!hasItems()) return [];
    // Avoid showing "+1 more" if it's just one extra - show them all
    if (selectedItems().length === maxChips + 1) return selectedItems();
    return selectedItems().slice(0, maxChips);
  });

  // Derived: remaining items (for tooltip)
  const remainingItems = $derived(() => {
    if (!hasItems()) return [];
    if (selectedItems().length === maxChips + 1) return [];
    return selectedItems().slice(maxChips);
  });

  // Derived: tooltip text for remaining items
  const remainingTooltip = $derived(() => {
    if (!hasItems()) return '';
    return remainingItems()
      .map(item => item.name)
      .join(', ');
  });

  // Toggle an option
  function toggleOption(option: FilterOption) {
    const optionToToggle = {
      id: option.id,
      name: option.name,
    };

    const idToToggle = optionToToggle.id;

    if (!hasItems()) {
      value = [optionToToggle];
      return;
    }

    if (selectedIds().includes(idToToggle)) {
      value = value.filter(v => v.id !== idToToggle);
    } else {
      value = [...value, optionToToggle];
    }
  }
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger asChild let:builder>
    {#if hasItems()}
      <button
        use:builder.action
        {...builder}
        class="bg-muted/50 py-2 rounded-lg h-8 flex items-center px-0 hover:bg-muted/80 transition-colors"
      >
        {#each selectedVisibleItems() as item (item.id)}
          <div
            class="px-3 border-r border-border text-foreground text-sm flex gap-2 items-center max-w-[100px]"
          >
            <span class="truncate">{item.name}</span>
          </div>
        {/each}
        {#if remainingItems().length > 0}
          <Tooltip.Root>
            <Tooltip.Trigger asChild let:builder>
              <div
                use:builder.action
                {...builder}
                class="px-3 border-r border-border text-foreground text-sm flex gap-2 items-center max-w-[100px]"
              >
                <span class="truncate">+{remainingItems().length} more</span>
              </div>
            </Tooltip.Trigger>
            <Tooltip.Content>
              <p>{remainingTooltip()}</p>
            </Tooltip.Content>
          </Tooltip.Root>
        {/if}
        <div class="flex items-center border-none px-3 gap-2">
          <Plus class="h-4 w-4" />
        </div>
      </button>
    {:else}
      <Button
        builders={[builder]}
        variant="secondary"
        size="sm"
        class="h-8 gap-1 text-sm font-normal text-muted-foreground"
      >
        <Plus class="h-4 w-4" />
        <span>Select value</span>
      </Button>
    {/if}
  </DropdownMenu.Trigger>
  <DropdownMenu.Content
    class="min-w-48 max-h-80 overflow-y-auto z-50"
    align="start"
  >
    {#each options as option (option.id)}
      <DropdownMenu.Item
        class="cursor-pointer gap-2 justify-between"
        on:click={e => {
          e.preventDefault();
          toggleOption(option);
        }}
      >
        <span class="truncate">{option.name}</span>
        {#if selectedIds().includes(option.id)}
          <Check class="h-4 w-4 text-primary shrink-0" />
        {/if}
      </DropdownMenu.Item>
    {/each}
  </DropdownMenu.Content>
</DropdownMenu.Root>
