<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import * as Select from '$lib/components/ui/select';
  import { Label } from '$lib/components/ui/label';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';

  interface Props {
    showTeamFilter?: boolean;
  }

  let { showTeamFilter = false }: Props = $props();

  const dispatch = createEventDispatcher();

  let from = $state(0);
  let to = $state(0);
  let selectedAgents = $state<any[]>([]);
  let selectedInbox = $state<any>(null);
  let selectedTeam = $state<any>(null);
  let selectedRating = $state<any>(null);

  const agents = $derived(agentsStore.getAgents());
  const inboxes = $derived(inboxesStore.getInboxes());
  const teams = $derived(teamsStore.getTeams());

  const ratingOptions = [
    { value: 1, label: '1 Star' },
    { value: 2, label: '2 Stars' },
    { value: 3, label: '3 Stars' },
    { value: 4, label: '4 Stars' },
    { value: 5, label: '5 Stars' },
  ];

  function onDateRangeChange(event: CustomEvent) {
    from = event.detail.from;
    to = event.detail.to;
    emitFilterChange();
  }

  function emitFilterChange() {
    dispatch('filter-change', {
      from,
      to,
      selectedAgents,
      selectedInbox,
      selectedTeam,
      selectedRating,
    });
  }
</script>

<div class="flex flex-col gap-4 p-4 bg-card rounded-lg border">
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="space-y-2">
      <Label>Date Range</Label>
      <DateRangePicker on:change={onDateRangeChange} />
    </div>

    <div class="space-y-2">
      <Label>Rating</Label>
      <Select.Root type="single">
        <Select.Trigger>
          {selectedRating?.label || 'All Ratings'}
        </Select.Trigger>
        <Select.Content>
          {#each ratingOptions as option}
            <Select.Item value={option.value.toString()} label={option.label}>
              {option.label}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    {#if showTeamFilter}
      <div class="space-y-2">
        <Label>Team</Label>
        <Select.Root type="single">
          <Select.Trigger>
            {selectedTeam?.name || 'All Teams'}
          </Select.Trigger>
          <Select.Content>
            {#each teams as team}
              <Select.Item value={team.id.toString()} label={team.name}>
                {team.name}
              </Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      </div>
    {/if}
  </div>
</div>
