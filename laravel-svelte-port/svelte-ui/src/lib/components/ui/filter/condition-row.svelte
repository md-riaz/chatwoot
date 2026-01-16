<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import * as Select from '$lib/components/ui/select';

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

  // Use string values directly for shadcn-svelte select
  let queryOperatorValue = $state<string>(condition.queryOperator || 'and');
  let attributeKeyValue = $state<string>(condition.attributeKey || '');
  let filterOperatorValue = $state<string>(condition.filterOperator || '');
  let filterValueValue = $state<string>(condition.values[0] || '');

  // Sync back to condition when select values change
  $effect(() => {
    condition.queryOperator = queryOperatorValue as 'and' | 'or';
  });

  $effect(() => {
    condition.attributeKey = attributeKeyValue;
  });

  $effect(() => {
    condition.filterOperator = filterOperatorValue;
  });

  $effect(() => {
    if (condition.values.length === 0) {
      condition.values = [''];
    }
    condition.values[0] = filterValueValue;
  });

  // Sync from condition when it changes externally
  $effect(() => {
    queryOperatorValue = condition.queryOperator || 'and';
    attributeKeyValue = condition.attributeKey || '';
    filterOperatorValue = condition.filterOperator || '';
    filterValueValue = condition.values[0] || '';
  });
</script>

<div class={cn('flex items-center gap-2 p-2', className)} {...restProps}>
  {#if showQueryOperator}
    <Select.Root bind:value={queryOperatorValue} type="single">
      <Select.Trigger class="h-8 w-[80px]">
        <Select.Value />
      </Select.Trigger>
      <Select.Content>
        <Select.Item value="and">AND</Select.Item>
        <Select.Item value="or">OR</Select.Item>
      </Select.Content>
    </Select.Root>
  {/if}

  <Select.Root bind:value={attributeKeyValue} type="single">
    <Select.Trigger class="h-8 min-w-[140px]">
      <Select.Value placeholder="Select attribute..." />
    </Select.Trigger>
    <Select.Content>
      {#each filterTypes as filterType}
        <Select.Item value={filterType.attributeKey}>{filterType.label}</Select.Item>
      {/each}
    </Select.Content>
  </Select.Root>

  {#if condition.attributeKey}
    <Select.Root bind:value={filterOperatorValue} type="single">
      <Select.Trigger class="h-8 min-w-[120px]">
        <Select.Value placeholder="Select operator..." />
      </Select.Trigger>
      <Select.Content>
        {#each operators as operator}
          <Select.Item value={operator.value}>{operator.label}</Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
  {/if}

  {#if condition.filterOperator && options.length > 0}
    <Select.Root bind:value={filterValueValue} type="single">
      <Select.Trigger class="h-8 min-w-[140px]">
        <Select.Value placeholder="Select value..." />
      </Select.Trigger>
      <Select.Content>
        {#each options as option}
          <Select.Item value={option.value}>{option.label}</Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
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
