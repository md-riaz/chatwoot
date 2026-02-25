<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import SectionLayout from '../../account/components/SectionLayout.svelte';
  import BaseSettingsHeader from '../../components/BaseSettingsHeader.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import * as Table from '$lib/components/ui/table';
  import * as Avatar from '$lib/components/ui/avatar';
  import { toast } from 'svelte-sonner';
  import type { Team } from '$lib/api/teams';

  const accountId = $derived(Number($page.params.accountId));

  // Wizard state
  let step = $state(1);
  let createdTeam = $state<Team | null>(null);

  // Step 1 Form state
  let name = $state('');
  let description = $state('');
  let allowAutoAssign = $state(true);
  let isCreatingTeam = $derived(teamsStore.uiFlags.isCreating);

  // Step 2 Agents state
  let selectedAgents = $state<number[]>([]);
  let isUpdatingMembers = $derived(teamsStore.uiFlags.isUpdatingMembers);
  let agents = $derived(agentsStore.sortedAgents);

  let allSelected = $derived(
    agents.length > 0 && selectedAgents.length === agents.length
  );

  onMount(() => {
    agentsStore.fetchAgents();
  });

  // Step 1 Handlers
  async function handleCreateTeam() {
    if (!name.trim()) {
      toast.error('Team name is required');
      return;
    }

    const data = {
      name,
      description,
      allow_auto_assign: allowAutoAssign,
    };

    const team = await teamsStore.createTeam(data);
    if (team) {
      createdTeam = team;
      step = 2;
    }
  }

  // Step 2 Handlers
  function toggleAllAgents(checked: boolean) {
    if (checked) {
      selectedAgents = agents.map(a => a.id);
    } else {
      selectedAgents = [];
    }
  }

  function toggleAgent(agentId: number, checked: boolean) {
    if (checked) {
      selectedAgents = [...selectedAgents, agentId];
    } else {
      selectedAgents = selectedAgents.filter(id => id !== agentId);
    }
  }

  async function handleUpdateMembers() {
    if (!createdTeam) return;

    const success = await teamsStore.updateTeamMembers(
      createdTeam.id,
      selectedAgents
    );
    if (success) {
      step = 3;
    } else {
      toast.error('Failed to update team members');
    }
  }

  // Common/Navigation Handlers
  function handleCancel() {
    goto(`/app/accounts/${accountId}/settings/teams`);
  }
</script>

<div class="flex flex-col w-full space-y-6">
  {#if step === 1}
    <BaseSettingsHeader title="Create new team" />
    <div class="flex-grow flex-shrink min-w-0 space-y-6">
      <SectionLayout
        title="Team Details"
        description="Create a new team of agents to route conversations efficiently."
      >
        <form
          onsubmit={e => {
            e.preventDefault();
            handleCreateTeam();
          }}
          class="space-y-6 max-w-2xl"
        >
          <div class="grid w-full gap-1.5">
            <Label for="name">Name *</Label>
            <Input
              type="text"
              id="name"
              bind:value={name}
              placeholder="Sales, Support, etc."
              required
            />
          </div>

          <div class="grid w-full gap-1.5">
            <Label for="description">Description</Label>
            <Textarea
              id="description"
              bind:value={description}
              placeholder="Handles customer inquiries..."
            />
          </div>

          <div class="flex items-center space-x-2">
            <Checkbox id="allow_auto_assign" bind:checked={allowAutoAssign} />
            <div class="space-y-1 leading-none">
              <Label
                for="allow_auto_assign"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                Allow auto assignment for this team
              </Label>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button
              variant="outline"
              type="button"
              onclick={handleCancel}
              disabled={isCreatingTeam}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isCreatingTeam || !name.trim()}>
              {isCreatingTeam ? 'Creating...' : 'Create Team'}
            </Button>
          </div>
        </form>
      </SectionLayout>
    </div>
  {:else if step === 2}
    <BaseSettingsHeader title={`Add agents to ${createdTeam?.name}`} />
    <div class="flex-grow flex-shrink min-w-0 space-y-6">
      <SectionLayout
        title="Add Agents"
        description="Select the agents you want to add to this team."
      >
        <div
          class="rounded-md border bg-card mb-4 min-h-[300px] max-h-[500px] overflow-y-auto w-full"
        >
          <Table.Root>
            <Table.Header>
              <Table.Row class="hover:bg-transparent">
                <Table.Head class="w-12 pl-4">
                  <Checkbox
                    checked={allSelected}
                    onCheckedChange={v => toggleAllAgents(v === true)}
                    aria-label="Select all"
                  />
                </Table.Head>
                <Table.Head>Agent</Table.Head>
                <Table.Head>Email</Table.Head>
              </Table.Row>
            </Table.Header>
            <Table.Body>
              {#if agents.length === 0}
                <Table.Row class="hover:bg-transparent">
                  <Table.Cell
                    colspan={3}
                    class="h-24 text-center text-muted-foreground"
                  >
                    No agents available to add.
                  </Table.Cell>
                </Table.Row>
              {:else}
                {#each agents as agent}
                  <Table.Row
                    class={selectedAgents.includes(agent.id)
                      ? 'bg-muted/50'
                      : 'hover:bg-muted/30'}
                  >
                    <Table.Cell class="pl-4">
                      <Checkbox
                        checked={selectedAgents.includes(agent.id)}
                        onCheckedChange={v => toggleAgent(agent.id, v === true)}
                        aria-label={`Select ${agent.name}`}
                      />
                    </Table.Cell>
                    <Table.Cell>
                      <div class="flex items-center gap-3">
                        <Avatar.Root class="h-8 w-8">
                          <Avatar.Image
                            src={agent.thumbnail || ''}
                            alt={agent.name}
                          />
                          <Avatar.Fallback
                            class="bg-primary/10 text-primary font-semibold"
                          >
                            {(agent.name || agent.email || 'A')
                              .charAt(0)
                              .toUpperCase()}
                          </Avatar.Fallback>
                        </Avatar.Root>
                        <span class="font-medium capitalize text-foreground"
                          >{agent.name}</span
                        >
                      </div>
                    </Table.Cell>
                    <Table.Cell class="text-muted-foreground"
                      >{agent.email || '---'}</Table.Cell
                    >
                  </Table.Row>
                {/each}
              {/if}
            </Table.Body>
          </Table.Root>
        </div>

        <div class="flex items-center justify-between mt-6">
          <p class="text-sm font-medium text-muted-foreground">
            {selectedAgents.length} out of {agents.length} agents selected
          </p>
          <Button
            onclick={handleUpdateMembers}
            disabled={isUpdatingMembers || selectedAgents.length === 0}
          >
            {isUpdatingMembers ? 'Adding...' : 'Add Agents'}
          </Button>
        </div>
      </SectionLayout>
    </div>
  {:else if step === 3}
    <div
      class="flex-grow flex flex-col items-center justify-center py-20 px-4 text-center border rounded-lg bg-card text-card-foreground"
    >
      <div class="mb-6 rounded-full bg-emerald-100 p-4">
        <svg
          class="h-12 w-12 text-emerald-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M5 13l4 4L19 7"
          />
        </svg>
      </div>
      <h2 class="text-2xl font-bold mb-4">You're completely setup!</h2>
      <p class="text-muted-foreground mb-8 max-w-md">
        The team has been created and agents have been added. You can now use
        this team to route conversations.
      </p>
      <Button onclick={handleCancel} size="lg">Finish Setup</Button>
    </div>
  {/if}
</div>
