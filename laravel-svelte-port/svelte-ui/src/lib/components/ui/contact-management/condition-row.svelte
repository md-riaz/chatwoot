<script lang="ts">
  /**
   * ConditionRow Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\components-next\filter\ConditionRow.vue
   *
   * A single filter condition row with attribute, operator, and value selectors
   */
  import { X, AlertCircle } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import FilterSelect from './filter-select.svelte';
  import SingleSelect from './single-select.svelte';
  import MultiSelect from './multi-select.svelte';
  import type { FilterType, FilterOption } from '$lib/constants/filter-types';
  import {
    NO_INPUT_OPS,
    OPS_INPUT_OVERRIDE,
    OPERATOR_LABELS,
    type FilterOpValue,
  } from '$lib/constants/filter-operators';

  // Props matching Vue defineProps
  interface Props {
    showQueryOperator?: boolean;
    filterTypes: FilterType[];
    attributeKey?: string;
    filterOperator?: string;
    values?: string | string[] | FilterOption | FilterOption[];
    queryOperator?: 'and' | 'or';
    onRemove?: () => void;
  }

  let {
    showQueryOperator = false,
    filterTypes,
    attributeKey = $bindable('name'),
    filterOperator = $bindable('equal_to'),
    values = $bindable(''),
    queryOperator = $bindable('and'),
    onRemove,
  }: Props = $props();

  // State
  let showErrors = $state(false);
  let hasError = $state(false);
  let isWiggling = $state(false);

  // Derived: current filter type
  // Derived: current filter type
  const currentFilter = $derived(
    filterTypes.find(f => f.attributeKey === attributeKey) || filterTypes[0]
  );

  // Derived: available operators for current filter
  // Derived: available operators for current filter
  const operatorOptions = $derived(
    currentFilter?.filterOperators.map(op => ({
      value: op.value,
      label: op.label,
      icon: op.icon,
    })) || []
  );

  // Derived: attribute options for dropdown
  // Derived: attribute options for dropdown
  const attributeOptions = $derived(
    filterTypes.map(f => ({
      value: f.attributeKey,
      label: f.label,
    }))
  );

  // Derived: should show input field
  // Derived: should show input field
  const showInput = $derived(
    !NO_INPUT_OPS.includes(filterOperator as FilterOpValue)
  );

  // Derived: input type for current filter
  // Derived: input type for current filter
  const inputType = $derived.by(() => {
    const override = OPS_INPUT_OVERRIDE[filterOperator as FilterOpValue];
    if (override) return override;
    return currentFilter?.inputType || 'plainText';
  });

  // Derived: options for select inputs
  // Derived: options for select inputs
  const selectOptions = $derived(currentFilter?.options || []);

  // Query operator options
  const queryOperatorOptions = [
    { value: 'and', label: 'AND' },
    { value: 'or', label: 'OR' },
  ];

  // Handle attribute change - reset operator and values
  function handleAttributeChange(newAttributeKey: string) {
    attributeKey = newAttributeKey;
    const newFilter = filterTypes.find(f => f.attributeKey === newAttributeKey);
    if (newFilter && newFilter.filterOperators.length > 0) {
      filterOperator = newFilter.filterOperators[0].value;
    }
    // Reset values
    if (newFilter?.inputType === 'multiSelect') {
      values = [];
    } else if (
      newFilter?.inputType === 'searchSelect' ||
      newFilter?.inputType === 'booleanSelect'
    ) {
      values = null;
    } else {
      values = '';
    }
    showErrors = false;
    hasError = false;
  }

  // Handle operator change
  function handleOperatorChange(newOperator: string) {
    filterOperator = newOperator;
    // Reset values if switching to no-input operator
    if (NO_INPUT_OPS.includes(newOperator as FilterOpValue)) {
      values = '';
    }
  }

  // Validate the condition
  export function validate(): boolean {
    // No validation needed for no-input operators
    if (!showInput) {
      hasError = false;
      return true;
    }

    // Check if value is empty
    let isEmpty = false;
    if (Array.isArray(values)) {
      isEmpty = values.length === 0;
    } else if (typeof values === 'object' && values !== null) {
      isEmpty = !values.id;
    } else {
      isEmpty = !values || String(values).trim() === '';
    }

    hasError = isEmpty;
    showErrors = isEmpty;

    if (isEmpty) {
      triggerWiggle();
    }

    return !isEmpty;
  }

  // Trigger wiggle animation
  function triggerWiggle() {
    isWiggling = true;
    setTimeout(() => {
      isWiggling = false;
    }, 500);
  }

  // Get current condition data
  export function getCondition() {
    // Normalize values to string array for API
    let normalizedValues: string[] = [];

    if (Array.isArray(values)) {
      normalizedValues = values.map(v =>
        typeof v === 'object' ? String(v.id) : String(v)
      );
    } else if (typeof values === 'object' && values !== null) {
      normalizedValues = [String((values as FilterOption).id)];
    } else if (values) {
      normalizedValues = [String(values)];
    }

    return {
      attributeKey,
      filterOperator,
      values: normalizedValues,
      queryOperator,
      attributeModel: currentFilter?.attributeModel || 'standard',
    };
  }
</script>

<div
  class="flex items-center gap-2 flex-wrap {isWiggling ? 'animate-wiggle' : ''}"
  class:border-destructive={hasError && showErrors}
>
  <!-- Query Operator (AND/OR) -->
  {#if showQueryOperator}
    <FilterSelect
      options={queryOperatorOptions}
      bind:value={queryOperator}
      variant="outline"
      class="w-16"
    />
  {/if}

  <!-- Attribute Selector -->
  <FilterSelect
    options={attributeOptions}
    value={attributeKey}
    on:change={e => handleAttributeChange(e.detail)}
    variant="secondary"
  />

  <!-- Operator Selector -->
  <FilterSelect
    options={operatorOptions}
    value={filterOperator}
    on:change={e => handleOperatorChange(e.detail)}
    variant="secondary"
  />

  <!-- Value Input -->
  {#if showInput}
    <div class="flex items-center gap-1">
      {#if inputType === 'plainText' || inputType === 'number'}
        <Input
          type={inputType === 'number' ? 'number' : 'text'}
          bind:value={values}
          placeholder="Enter value"
          class="h-8 w-40 text-sm {hasError && showErrors
            ? 'border-destructive'
            : ''}"
        />
      {:else if inputType === 'date'}
        <Input
          type="date"
          bind:value={values}
          class="h-8 w-40 text-sm {hasError && showErrors
            ? 'border-destructive'
            : ''}"
        />
      {:else if inputType === 'searchSelect' || inputType === 'booleanSelect'}
        <SingleSelect
          options={selectOptions}
          bind:value={values}
          placeholder="Select value"
        />
      {:else if inputType === 'multiSelect'}
        <MultiSelect options={selectOptions} bind:value={values} />
      {/if}

      <!-- Validation Error Icon -->
      {#if hasError && showErrors}
        <AlertCircle class="h-4 w-4 text-destructive shrink-0" />
      {/if}
    </div>
  {/if}

  <!-- Remove Button -->
  {#if onRemove}
    <Button
      variant="ghost"
      size="icon"
      class="h-8 w-8 shrink-0"
      on:click={onRemove}
    >
      <X class="h-4 w-4" />
    </Button>
  {/if}
</div>

<style>
  @keyframes wiggle {
    0%,
    100% {
      transform: translateX(0);
    }
    25% {
      transform: translateX(-2px);
    }
    75% {
      transform: translateX(2px);
    }
  }

  .animate-wiggle {
    animation: wiggle 0.15s ease-in-out 3;
  }
</style>
