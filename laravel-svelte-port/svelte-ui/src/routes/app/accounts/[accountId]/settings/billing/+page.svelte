<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { billingStore } from '$lib/stores/billing.svelte';
  import { toast } from 'svelte-sonner';
  import { AlertCircle } from '@lucide/svelte';

  const accountId = $derived($page.params.accountId);
  const currentPlan = $derived(billingStore.plan);
  const invoices = $derived(billingStore.invoices);
  const isLoading = $derived(billingStore.isLoading);
  const error = $derived(billingStore.error);

  onMount(async () => {
    await billingStore.fetch();
  });

  function handleUpgrade() {
    goto(`/app/accounts/${accountId}/settings/account`);
    toast.info('Redirected to account settings to manage your plan.');
  }

  function handleDownload(invoiceId: string, downloadUrl?: string) {
    if (!downloadUrl) {
      toast.error(`Invoice ${invoiceId} is not available for download yet.`);
      return;
    }

    window.open(downloadUrl, '_blank', 'noopener,noreferrer');
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Billing</h1>
    <p class="text-muted-foreground mt-2">
      Manage your subscription and billing information
    </p>
  </div>

  {#if isLoading}
    <Card.Root
      ><Card.Content class="p-6"><Skeleton class="h-40 w-full" /></Card.Content
      ></Card.Root
    >
  {:else if error}
    <Card.Root>
      <Card.Content class="py-12 flex flex-col items-center">
        <AlertCircle class="h-12 w-12 mb-4 text-destructive" />
        <h3 class="text-lg font-semibold mb-2">
          Unable to load billing information
        </h3>
        <p class="text-sm text-muted-foreground mb-4">{error}</p>
        <Button variant="outline" onclick={() => billingStore.fetch()}
          >Retry</Button
        >
      </Card.Content>
    </Card.Root>
  {:else}
    <Card.Root>
      <Card.Header>
        <Card.Title>Current Plan</Card.Title>
      </Card.Header>
      <Card.Content>
        {#if currentPlan}
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="text-2xl font-bold">{currentPlan.name}</h3>
              <p class="text-gray-600 mt-1">
                ${currentPlan.amount} / {currentPlan.billingCycle}
              </p>
            </div>
            <Badge variant="default" class="text-sm px-4 py-2"
              >{currentPlan.status}</Badge
            >
          </div>

          <div class="space-y-2 mb-6">
            <h4 class="font-semibold">Included Features:</h4>
            <ul class="space-y-2">
              {#each currentPlan.features as feature}
                <li>{feature}</li>
              {/each}
            </ul>
          </div>
        {:else}
          <p class="text-sm text-muted-foreground">No active plan found.</p>
        {/if}

        <div class="flex gap-2">
          <Button onclick={handleUpgrade}>Upgrade Plan</Button>
          <Button variant="outline" onclick={handleUpgrade}
            >Manage Subscription</Button
          >
        </div>
      </Card.Content>
    </Card.Root>

    <Card.Root>
      <Card.Header>
        <Card.Title>Invoice History</Card.Title>
      </Card.Header>
      <Card.Content>
        {#if invoices.length === 0}
          <p class="text-sm text-muted-foreground">No invoices available.</p>
        {:else}
          <div class="space-y-3">
            {#each invoices as invoice}
              <div class="flex items-center justify-between border-b pb-3">
                <div>
                  <p class="font-semibold">{invoice.id}</p>
                  <p class="text-sm text-gray-600">
                    {new Date(invoice.date).toLocaleDateString()}
                  </p>
                </div>
                <div class="flex items-center gap-3">
                  <span class="font-semibold">${invoice.amount}</span>
                  <Badge variant="outline">{invoice.status}</Badge>
                  <Button
                    variant="outline"
                    size="sm"
                    onclick={() =>
                      handleDownload(invoice.id, invoice.downloadUrl)}
                  >
                    Download
                  </Button>
                </div>
              </div>
            {/each}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  {/if}
</div>
