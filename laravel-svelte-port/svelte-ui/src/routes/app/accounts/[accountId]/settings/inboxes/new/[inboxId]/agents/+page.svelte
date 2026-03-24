<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import * as inboxesApi from '$lib/api/inboxes';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { ArrowLeft } from 'lucide-svelte';

  let accountId = $derived(Number($page.params.accountId));
  let inboxId = $derived(Number($page.params.inboxId));

  let selectedAgentIds = $state<number[]>([]);
  let isLoading = $state(true);
  let isSaving = $state(false);
  let errorMessage = $state<string | null>(null);

  const inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );
  const allAgents = $derived(agentsStore.sortedAgents);

  async function loadData() {
    isLoading = true;
    errorMessage = null;

    try {
      await Promise.all([
        inboxesStore.fetchInbox(inboxId),
        agentsStore.fetchAgents(),
      ]);
    } catch (err: any) {
      errorMessage = err?.message || 'Failed to load inbox agents';
    } finally {
      isLoading = false;
    }
  }

  $effect(() => {
    loadData();
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

  async function handleContinue() {
    isSaving = true;
    errorMessage = null;

    try {
      if (selectedAgentIds.length > 0) {
        await inboxesApi.addInboxMembers(accountId, inboxId, selectedAgentIds);
      }
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inboxId}/finish`);
    } catch (err: any) {
      errorMessage = err?.message || 'Failed to add inbox agents';
    } finally {
      isSaving = false;
    }
  }

  function handleSkip() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new/${inboxId}/finish`);
  }
</script>

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <Button
      variant="ghost"
      onclick={() => goto(`/app/accounts/${accountId}/settings/inboxes/new`)}
    >
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Add Inbox Agents
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        Choose agents who should have access to this inbox.
      </p>
    </div>
  </div>

  {#if errorMessage}
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
      {errorMessage}
    </div>
  {/if}

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else}
    <Card.Root>
      <Card.Header>
        <Card.Title>{inbox?.name || 'New Inbox'}</Card.Title>
        <Card.Description>
          Agents added here can collaborate on conversations from this inbox.
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
      <Card.Footer class="flex justify-end gap-3">
        <Button variant="outline" onclick={handleSkip}>Skip for now</Button>
        <Button onclick={handleContinue} disabled={isSaving}>
          {isSaving ? 'Saving...' : 'Continue'}
        </Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
