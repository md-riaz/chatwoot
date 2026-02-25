<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import {
    Puzzle,
    Plus,
    Settings,
    CheckCircle,
    AlertCircle,
  } from '@lucide/svelte';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { integrationsStore } from '$lib/stores/integrations.svelte';
  import { toast } from 'svelte-sonner';

  const accountId = $derived($page.params.accountId);
  const integrations = $derived(integrationsStore.integrations);
  const isLoading = $derived(integrationsStore.isLoading);
  const error = $derived(integrationsStore.error);

  $effect(() => {
    if (accountId) {
      integrationsStore.fetch();
    }
  });

  function handleIntegrationAction(status: 'connected' | 'available') {
    if (status === 'connected') {
      goto(`/app/accounts/${accountId}/settings/inboxes`);
      return;
    }

    toast.info('Integration setup flow will be available in a next iteration.');
  }
</script>

<div class="space-y-6">
  <div
    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div>
      <h1 class="text-3xl font-bold">Integrations</h1>
      <p class="text-muted-foreground">
        Connect your favorite tools and channels
      </p>
    </div>
  </div>

  {#if isLoading}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {#each Array(6) as _}
        <Card.Root
          ><Card.Content class="p-6"
            ><Skeleton class="h-24 w-full" /></Card.Content
          ></Card.Root
        >
      {/each}
    </div>
  {:else if error}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <AlertCircle class="mb-4 h-12 w-12 text-destructive" />
        <h3 class="mb-2 text-lg font-semibold">Unable to load integrations</h3>
        <p class="mb-4 text-sm text-muted-foreground">{error}</p>
        <Button variant="outline" onclick={() => integrationsStore.fetch()}
          >Retry</Button
        >
      </Card.Content>
    </Card.Root>
  {:else if integrations.length === 0}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <Puzzle class="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-2 text-lg font-semibold">No integrations available</h3>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="mb-4">
      <p class="text-sm text-muted-foreground">
        {integrationsStore.connectedCount} of {integrations.length} integrations connected
      </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {#each integrations as integration (integration.id)}
        <Card.Root class="transition-all hover:shadow-md">
          <Card.Content class="p-6">
            <div
              class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10"
            >
              {#if integration.logo}
                <img
                  src={integration.logo}
                  alt={`${integration.name} logo`}
                  class="h-8 w-8 rounded"
                />
              {:else}
                <Puzzle class="h-6 w-6 text-primary" />
              {/if}
            </div>
            <div class="mb-2 flex items-center gap-2">
              <h3 class="font-semibold">{integration.name}</h3>
              <Badge
                variant={integration.status === 'connected'
                  ? 'default'
                  : 'secondary'}
                class="gap-1 text-xs"
              >
                {#if integration.status === 'connected'}
                  <CheckCircle class="h-3 w-3" />
                {/if}
                {integration.status}
              </Badge>
            </div>
            {#if integration.description}
              <p class="mb-4 text-sm text-muted-foreground">
                {integration.description}
              </p>
            {/if}
            <Button
              variant={integration.status === 'connected'
                ? 'secondary'
                : 'default'}
              size="sm"
              class="w-full"
              onclick={() => handleIntegrationAction(integration.status)}
            >
              {#if integration.status === 'connected'}
                <Settings class="mr-2 h-4 w-4" />
                Configure
              {:else}
                <Plus class="mr-2 h-4 w-4" />
                Connect
              {/if}
            </Button>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</div>
