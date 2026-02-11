<script lang="ts">
  /**
   * SLAReportFilters Component
   * Filter controls for SLA reports
   * Vue Parity: Replaces SLA filter component from Vue dashboard
   */
  import { createEventDispatcher } from 'svelte';
  import * as Select from '$lib/components/ui/select';
  import { Label } from '$lib/components/ui/label';
  import DateRangePicker from '$lib/components/ui/date-range-picker/DateRangePicker.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { slaStore } from '$lib/stores/sla.svelte';

  const dispatch = createEventDispatcher<{
    'filter-change': {
      from: number;
      to: number;
      assigned_agent_id: string | null;
      inbox_id: string | null;
      team_id: string | null;
      sla_policy_id: string | null;
      label_list: string | null;
    };
  }>();

  // Filter state
  let from = $state(0);
  let to = $state(0);
  let selectedAgent = $state<string>('');
  let selectedInbox = $state<string>('');
  let selectedTeam = $state<string>('');
  let selectedSLA = $state<string>('');
  let selectedLabel = $state<string>('');

  // Get data from stores
  const agents = $derived(agentsStore.allAgents);
  const inboxes = $derived(inboxesStore.allInboxes);
  const teams = $derived(teamsStore.allTeams);
  const slas = $derived(slaStore.all);
  const labels = $derived(labelsStore.allLabels);

  function handleDateRangeChange(event: CustomEvent<{ from: number; to: number }>) {
    from = event.detail.from;
    to = event.detail.to;
    emitFilterChange();
  }

  function emitFilterChange() {
    dispatch('filter-change', {
      from,
      to,
      assigned_agent_id: selectedAgent || null,
      inbox_id: selectedInbox || null,
      team_id: selectedTeam || null,
      sla_policy_id: selectedSLA || null,
      label_list: selectedLabel || null,
    });
  }
</script>

<div class="bg-card rounded-lg border p-4">
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <!-- Date Range -->
    <div class="space-y-2 md:col-span-2">
      <Label>Date Range</Label>
      <DateRangePicker on:change={handleDateRangeChange} />
    </div>

    <!-- Agent Filter -->
    <div class="space-y-2">
      <Label>Agent</Label>
      <Select.Root value={selectedAgent} onValueChange={(v) => { selectedAgent = v || ''; emitFilterChange(); }} type="single">
        <Select.Trigger>
          {selectedAgent ? agents.find(a => String(a.id) === selectedAgent)?.name || 'All Agents' : 'All Agents'}
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="">All Agents</Select.Item>
          {#each agents as agent}
            <Select.Item value={String(agent.id)}>{agent.name}</Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    <!-- Inbox Filter -->
    <div class="space-y-2">
      <Label>Inbox</Label>
      <Select.Root value={selectedInbox} onValueChange={(v) => { selectedInbox = v || ''; emitFilterChange(); }} type="single">
        <Select.Trigger>
          {selectedInbox ? inboxes.find(i => String(i.id) === selectedInbox)?.name || 'All Inboxes' : 'All Inboxes'}
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="">All Inboxes</Select.Item>
          {#each inboxes as inbox}
            <Select.Item value={String(inbox.id)}>{inbox.name}</Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    <!-- Team Filter -->
    <div class="space-y-2">
      <Label>Team</Label>
      <Select.Root value={selectedTeam} onValueChange={(v) => { selectedTeam = v || ''; emitFilterChange(); }} type="single">
        <Select.Trigger>
          {selectedTeam ? teams.find(t => String(t.id) === selectedTeam)?.name || 'All Teams' : 'All Teams'}
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="">All Teams</Select.Item>
          {#each teams as team}
            <Select.Item value={String(team.id)}>{team.name}</Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>

    <!-- SLA Policy Filter -->
    <div class="space-y-2">
      <Label>SLA Policy</Label>
      <Select.Root value={selectedSLA} onValueChange={(v) => { selectedSLA = v || ''; emitFilterChange(); }} type="single">
        <Select.Trigger>
          {selectedSLA ? slas.find(s => String(s.id) === selectedSLA)?.name || 'All Policies' : 'All Policies'}
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="">All Policies</Select.Item>
          {#each slas as sla}
            <Select.Item value={String(sla.id)}>{sla.name}</Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
    </div>
  </div>
</div>
