<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import * as inboxesApi from '$lib/api/inboxes';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Label } from '$lib/components/ui/label';
  import { Input } from '$lib/components/ui/input';
  import { Switch } from '$lib/components/ui/switch';
  import InboxSettingsHeader from '$lib/components/inbox/InboxSettingsHeader.svelte';
  import InboxSettingsTabs from '$lib/components/inbox/InboxSettingsTabs.svelte';

  let accountId = $derived(Number($page.params.accountId));
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(i => i.id === inboxId) ?? null
  );
  let isLoadingInbox = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isSavingInbox = $derived(inboxesStore.uiFlags.isUpdating);
  let isDeleting = $derived(inboxesStore.uiFlags.isDeleting);

  let selectedAgentIds = $state<number[]>([]);
  let loadedMemberIds = $state<number[]>([]);
  let enableAutoAssignment = $state(false);
  let maxAssignmentLimit = $state('');
  let isLoadingMembers = $state(false);
  let isSavingMembers = $state(false);
  let errorMessage = $state<string | null>(null);
  let successMessage = $state<string | null>(null);
  let successTimeout: ReturnType<typeof setTimeout> | null = null;

  const allAgents = $derived(agentsStore.sortedAgents);

  function setSuccess(message: string) {
    successMessage = message;
    if (successTimeout) {
      clearTimeout(successTimeout);
    }
    successTimeout = setTimeout(() => {
      successMessage = null;
      successTimeout = null;
    }, 3000);
  }

  async function loadCollaboratorState() {
    if (!accountId || !inboxId) return;

    errorMessage = null;
    isLoadingMembers = true;

    try {
      await Promise.all([
        inboxesStore.fetchInbox(inboxId),
        agentsStore.fetchAgents(),
      ]);

      const members = await inboxesApi.getInboxMembers(accountId, inboxId);
      loadedMemberIds = members.map(member => member.id);
      selectedAgentIds = [...loadedMemberIds];
    } catch (err: any) {
      errorMessage = err?.message || 'Failed to load inbox collaborators';
    } finally {
      isLoadingMembers = false;
    }
  }

  $effect(() => {
    loadCollaboratorState();
  });

  $effect(() => {
    if (!inbox) return;

    enableAutoAssignment = Boolean(inbox.enableAutoAssignment);
    const maxLimit = inbox.autoAssignmentConfig?.max_assignment_limit;
    maxAssignmentLimit = maxLimit ? String(maxLimit) : '';
  });

  function toggleAgent(agentId: number, checked: boolean) {
    if (checked) {
      if (!selectedAgentIds.includes(agentId)) {
        selectedAgentIds = [...selectedAgentIds, agentId];
      }
      return;
    }

    selectedAgentIds = selectedAgentIds.filter(id => id !== agentId);
  }

  async function handleSaveCollaborators() {
    try {
      isSavingMembers = true;
      errorMessage = null;

      const agentsToAdd = selectedAgentIds.filter(
        id => !loadedMemberIds.includes(id)
      );
      const agentsToRemove = loadedMemberIds.filter(
        id => !selectedAgentIds.includes(id)
      );

      if (agentsToAdd.length > 0) {
        await inboxesApi.addInboxMembers(accountId, inboxId, agentsToAdd);
      }

      if (agentsToRemove.length > 0) {
        await inboxesApi.removeInboxMembers(accountId, inboxId, agentsToRemove);
      }

      loadedMemberIds = [...selectedAgentIds];
      setSuccess('Inbox collaborators updated successfully.');
    } catch (err: any) {
      errorMessage = err?.message || 'Failed to update inbox collaborators';
    } finally {
      isSavingMembers = false;
    }
  }

  async function handleSaveAssignment() {
    const parsedLimit = maxAssignmentLimit ? Number(maxAssignmentLimit) : null;
    if (parsedLimit !== null && (!Number.isFinite(parsedLimit) || parsedLimit < 1)) {
      errorMessage = 'Max assignment limit must be at least 1.';
      return;
    }

    try {
      errorMessage = null;
      await inboxesStore.updateInbox(inboxId, {
        enable_auto_assignment: enableAutoAssignment,
        auto_assignment_config:
          parsedLimit !== null ? { max_assignment_limit: parsedLimit } : {},
      });
      setSuccess('Assignment settings updated successfully.');
    } catch (err: any) {
      errorMessage = err?.message || inboxesStore.error || 'Failed to update assignment settings';
    }
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader
    accountId={accountId}
    {inbox}
    {isDeleting}
  />

  {#if inbox}
    <InboxSettingsTabs
      accountId={accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="collaborators"
    />
  {/if}

  {#if successMessage}
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
      {successMessage}
    </div>
  {/if}

  {#if errorMessage}
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
      {errorMessage}
    </div>
  {/if}

  {#if isLoadingInbox || isLoadingMembers}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if !inbox}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <p class="text-muted-foreground">Inbox not found</p>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="space-y-6">
      <Card.Root>
        <Card.Header>
          <Card.Title>Inbox Agents</Card.Title>
          <Card.Description>
            Choose which agents can collaborate inside this inbox.
          </Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
          {#if allAgents.length === 0}
            <p class="text-sm text-muted-foreground">
              No agents are available in this account yet.
            </p>
          {:else}
            <div class="grid gap-3 md:grid-cols-2">
              {#each allAgents as agent (agent.id)}
                <label
                  class="flex items-start gap-3 rounded-lg border p-3 cursor-pointer hover:bg-muted/40"
                >
                  <Checkbox
                    checked={selectedAgentIds.includes(agent.id)}
                    onCheckedChange={checked => toggleAgent(agent.id, Boolean(checked))}
                  />
                  <div class="space-y-1">
                    <div class="font-medium leading-none">{agent.name}</div>
                    <div class="text-sm text-muted-foreground">{agent.email}</div>
                    <div class="text-xs text-muted-foreground capitalize">
                      {agent.role} · {agent.availabilityStatus}
                    </div>
                  </div>
                </label>
              {/each}
            </div>
          {/if}
        </Card.Content>
        <Card.Footer>
          <Button onclick={handleSaveCollaborators} disabled={isSavingMembers}>
            {isSavingMembers ? 'Saving...' : 'Save Collaborators'}
          </Button>
        </Card.Footer>
      </Card.Root>

      <Card.Root>
        <Card.Header>
          <Card.Title>Agent Assignment</Card.Title>
          <Card.Description>
            Configure automatic assignment for conversations in this inbox.
          </Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="space-y-0.5">
              <Label>Enable Auto Assignment</Label>
              <p class="text-sm text-muted-foreground">
                Automatically assign incoming conversations to selected collaborators.
              </p>
            </div>
            <Switch bind:checked={enableAutoAssignment} />
          </div>

          <div class="space-y-2 max-w-xs">
            <Label for="max-assignment-limit">Max Assignment Limit</Label>
            <Input
              id="max-assignment-limit"
              type="number"
              min="1"
              bind:value={maxAssignmentLimit}
              placeholder="Optional"
            />
            <p class="text-sm text-muted-foreground">
              Leave blank to use the default assignment behavior.
            </p>
          </div>
        </Card.Content>
        <Card.Footer>
          <Button onclick={handleSaveAssignment} disabled={isSavingInbox}>
            {isSavingInbox ? 'Saving...' : 'Save Assignment Settings'}
          </Button>
        </Card.Footer>
      </Card.Root>
    </div>
  {/if}
</div>
