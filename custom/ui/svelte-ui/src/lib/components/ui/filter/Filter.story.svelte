<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ConditionRow, ActiveFilterPreview } from './index';
  import { Button } from '$lib/components/ui/button';

  const filterTypes = [
    {
      attributeKey: 'status',
      label: 'Status',
      inputType: 'select' as const,
      operators: [
        { value: 'equal_to', label: 'is' },
        { value: 'not_equal_to', label: 'is not' },
      ],
      options: [
        { value: 'open', label: 'Open' },
        { value: 'resolved', label: 'Resolved' },
        { value: 'pending', label: 'Pending' },
        { value: 'snoozed', label: 'Snoozed' },
      ],
    },
    {
      attributeKey: 'assignee',
      label: 'Assignee',
      inputType: 'select' as const,
      operators: [
        { value: 'equal_to', label: 'is' },
        { value: 'not_equal_to', label: 'is not' },
        { value: 'is_present', label: 'is present' },
        { value: 'is_not_present', label: 'is not present' },
      ],
      options: [
        { value: 'agent1', label: 'John Doe' },
        { value: 'agent2', label: 'Jane Smith' },
        { value: 'agent3', label: 'Mike Johnson' },
      ],
    },
    {
      attributeKey: 'inbox',
      label: 'Inbox',
      inputType: 'select' as const,
      operators: [
        { value: 'equal_to', label: 'is' },
        { value: 'not_equal_to', label: 'is not' },
      ],
      options: [
        { value: 'inbox1', label: 'Support' },
        { value: 'inbox2', label: 'Sales' },
        { value: 'inbox3', label: 'Billing' },
      ],
    },
  ];

  const DEFAULT_FILTER = {
    attributeKey: 'status',
    filterOperator: 'equal_to',
    values: [] as string[],
    queryOperator: 'and' as const,
  };

  let filters = $state([{ ...DEFAULT_FILTER }]);

  function removeFilter(index: number) {
    filters = filters.filter((_, i) => i !== index);
  }

  function addFilter() {
    filters = [...filters, { ...DEFAULT_FILTER }];
  }

  function clearFilters() {
    filters = [{ ...DEFAULT_FILTER }];
  }

  // Preview filters
  const previewFilters = [
    { attributeKey: 'status', filterOperator: 'is', values: ['Open'], queryOperator: 'and' as const },
    { attributeKey: 'assignee', filterOperator: 'is', values: ['John Doe'], queryOperator: 'and' as const },
  ];
</script>

<Hst.Story title="Filters/ConditionRow" icon="lucide:filter">
  <Hst.Variant title="Filter Builder">
    <div class="min-h-[400px] p-4 space-y-2 bg-background">
      {#each filters as filter, index}
        <ConditionRow
          bind:condition={filters[index]}
          {filterTypes}
          showQueryOperator={index > 0}
          onRemove={() => removeFilter(index)}
        />
      {/each}
      <div class="flex gap-3 mt-4">
        <Button variant="outline" size="sm" onclick={addFilter}>
          Add Filter
        </Button>
        <Button size="sm" onclick={() => console.log('Filters:', filters)}>
          Apply Filters
        </Button>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Active Filter Preview">
    <div class="p-4 bg-background">
      <ActiveFilterPreview filters={previewFilters} onClear={clearFilters} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty Filter Preview">
    <div class="p-4 bg-background">
      <ActiveFilterPreview filters={[]} onClear={clearFilters} />
    </div>
  </Hst.Variant>
</Hst.Story>
