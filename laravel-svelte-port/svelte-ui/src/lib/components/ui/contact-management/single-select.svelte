<script lang="ts">
  /**
   * SingleSelect Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\components-next\filter\inputs\SingleSelect.vue
   *
   * A searchable single-select dropdown for filter values
   */
  import { Search, Plus, Check } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import type { FilterOption } from '$lib/constants/filter-types';

  // Props matching Vue defineProps
  interface Props {
    options: FilterOption[];
    disableSearch?: boolean;
    placeholderIcon?: string;
    placeholder?: string;
    placeholderTrailingIcon?: boolean;
    searchPlaceholder?: string;
    value?: FilterOption | null;
  }

  let {
    options,
    disableSearch = false,
    placeholderIcon = '',
    placeholder = 'Select value',
    placeholderTrailingIcon = false,
    searchPlaceholder = 'Search...',
    value = $bindable(null),
  }: Props = $props();

  // State
  let open = $state(false);
  let searchTerm = $state('');

  // Derived: search results using simple filtering (picoSearch equivalent)
  const searchResults = $derived(() => {
    if (!options || options.length === 0) return [];
    if (!searchTerm.trim()) return options;

    const term = searchTerm.toLowerCase();
    return options.filter(option =>
      String(option.name).toLowerCase().includes(term)
    );
  });

  // Derived: selected item
  const selectedItem = $derived(() => {
    if (!options || !value) return null;

    // Handle case where value is an array
    const optionToSearch = Array.isArray(value) ? value[0] : value;
    if (!optionToSearch) return null;

    return options.find(option => option.id === optionToSearch.id) || null;
  });

  // Toggle selected option
  function toggleSelected(option: FilterOption) {
    const optionToToggle = {
      id: option.id,
      name: option.name,
    };

    if (value && value.id === optionToToggle.id) {
      value = null;
    } else {
      value = optionToToggle;
    }
    open = false;
  }

  // Reset search when dropdown closes
  $effect(() => {
    if (!open) {
      searchTerm = '';
    }
  });
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger asChild let:builder>
    {#if selectedItem()}
      <Button
        builders={[builder]}
        variant="secondary"
        size="sm"
        class="h-8 gap-1 text-sm font-normal"
      >
        <span class="truncate max-w-[150px]">{selectedItem()?.name}</span>
      </Button>
    {:else}
      <Button
        builders={[builder]}
        variant="secondary"
        size="sm"
        class="h-8 gap-1 text-sm font-normal text-muted-foreground"
      >
        {#if placeholderIcon}
          <span class={placeholderIcon}></span>
        {:else}
          <Plus class="h-4 w-4" />
        {/if}
        <span>{placeholder}</span>
      </Button>
    {/if}
  </DropdownMenu.Trigger>
  <DropdownMenu.Content class="min-w-56 z-50" align="start">
    {#if !disableSearch}
      <div class="relative p-1">
        <Search
          class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground"
        />
        <input
          bind:value={searchTerm}
          class="w-full pl-8 pr-2 py-1.5 text-sm bg-muted/50 rounded-md border-0 focus:outline-none focus:ring-1 focus:ring-ring"
          placeholder={searchPlaceholder}
        />
      </div>
    {/if}
    <div class="max-h-80 overflow-y-auto">
      {#if searchResults().length > 0}
        {#each searchResults() as option (option.id)}
          <DropdownMenu.Item
            class="cursor-pointer gap-2 justify-between"
            on:click={() => toggleSelected(option)}
          >
            <span class="truncate">{option.name}</span>
            {#if selectedItem() && selectedItem()?.id === option.id}
              <Check class="h-4 w-4 text-primary shrink-0" />
            {/if}
          </DropdownMenu.Item>
        {/each}
      {:else if searchTerm}
        <DropdownMenu.Item disabled class="text-muted-foreground">
          No results for "{searchTerm}"
        </DropdownMenu.Item>
      {:else}
        <DropdownMenu.Item disabled class="text-muted-foreground">
          No options available
        </DropdownMenu.Item>
      {/if}
    </div>
  </DropdownMenu.Content>
</DropdownMenu.Root>
