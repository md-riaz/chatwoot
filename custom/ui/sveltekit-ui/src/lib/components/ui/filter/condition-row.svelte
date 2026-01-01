<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  interface FilterCondition {
    attributeKey: string;
    filterOperator: string;
    values: string[];
    queryOperator?: 'and' | 'or';
  }

  interface FilterType {
    attributeKey: string;
    label: string;
    inputType: 'select' | 'text' | 'number' | 'date';
    operators: { value: string; label: string }[];
    options?: { value: string; label: string }[];
  }

  let {
    condition = $bindable<FilterCondition>({
      attributeKey: '',
      filterOperator: '',
      values: [],
      queryOperator: 'and',
    }),
    filterTypes = [],
    showQueryOperator = false,
    class: className = '',
    onRemove = () => {},
    ...restProps
  }: {
    condition?: FilterCondition;
    filterTypes: FilterType[];
    showQueryOperator?: boolean;
    class?: string;
    onRemove?: () => void;
  } = $props();

  const selectedFilterType = $derived(
    filterTypes.find((f) => f.attributeKey === condition.attributeKey)
  );

  const operators = $derived(selectedFilterType?.operators || []);
  const options = $derived(selectedFilterType?.options || []);
</script>

<div class={cn('flex items-center gap-2 p-2', className)} {...restProps}>
  {#if showQueryOperator}
    <select
      class="h-8 px-2 text-sm border rounded-md bg-background"
      bind:value={condition.queryOperator}
    >
      <option value="and">AND</option>
      <option value="or">OR</option>
    </select>
  {/if}

  <select
    class="h-8 px-2 text-sm border rounded-md bg-background min-w-[140px]"
    bind:value={condition.attributeKey}
  >
    <option value="">Select attribute...</option>
    {#each filterTypes as filterType}
      <option value={filterType.attributeKey}>{filterType.label}</option>
    {/each}
  </select>

  {#if condition.attributeKey}
    <select
      class="h-8 px-2 text-sm border rounded-md bg-background min-w-[120px]"
      bind:value={condition.filterOperator}
    >
      <option value="">Select operator...</option>
      {#each operators as operator}
        <option value={operator.value}>{operator.label}</option>
      {/each}
    </select>
  {/if}

  {#if condition.filterOperator && options.length > 0}
    <select
      class="h-8 px-2 text-sm border rounded-md bg-background min-w-[140px]"
      bind:value={condition.values[0]}
    >
      <option value="">Select value...</option>
      {#each options as option}
        <option value={option.value}>{option.label}</option>
      {/each}
    </select>
  {:else if condition.filterOperator}
    <input
      type="text"
      class="h-8 px-2 text-sm border rounded-md bg-background min-w-[140px]"
      placeholder="Enter value..."
      bind:value={condition.values[0]}
    />
  {/if}

  <Button variant="ghost" size="sm" onclick={onRemove}>
    ×
  </Button>
</div>
