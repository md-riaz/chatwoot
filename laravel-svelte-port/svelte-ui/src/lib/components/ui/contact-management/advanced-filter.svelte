<script lang="ts">
  /**
   * Advanced Contacts Filter (Vue Parity)
   * Inline filter panel with condition rows like Vue ContactsFilter.vue
   * Features: AND/OR query operator, searchSelect, multiSelect, date inputs
   */
  import { createEventDispatcher } from 'svelte';
  import { Trash2, ChevronDown, Check, Search } from 'lucide-svelte';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import * as Popover from '$lib/components/ui/popover';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Badge } from '$lib/components/ui/badge';

  // Filter condition interface
  export interface FilterCondition {
    attributeKey: string;
    filterOperator: string;
    values: string | string[] | { id: string; name: string };
    queryOperator: 'and' | 'or';
  }

  // Filter type definition
  interface FilterOperator {
    value: string;
    label: string;
    hasInput: boolean;
  }

  interface FilterOption {
    id: string;
    name: string;
  }

  interface FilterType {
    attributeKey: string;
    label: string;
    inputType: 'plainText' | 'date' | 'searchSelect' | 'multiSelect';
    filterOperators: FilterOperator[];
    options?: FilterOption[];
  }

  // Props
  interface Props {
    open?: boolean;
    filters?: FilterCondition[];
    labels?: Array<{ id: string; name: string; title?: string }>;
  }

  let {
    open = $bindable(false),
    filters = $bindable([]),
    labels = [],
  }: Props = $props();

  // Countries list (common countries)
  const countries: FilterOption[] = [
    { id: 'US', name: 'United States' },
    { id: 'GB', name: 'United Kingdom' },
    { id: 'CA', name: 'Canada' },
    { id: 'AU', name: 'Australia' },
    { id: 'DE', name: 'Germany' },
    { id: 'FR', name: 'France' },
    { id: 'IN', name: 'India' },
    { id: 'JP', name: 'Japan' },
    { id: 'CN', name: 'China' },
    { id: 'BR', name: 'Brazil' },
    { id: 'MX', name: 'Mexico' },
    { id: 'ES', name: 'Spain' },
    { id: 'IT', name: 'Italy' },
    { id: 'NL', name: 'Netherlands' },
    { id: 'SE', name: 'Sweden' },
    { id: 'NO', name: 'Norway' },
    { id: 'DK', name: 'Denmark' },
    { id: 'FI', name: 'Finland' },
    { id: 'CH', name: 'Switzerland' },
    { id: 'AT', name: 'Austria' },
    { id: 'BE', name: 'Belgium' },
    { id: 'PL', name: 'Poland' },
    { id: 'PT', name: 'Portugal' },
    { id: 'IE', name: 'Ireland' },
    { id: 'NZ', name: 'New Zealand' },
    { id: 'SG', name: 'Singapore' },
    { id: 'HK', name: 'Hong Kong' },
    { id: 'KR', name: 'South Korea' },
    { id: 'AE', name: 'United Arab Emirates' },
    { id: 'SA', name: 'Saudi Arabia' },
    { id: 'ZA', name: 'South Africa' },
    { id: 'RU', name: 'Russia' },
    { id: 'TR', name: 'Turkey' },
    { id: 'ID', name: 'Indonesia' },
    { id: 'TH', name: 'Thailand' },
    { id: 'PH', name: 'Philippines' },
    { id: 'VN', name: 'Vietnam' },
    { id: 'MY', name: 'Malaysia' },
    { id: 'AR', name: 'Argentina' },
    { id: 'CL', name: 'Chile' },
    { id: 'CO', name: 'Colombia' },
    { id: 'PE', name: 'Peru' },
    { id: 'EG', name: 'Egypt' },
    { id: 'NG', name: 'Nigeria' },
    { id: 'KE', name: 'Kenya' },
    { id: 'IL', name: 'Israel' },
    { id: 'PK', name: 'Pakistan' },
    { id: 'BD', name: 'Bangladesh' },
  ];

  // Boolean options for Blocked filter
  const booleanOptions: FilterOption[] = [
    { id: 'true', name: 'Yes' },
    { id: 'false', name: 'No' },
  ];

  // Common operators
  const equalityOperators: FilterOperator[] = [
    { value: 'equal_to', label: 'Equal to', hasInput: true },
    { value: 'not_equal_to', label: 'Not equal to', hasInput: true },
    { value: 'is_present', label: 'Is present', hasInput: false },
    { value: 'is_not_present', label: 'Is not present', hasInput: false },
  ];

  const containmentOperators: FilterOperator[] = [
    { value: 'equal_to', label: 'Equal to', hasInput: true },
    { value: 'not_equal_to', label: 'Not equal to', hasInput: true },
    { value: 'contains', label: 'Contains', hasInput: true },
    { value: 'does_not_contain', label: 'Does not contain', hasInput: true },
    { value: 'is_present', label: 'Is present', hasInput: false },
    { value: 'is_not_present', label: 'Is not present', hasInput: false },
  ];

  const dateOperators: FilterOperator[] = [
    { value: 'is_greater_than', label: 'Is greater than', hasInput: true },
    { value: 'is_less_than', label: 'Is less than', hasInput: true },
    { value: 'days_before', label: 'Is x days before', hasInput: true },
  ];

  // All contact filter types (matching Vue)
  const filterTypes: FilterType[] = [
    {
      attributeKey: 'name',
      label: 'Name',
      inputType: 'plainText',
      filterOperators: equalityOperators,
    },
    {
      attributeKey: 'email',
      label: 'Email',
      inputType: 'plainText',
      filterOperators: containmentOperators,
    },
    {
      attributeKey: 'phone_number',
      label: 'Phone number',
      inputType: 'plainText',
      filterOperators: containmentOperators,
    },
    {
      attributeKey: 'identifier',
      label: 'Identifier',
      inputType: 'plainText',
      filterOperators: equalityOperators,
    },
    {
      attributeKey: 'country_code',
      label: 'Country',
      inputType: 'searchSelect',
      filterOperators: equalityOperators,
      options: countries,
    },
    {
      attributeKey: 'city',
      label: 'City',
      inputType: 'plainText',
      filterOperators: containmentOperators,
    },
    {
      attributeKey: 'created_at',
      label: 'Created at',
      inputType: 'date',
      filterOperators: dateOperators,
    },
    {
      attributeKey: 'last_activity_at',
      label: 'Last activity',
      inputType: 'date',
      filterOperators: dateOperators,
    },
    {
      attributeKey: 'referer',
      label: 'Referer link',
      inputType: 'plainText',
      filterOperators: containmentOperators,
    },
    {
      attributeKey: 'blocked',
      label: 'Blocked',
      inputType: 'searchSelect',
      filterOperators: equalityOperators,
      options: booleanOptions,
    },
    {
      attributeKey: 'labels',
      label: 'Labels',
      inputType: 'multiSelect',
      filterOperators: equalityOperators,
      options: [], // Will be populated from props
    },
  ];

  // Derive labels options
  $effect(() => {
    const labelsType = filterTypes.find(f => f.attributeKey === 'labels');
    if (labelsType && labels.length > 0) {
      labelsType.options = labels.map(l => ({ id: l.title || l.id, name: l.title || l.name }));
    }
  });

  // Query operator options (AND / OR)
  const queryOperatorOptions = [
    { value: 'and', label: 'AND', icon: '&&' },
    { value: 'or', label: 'OR', icon: '||' },
  ];

  // Default filter
  const DEFAULT_FILTER: FilterCondition = {
    attributeKey: 'name',
    filterOperator: 'equal_to',
    values: '',
    queryOperator: 'and',
  };

  // Initialize with one filter if empty
  $effect(() => {
    if (filters.length === 0) {
      filters = [{ ...DEFAULT_FILTER }];
    }
  });

  const dispatch = createEventDispatcher<{
    apply: FilterCondition[];
    clear: void;
  }>();

  // Search states for searchSelect
  let searchQueries = $state<Record<number, string>>({});

  // Helpers
  function getFilterType(key: string): FilterType {
    return filterTypes.find(f => f.attributeKey === key) || filterTypes[0];
  }

  function getOperators(key: string): FilterOperator[] {
    return getFilterType(key).filterOperators;
  }

  function getOperatorLabel(key: string, operatorValue: string): string {
    const ops = getOperators(key);
    return ops.find(op => op.value === operatorValue)?.label || operatorValue;
  }

  function getCurrentOperator(key: string, operatorValue: string): FilterOperator {
    const ops = getOperators(key);
    return ops.find(op => op.value === operatorValue) || ops[0];
  }

  function getFilteredOptions(index: number, options: FilterOption[]): FilterOption[] {
    const query = searchQueries[index]?.toLowerCase() || '';
    if (!query) return options;
    return options.filter(opt => opt.name.toLowerCase().includes(query));
  }

  function getSelectedOptionName(filter: FilterCondition): string {
    const type = getFilterType(filter.attributeKey);
    if (!type.options) return String(filter.values);
    if (typeof filter.values === 'object' && filter.values && 'name' in filter.values) {
      return filter.values.name;
    }
    const opt = type.options.find(o => o.id === filter.values);
    return opt?.name || 'Select...';
  }

  function getSelectedMultipleNames(filter: FilterCondition): string[] {
    const type = getFilterType(filter.attributeKey);
    if (!type.options || !Array.isArray(filter.values)) return [];
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
      dispatch('clear');
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
      values: newType.inputType === 'multiSelect' ? [] : '',
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
    newFilters[index] = {
      ...newFilters[index],
      values: newValue,
    };
    filters = newFilters;
  }

  function updateSelectValue(index: number, option: FilterOption) {
    const newFilters = [...filters];
    newFilters[index] = {
      ...newFilters[index],
      values: option.id,
    };
    filters = newFilters;
  }

  function toggleMultiSelectValue(index: number, optionId: string) {
    const newFilters = [...filters];
    const currentValues = Array.isArray(newFilters[index].values) 
      ? [...newFilters[index].values as string[]] 
      : [];
    
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
    dispatch('clear');
    open = false;
  }

  function applyFilters() {
    dispatch('apply', filters);
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
                      onclick={() => updateQueryOperator(index, qop.value as 'and' | 'or')}
                      class="flex items-center gap-2"
                    >
                      <span class="text-blue-600 font-mono text-xs">{qop.icon}</span>
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
                    {getOperatorLabel(filter.attributeKey, filter.filterOperator)}
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
                          oninput={(e) => searchQueries[index] = e.currentTarget.value}
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
                          {#if filter.values === option.id}
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
                        {#if Array.isArray(filter.values) && filter.values.length > 0}
                          <div class="flex gap-1 flex-wrap max-w-[200px]">
                            {#each getSelectedMultipleNames(filter).slice(0, 2) as name}
                              <Badge variant="secondary" class="text-xs px-1">{name}</Badge>
                            {/each}
                            {#if Array.isArray(filter.values) && filter.values.length > 2}
                              <Badge variant="secondary" class="text-xs px-1">+{filter.values.length - 2}</Badge>
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
                          oninput={(e) => searchQueries[index] = e.currentTarget.value}
                        />
                      </div>
                    </div>
                    <div class="max-h-[200px] overflow-y-auto p-1">
                      {#each getFilteredOptions(index, filterType.options) as option}
                        <button
                          type="button"
                          class="w-full px-2 py-1.5 text-sm text-left rounded hover:bg-muted flex items-center gap-2"
                          onclick={() => toggleMultiSelectValue(index, option.id)}
                        >
                          <Checkbox
                            checked={Array.isArray(filter.values) && filter.values.includes(option.id)}
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
                  value={filter.values as string}
                  oninput={(e) => updateValue(index, e.currentTarget.value)}
                  class="h-8 w-[150px]"
                />
                
              {:else}
                <!-- Plain Text Input -->
                <Input
                  value={filter.values as string}
                  oninput={(e) => updateValue(index, e.currentTarget.value)}
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
