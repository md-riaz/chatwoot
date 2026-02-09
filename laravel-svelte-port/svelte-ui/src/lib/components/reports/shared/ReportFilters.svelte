<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import * as Select from '$lib/components/ui/select';
  import { Label } from '$lib/components/ui/label';
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';

  interface Props {
    type?: string;
    filterItemsList?: any[];
    groupByfilterItemsList?: any[];
    selectedGroupByFilter?: any;
    currentFilter?: any;
  }

  let {
    type = 'account',
    filterItemsList = [],
    groupByfilterItemsList = [],
    selectedGroupByFilter = null,
    currentFilter = null,
  }: Props = $props();

  const dispatch = createEventDispatcher();

  // Use $derived to maintain reactive references to props
  const currentFilterId = $derived(currentFilter?.id?.toString() || '');
  const selectedGroupById = $derived(selectedGroupByFilter?.id?.toString() || '');

  let selectedFilterValue = $state(currentFilterId);
  let selectedGroupByValue = $state(selectedGroupById);
  let businessHoursEnabled = $state(false);

  // Update local state when props change
  $effect(() => {
    selectedFilterValue = currentFilterId;
  });

  $effect(() => {
    selectedGroupByValue = selectedGroupById;
  });

  const filterLabel = $derived(
    filterItemsList.find((item) => item.id?.toString() === selectedFilterValue)?.name || 
    `Select ${type}`
  );

  const groupByLabel = $derived(
    groupByfilterItemsList.find((item) => item.id?.toString() === selectedGroupByValue)?.groupBy || 
    'Select grouping'
  );

  function onFilterSelect(value: string) {
    selectedFilterValue = value;
    const filter = filterItemsList.find((item) => item.id?.toString() === value);
    dispatch('filter-change', filter);
  }

  function onGroupBySelect(value: string) {
    selectedGroupByValue = value;
    const groupBy = groupByfilterItemsList.find((item) => item.id?.toString() === value);
    dispatch('group-by-filter-change', groupBy);
  }

  function onDateRangeChange(event: CustomEvent) {
    dispatch('date-range-change', event.detail);
  }

  function onBusinessHoursChange(checked: boolean) {
    businessHoursEnabled = checked;
    dispatch('business-hours-toggle', checked);
  }
</script>

<div class="flex flex-col gap-4 p-4 bg-card rounded-lg border">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Date Range Picker -->
    <div class="space-y-2">
      <Label>Date Range</Label>
      <DateRangePicker on:change={onDateRangeChange} />
    </div>

    <!-- Filter Selector -->
    <div class="space-y-2">
      <Label>Filter by {type}</Label>
      <Select.Root value={selectedFilterValue} onValueChange={onFilterSelect} type="single">
        <Select.Trigger>
          {filterLabel}
        </Select.Trigger>
        <Select.Content>
          {#each filterItemsList as item}
            <Select.Item value={item.id?.toString()} label={item.name}>
              {item.name}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    <!-- Group By Selector -->
    <div class="space-y-2">
      <Label>Group By</Label>
      <Select.Root value={selectedGroupByValue} onValueChange={onGroupBySelect} type="single">
        <Select.Trigger>
          {groupByLabel}
        </Select.Trigger>
        <Select.Content>
          {#each groupByfilterItemsList as item}
            <Select.Item value={item.id?.toString()} label={item.groupBy}>
              {item.groupBy}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    <!-- Business Hours Toggle -->
    <div class="flex items-end space-x-2">
      <Switch
        id="business-hours"
        checked={businessHoursEnabled}
        onCheckedChange={onBusinessHoursChange}
      />
      <Label for="business-hours">Business Hours Only</Label>
    </div>
  </div>
</div>
