<script lang="ts">
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Switch } from '$lib/components/ui/switch';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';

  let roundRobinEnabled = $state(true);
  let enforceAgentCapacity = $state(false);
  let useOnlineAgentsOnly = $state(true);
  let isSaving = $state(false);

  $effect(() => {
    const settings = authStore.uiSettings?.assignmentPolicy || {};
    roundRobinEnabled = settings.roundRobinEnabled ?? true;
    enforceAgentCapacity = settings.enforceAgentCapacity ?? false;
    useOnlineAgentsOnly = settings.useOnlineAgentsOnly ?? true;
  });

  async function handleSave() {
    isSaving = true;
    await authStore.updateUISettings({
      ...authStore.uiSettings,
      assignmentPolicy: {
        roundRobinEnabled,
        enforceAgentCapacity,
        useOnlineAgentsOnly,
      },
    });
    isSaving = false;
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Assignment Policy</h1>
    <p class="text-muted-foreground mt-2">
      Manage automatic routing strategy for new conversations.
    </p>
  </div>

  <Card.Root>
    <Card.Header>
      <Card.Title>Routing Strategy</Card.Title>
      <Card.Description
        >These settings control how incoming conversations are distributed.</Card.Description
      >
    </Card.Header>
    <Card.Content class="space-y-5">
      <div class="flex items-center justify-between gap-4">
        <div>
          <Label for="round-robin">Round-robin assignment</Label>
          <p class="text-sm text-muted-foreground">
            Distribute conversations evenly across available agents.
          </p>
        </div>
        <Switch id="round-robin" bind:checked={roundRobinEnabled} />
      </div>
      <div class="flex items-center justify-between gap-4">
        <div>
          <Label for="agent-capacity">Respect agent capacity rules</Label>
          <p class="text-sm text-muted-foreground">
            Stop auto-assignment when an agent reaches configured capacity.
          </p>
        </div>
        <Switch id="agent-capacity" bind:checked={enforceAgentCapacity} />
      </div>
      <div class="flex items-center justify-between gap-4">
        <div>
          <Label for="online-only">Assign only to online agents</Label>
          <p class="text-sm text-muted-foreground">
            Skip agents who are offline, away, or set to unavailable.
          </p>
        </div>
        <Switch id="online-only" bind:checked={useOnlineAgentsOnly} />
      </div>
    </Card.Content>
  </Card.Root>

  <div class="flex justify-end">
    <Button onclick={handleSave} disabled={isSaving}
      >{isSaving ? 'Saving...' : 'Save Assignment Policy'}</Button
    >
  </div>
</div>
