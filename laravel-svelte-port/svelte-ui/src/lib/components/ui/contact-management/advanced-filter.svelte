<script lang="ts">
  /**
   * Advanced Contacts Filter (Vue Parity)
   * Inline filter panel with condition rows like Vue ContactsFilter.vue
   * Features: AND/OR query operator, searchSelect, multiSelect, date inputs
   */
  import { Trash2, ChevronDown, Check, Search } from 'lucide-svelte';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import * as Popover from '$lib/components/ui/popover';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Badge } from '$lib/components/ui/badge';

  // Import constants from Vue-parity files
  import {
    buildFilterTypes,
    countries,
    booleanOptions,
    DEFAULT_FILTER,
    type FilterType,
    type FilterOption,
    type FilterCondition,
  } from '$lib/constants/filter-types';
  import {
    equalityOperators,
    containmentOperators,
    dateOperators,
    NO_INPUT_OPS,
    type FilterOperator,
    type FilterOpValue,
  } from '$lib/constants/filter-operators';

  // Re-export FilterCondition for backwards compatibility
  export type { FilterCondition };

  // Props
  interface Props {
    open?: boolean;
    filters?: FilterCondition[];
    labels?: Array<{ id: string; name: string; title?: string }>;
    onapply?: (filters: FilterCondition[]) => void;
    onclear?: () => void;
  }

  let {
    open = $bindable(false),
    filters = $bindable([]),
    labels = [],
    onapply = (_filters: FilterCondition[]) => {},
    onclear = () => {},
  }: Props = $props();

  // Build filter types with labels from props
  // This uses the Vue-parity constants including:
  // - Full countries list (250+)
  // - Correct labels ("Country name", "True"/"False")
  // - All 12 filter attributes
  // - Correct operator labels ("Is lesser than")
  const filterTypes = $derived(
    buildFilterTypes(
      labels.map(l => ({ id: l.title || l.id, name: l.title || l.name }))
    )
  );

  // Query operator options (AND / OR)
  const queryOperatorOptions = [
    { value: 'and', label: 'AND', icon: '&&' },
    { value: 'or', label: 'OR', icon: '||' },
  ];

  // Use DEFAULT_FILTER from constants (already imported from filter-types.ts)

  // Initialize with one filter if empty
  $effect(() => {
    if (filters.length === 0) {
      filters = [{ ...DEFAULT_FILTER }];
    }
  });

  // Search states for searchSelect
  let searchQueries = $state<Record<number, string>>({});

  // Helpers
  function getFilterType(key: string): FilterType {
    const types = filterTypes;
    return types.find(f => f.attributeKey === key) || types[0];
  }

  function getOperators(key: string): FilterOperator[] {
    return getFilterType(key).filterOperators;
  }

  function getOperatorLabel(key: string, operatorValue: string): string {
    const ops = getOperators(key);
    return ops.find(op => op.value === operatorValue)?.label || operatorValue;
  }

  function getCurrentOperator(
    key: string,
    operatorValue: string
  ): FilterOperator {
    const ops = getOperators(key);
    return ops.find(op => op.value === operatorValue) || ops[0];
  }

  function getFilteredOptions(
    index: number,
    options: FilterOption[]
  ): FilterOption[] {
    const query = searchQueries[index]?.toLowerCase() || '';
    if (!query) return options;
    return options.filter(opt => opt.name.toLowerCase().includes(query));
  }

  function getSelectedOptionName(filter: FilterCondition): string {
    const type = getFilterType(filter.attributeKey);
    if (!type.options) return filter.values.join(', ');
    // For searchSelect, values is array with single element
    const selectedId = filter.values[0];
    if (!selectedId) return 'Select...';
    const opt = type.options.find(o => o.id === selectedId);
    return opt?.name || 'Select...';
  }

  function getSelectedMultipleNames(filter: FilterCondition): string[] {
    const type = getFilterType(filter.attributeKey);
    if (!type.options) return [];
    return filter.values
      .map(v => type.options?.find(o => o.id === v)?.name || v)
      .filter(Boolean) as string[];
  }

  // Actions
  function addFilter() {
    filters = [...filters, { ...DEFAULT_FILTER }];
  }

  function removeFilter(index: number) {
    if (filters.length === 1) {
      filters = [{ ...DEFAULT_FILTER }];
      onclear();
    } else {
      filters = filters.filter((_, i) => i !== index);
    }
  }

  function updateAttribute(index: number, newKey: string) {
    const newFilters = [...filters];
    const newType = getFilterType(newKey);
    newFilters[index] = {
      ...newFilters[index],
      attributeKey: newKey,
      filterOperator: newType.filterOperators[0].value,
      values: [], // Always reset to empty array
    };
    filters = newFilters;
  }

  function updateOperator(index: number, newOp: string) {
    const newFilters = [...filters];
    newFilters[index] = {
      ...newFilters[index],
      filterOperator: newOp,
    };
    filters = newFilters;
  }

  function updateValue(index: number, newValue: string) {
    const newFilters = [...filters];
    // Store as array with single value (Vue format)
    newFilters[index] = {
      ...newFilters[index],
      values: newValue ? [newValue] : [],
    };
    filters = newFilters;
  }

  function updateSelectValue(index: number, option: FilterOption) {
    const newFilters = [...filters];
    // Store as array with single value (Vue format)
    newFilters[index] = {
      ...newFilters[index],
      values: [String(option.id)],
    };
    filters = newFilters;
  }

  function toggleMultiSelectValue(index: number, optionId: string) {
    const newFilters = [...filters];
    const currentValues = [...newFilters[index].values];

    const valueIndex = currentValues.indexOf(optionId);
    if (valueIndex > -1) {
      currentValues.splice(valueIndex, 1);
    } else {
      currentValues.push(optionId);
    }

    newFilters[index] = {
      ...newFilters[index],
      values: currentValues,
    };
    filters = newFilters;
  }

  // Update query operator on PREVIOUS row (Vue pattern)
  function updateQueryOperator(index: number, newQueryOp: 'and' | 'or') {
    if (index > 0) {
      const newFilters = [...filters];
      newFilters[index - 1] = {
        ...newFilters[index - 1],
        queryOperator: newQueryOp,
      };
      filters = newFilters;
    }
  }

  // Get query operator from PREVIOUS row
  function getQueryOperator(index: number): 'and' | 'or' {
    if (index > 0 && filters[index - 1]) {
      return filters[index - 1].queryOperator || 'and';
    }
    return 'and';
  }

  function clearFilters() {
    filters = [{ ...DEFAULT_FILTER }];
    onclear();
    open = false;
  }

  function applyFilters() {
    // Dispatch filters with values always as arrays (Vue parity)
    // The API client will auto-convert keys to snake_case
    onapply(filters);
    open = false;
  }
</script>

{#if open}
  <div
    class="absolute top-full right-0 mt-2 z-50 w-[750px] overflow-visible border border-border bg-background/95 backdrop-blur-[100px] shadow-lg rounded-xl p-6 grid gap-6"
  >
    <h3 class="text-base font-medium leading-6 text-foreground">
      Filter contacts
    </h3>

    <!-- Filter Rows -->
    <ul class="grid gap-4 list-none m-0 p-0">
      {#each filters as filter, index (index)}
        <li class="list-none">
          <div class="flex items-center gap-2 rounded-md">
            <!-- Query Operator (AND/OR) - Only shown for index > 0 -->
            {#if index > 0}
              <DropdownMenu.Root>
                <DropdownMenu.Trigger>
                  {#snippet child({ props })}
                    <Button
                      {...props}
                      variant="secondary"
                      size="sm"
                      class="h-8 px-3 font-medium text-sm min-w-[60px]"
                    >
                      {getQueryOperator(index).toUpperCase()}
                    </Button>
                  {/snippet}
                </DropdownMenu.Trigger>
                <DropdownMenu.Content align="start" class="w-28">
                  {#each queryOperatorOptions as qop}
                    <DropdownMenu.Item
                      onclick={() =>
                        updateQueryOperator(index, qop.value as 'and' | 'or')}
                      class="flex items-center gap-2"
                    >
                      <span class="text-blue-600 font-mono text-xs"
                        >{qop.icon}</span
                      >
                      {qop.label}
                    </DropdownMenu.Item>
                  {/each}
                </DropdownMenu.Content>
              </DropdownMenu.Root>
            {/if}

            <!-- Attribute Selector -->
            <DropdownMenu.Root>
              <DropdownMenu.Trigger>
                {#snippet child({ props })}
                  <Button
                    {...props}
                    variant="secondary"
                    size="sm"
                    class="h-8 gap-1 min-w-[120px] justify-between"
                  >
                    {getFilterType(filter.attributeKey).label}
                    <ChevronDown class="h-3 w-3 opacity-50" />
                  </Button>
                {/snippet}
              </DropdownMenu.Trigger>
              <DropdownMenu.Content align="start" class="w-44">
                {#each filterTypes as type}
                  <DropdownMenu.Item
                    onclick={() => updateAttribute(index, type.attributeKey)}
                  >
                    {type.label}
                  </DropdownMenu.Item>
                {/each}
              </DropdownMenu.Content>
            </DropdownMenu.Root>

            <!-- Operator Selector -->
            <DropdownMenu.Root>
              <DropdownMenu.Trigger>
                {#snippet child({ props })}
                  <Button
                    {...props}
                    variant="ghost"
                    size="sm"
                    class="h-8 gap-1 text-muted-foreground"
                  >
                    <span class="text-blue-600 font-medium">=</span>
                    {getOperatorLabel(
                      filter.attributeKey,
                      filter.filterOperator
                    )}
                    <ChevronDown class="h-3 w-3 opacity-50" />
                  </Button>
                {/snippet}
              </DropdownMenu.Trigger>
              <DropdownMenu.Content align="start" class="w-48">
                {#each getOperators(filter.attributeKey) as op}
                  <DropdownMenu.Item
                    onclick={() => updateOperator(index, op.value)}
                    class="flex items-center gap-2"
                  >
                    {op.label}
                  </DropdownMenu.Item>
                {/each}
              </DropdownMenu.Content>
            </DropdownMenu.Root>

            <!-- Value Input (based on inputType) -->
            {#if getCurrentOperator(filter.attributeKey, filter.filterOperator).hasInput}
              {@const filterType = getFilterType(filter.attributeKey)}

              {#if filterType.inputType === 'searchSelect' && filterType.options}
                <!-- Searchable Single Select -->
                <Popover.Root>
                  <Popover.Trigger>
                    {#snippet child({ props })}
                      <Button
                        {...props}
                        variant="outline"
                        size="sm"
                        class="h-8 min-w-[150px] justify-between font-normal"
                      >
                        {getSelectedOptionName(filter) || 'Select...'}
                        <ChevronDown class="h-3 w-3 opacity-50" />
                      </Button>
                    {/snippet}
                  </Popover.Trigger>
                  <Popover.Content class="w-[200px] p-0" align="start">
                    <div class="p-2 border-b">
                      <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-muted-foreground" />
                        <input
                          type="text"
                          placeholder="Search..."
                          class="flex-1 bg-transparent text-sm outline-none"
                          oninput={e =>
                            (searchQueries[index] = e.currentTarget.value)}
                        />
                      </div>
                    </div>
                    <div class="max-h-[200px] overflow-y-auto p-1">
                      {#each getFilteredOptions(index, filterType.options) as option}
                        <button
                          type="button"
                          class="w-full px-2 py-1.5 text-sm text-left rounded hover:bg-muted flex items-center justify-between"
                          onclick={() => {
                            updateSelectValue(index, option);
                            searchQueries[index] = '';
                          }}
                        >
                          {option.name}
                          {#if filter.values.includes(String(option.id))}
                            <Check class="h-4 w-4 text-blue-600" />
                          {/if}
                        </button>
                      {/each}
                    </div>
                  </Popover.Content>
                </Popover.Root>
              {:else if filterType.inputType === 'multiSelect' && filterType.options}
                <!-- Multi Select -->
                <Popover.Root>
                  <Popover.Trigger>
                    {#snippet child({ props })}
                      <Button
                        {...props}
                        variant="outline"
                        size="sm"
                        class="h-8 min-w-[150px] justify-between font-normal gap-1"
                      >
                        {#if filter.values.length > 0}
                          <div class="flex gap-1 flex-wrap max-w-[200px]">
                            {#each getSelectedMultipleNames(filter).slice(0, 2) as name}
                              <Badge variant="secondary" class="text-xs px-1"
                                >{name}</Badge
                              >
                            {/each}
                            {#if filter.values.length > 2}
                              <Badge variant="secondary" class="text-xs px-1"
                                >+{filter.values.length - 2}</Badge
                              >
                            {/if}
                          </div>
                        {:else}
                          Select...
                        {/if}
                        <ChevronDown class="h-3 w-3 opacity-50 flex-shrink-0" />
                      </Button>
                    {/snippet}
                  </Popover.Trigger>
                  <Popover.Content class="w-[200px] p-0" align="start">
                    <div class="p-2 border-b">
                      <div class="flex items-center gap-2">
                        <Search class="h-4 w-4 text-muted-foreground" />
                        <input
                          type="text"
                          placeholder="Search..."
                          class="flex-1 bg-transparent text-sm outline-none"
                          oninput={e =>
                            (searchQueries[index] = e.currentTarget.value)}
                        />
                      </div>
                    </div>
                    <div class="max-h-[200px] overflow-y-auto p-1">
                      {#each getFilteredOptions(index, filterType.options) as option}
                        <button
                          type="button"
                          class="w-full px-2 py-1.5 text-sm text-left rounded hover:bg-muted flex items-center gap-2"
                          onclick={() =>
                            toggleMultiSelectValue(index, String(option.id))}
                        >
                          <Checkbox
                            checked={filter.values.includes(String(option.id))}
                            class="h-4 w-4"
                          />
                          {option.name}
                        </button>
                      {/each}
                    </div>
                  </Popover.Content>
                </Popover.Root>
              {:else if filterType.inputType === 'date'}
                <!-- Date Input -->
                <Input
                  type="date"
                  value={filter.values[0] || ''}
                  oninput={e => updateValue(index, e.currentTarget.value)}
                  class="h-8 w-[150px]"
                />
              {:else}
                <!-- Plain Text Input -->
                <Input
                  value={filter.values[0] || ''}
                  oninput={e => updateValue(index, e.currentTarget.value)}
                  placeholder="Enter value"
                  class="h-8 flex-1"
                />
              {/if}
            {/if}

            <!-- Delete Button -->
            <Button
              variant="secondary"
              size="sm"
              class="h-8 w-8 flex-shrink-0"
              onclick={() => removeFilter(index)}
            >
              <Trash2 class="h-4 w-4" />
            </Button>
          </div>
        </li>
      {/each}
    </ul>

    <!-- Footer Actions -->
    <div class="flex justify-between gap-2">
      <Button
        variant="ghost"
        size="sm"
        class="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
        onclick={addFilter}
      >
        Add filter
      </Button>
      <div class="flex gap-2 flex-shrink-0">
        <Button variant="secondary" size="sm" onclick={clearFilters}>
          Clear filters
        </Button>
        <Button
          size="sm"
          class="bg-blue-600 hover:bg-blue-700 text-white"
          onclick={applyFilters}
        >
          Apply filters
        </Button>
      </div>
    </div>
  </div>
{/if}
