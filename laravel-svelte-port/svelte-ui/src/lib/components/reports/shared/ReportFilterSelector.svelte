<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import * as Select from '$lib/components/ui/select';
  import { Label } from '$lib/components/ui/label';
  import { Switch } from '$lib/components/ui/switch';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

  interface Props {
    showAgentsFilter?: boolean;
    showGroupByFilter?: boolean;
    showBusinessHoursSwitch?: boolean;
  }

  let {
    showAgentsFilter = true,
    showGroupByFilter = false,
    showBusinessHoursSwitch = true,
  }: Props = $props();

  const dispatch = createEventDispatcher();

  let from = $state(0);
  let to = $state(0);
  let groupBy = $state(GROUP_BY_FILTER[1]);
  let businessHours = $state(false);
  let selectedAgents = $state<any[]>([]);

  const groupByLabel = $derived(groupBy?.label || 'Select grouping');

  function onDateRangeChange(event: CustomEvent) {
    from = event.detail.from;
    to = event.detail.to;
    emitFilterChange();
  }

  function onGroupBySelect(value: string) {
    const selected = Object.values(GROUP_BY_FILTER).find(
      (filter) => filter.id?.toString() === value
    );
    if (selected) {
      groupBy = selected;
      emitFilterChange();
    }
  }

  function onBusinessHoursChange(checked: boolean) {
    businessHours = checked;
    emitFilterChange();
  }

  function emitFilterChange() {
    dispatch('filter-change', {
      from,
      to,
      groupBy,
      businessHours,
      selectedAgents,
    });
  }
</script>

<div class="flex flex-col gap-4 p-4 bg-card rounded-lg border">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Date Range Picker -->
    <div class="space-y-2">
      <Label>Date Range</Label>
      <DateRangePicker on:change={onDateRangeChange} />
    </div>

    <!-- Group By Selector -->
    {#if showGroupByFilter}
      <div class="space-y-2">
        <Label>Group By</Label>
        <Select.Root 
          value={groupBy?.id?.toString()} 
          onValueChange={onGroupBySelect} 
          type="single"
        >
          <Select.Trigger>
            {groupByLabel}
          </Select.Trigger>
          <Select.Content>
            {#each Object.values(GROUP_BY_FILTER) as filter}
              <Select.Item value={filter.id?.toString()} label={filter.label}>
                {filter.label}
              </Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      </div>
    {/if}

    <!-- Business Hours Toggle -->
    {#if showBusinessHoursSwitch}
      <div class="flex items-end space-x-2">
        <Switch
          id="business-hours"
          checked={businessHours}
          onCheckedChange={onBusinessHoursChange}
        />
        <Label for="business-hours">Business Hours Only</Label>
      </div>
    {/if}
  </div>
</div>
