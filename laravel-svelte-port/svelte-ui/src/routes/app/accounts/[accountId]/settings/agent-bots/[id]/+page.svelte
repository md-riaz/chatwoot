<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { agentBotsStore } from '$lib/stores/agentBots.svelte';
  import SectionLayout from '../../account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));
  const botId = $derived(
    $page.params.id === 'new' ? null : Number($page.params.id)
  );

  let name = $state('');
  let description = $state('');
  let outgoingUrl = $state('');
  let isSubmitting = $derived(
    agentBotsStore.uiFlags.isCreating || agentBotsStore.uiFlags.isUpdating
  );

  onMount(async () => {
    if (botId) {
      if (agentBotsStore.allBots.length === 0) {
        await agentBotsStore.fetchAgentBots();
      }
      const bot = agentBotsStore.allBots.find(b => b.id === botId);
      if (bot) {
        name = bot.name;
        description = bot.description || '';
        outgoingUrl = bot.outgoing_url;
      }
    }
  });

  async function handleSubmit() {
    if (!name || !outgoingUrl) {
      toast.error('Please fill in all required fields');
      return;
    }

    const data = {
      name,
      description,
      outgoing_url: outgoingUrl,
    };

    if (botId) {
      const result = await agentBotsStore.updateAgentBot(botId, data);
      if (result) {
        toast.success('Agent bot updated successfully');
        goto(`/app/accounts/${accountId}/settings/agent-bots`);
      }
    } else {
      const result = await agentBotsStore.createAgentBot(data);
      if (result) {
        toast.success('Agent bot created successfully');
        goto(`/app/accounts/${accountId}/settings/agent-bots`);
      }
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/settings/agent-bots`);
  }

  async function handleDelete() {
    if (!botId) return;
    if (confirm('Are you sure you want to delete this agent bot?')) {
      await agentBotsStore.deleteAgentBot(botId);
      toast.success('Agent bot deleted successfully');
      goto(`/app/accounts/${accountId}/settings/agent-bots`);
    }
  }
</script>

<SectionLayout
  title={botId ? 'Edit Agent Bot' : 'New Agent Bot'}
  description={botId ? 'Update agent bot details' : 'Create a new agent bot'}
>
  <form onsubmit={handleSubmit} class="space-y-6 max-w-2xl">
    <div class="grid w-full gap-1.5">
      <Label for="name">Name *</Label>
      <Input
        type="text"
        id="name"
        bind:value={name}
        placeholder="My Bot"
        required
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="description">Description</Label>
      <Textarea
        id="description"
        bind:value={description}
        placeholder="Bot description..."
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="outgoing_url">Outgoing URL *</Label>
      <Input
        type="url"
        id="outgoing_url"
        bind:value={outgoingUrl}
        placeholder="https://api.example.com/webhook"
        required
      />
      <p class="text-xs text-muted-foreground">
        The URL where Chatwoot will send events.
      </p>
    </div>

    <div class="flex justify-between pt-4">
      {#if botId}
        <Button variant="destructive" type="button" onclick={handleDelete}>
          Delete
        </Button>
      {:else}
        <div></div>
      {/if}
      <div class="flex gap-2">
        <Button variant="outline" type="button" onclick={handleCancel}>
          Cancel
        </Button>
        <Button type="submit" disabled={isSubmitting}>
          {isSubmitting
            ? 'Saving...'
            : botId
              ? 'Update Agent Bot'
              : 'Create Agent Bot'}
        </Button>
      </div>
    </div>
  </form>
</SectionLayout>
