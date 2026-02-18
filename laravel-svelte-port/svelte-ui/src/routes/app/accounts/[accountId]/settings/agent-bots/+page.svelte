<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { agentBotsStore } from '$lib/stores/agentBots.svelte';
  import SectionLayout from '../components/SectionLayout.svelte';
  import DataTable from '$lib/components/ui/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { AgentBot } from '$lib/api/agentBots';

  const accountId = $derived(Number($page.params.accountId));
  const bots = $derived(agentBotsStore.allBots);
  const loading = $derived(agentBotsStore.uiFlags.isFetching);

  onMount(() => {
    agentBotsStore.fetchAgentBots();
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/settings/agent-bots/new`);
  }

  function handleEdit(bot: AgentBot) {
    goto(`/app/accounts/${accountId}/settings/agent-bots/${bot.id}`);
  }

  async function handleDelete(bot: AgentBot) {
    if (confirm('Are you sure you want to delete this agent bot?')) {
      await agentBotsStore.deleteAgentBot(bot.id);
    }
  }

  const columns = [
    { key: 'name', label: 'Name' },
    { key: 'description', label: 'Description' },
    { key: 'outgoing_url', label: 'Outgoing URL' },
  ];
</script>

<SectionLayout
  title="Agent Bots"
  description="Configure agent bots for your account"
>
  <div slot="actions">
    <Button on:click={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Add Agent Bot
    </Button>
  </div>

  <DataTable
    {columns}
    data={bots}
    {loading}
    on:edit={e => handleEdit(e.detail)}
    on:delete={e => handleDelete(e.detail)}
    emptyMessage="No agent bots found"
  />
</SectionLayout>
