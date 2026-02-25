<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import {
    Search,
    MessageSquare,
    Edit,
    Trash2,
    Copy,
    Plus,
    AlertCircle,
  } from '@lucide/svelte';
  import * as Card from '$lib/components/ui/card';
  import * as Dialog from '$lib/components/ui/alert-dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { PaginationFooter } from '$lib/components/ui/pagination';
  import { cannedResponsesStore } from '$lib/stores/cannedResponses.svelte';
  import { toast } from 'svelte-sonner';
  import { truncate } from '$lib/utils/format';

  let searchQuery = $state('');
  let responseToDelete = $state<number | null>(null);
  let showDeleteDialog = $state(false);

  const accountId = $derived(page.params.accountId);
  const cannedResponses = $derived(cannedResponsesStore.items);
  const isLoading = $derived(cannedResponsesStore.isLoading);
  const isDeleting = $derived(cannedResponsesStore.isDeleting);
  const error = $derived(cannedResponsesStore.error);

  $effect(() => {
    if (accountId) {
      searchQuery = '';
      cannedResponsesStore.fetch({ page: 1, perPage: 15 });
    }
  });

  async function handleSearch() {
    await cannedResponsesStore.fetch({
      page: 1,
      perPage: 15,
      search: searchQuery.trim() || undefined,
    });
  }

  async function copyShortCode(code: string) {
    try {
      await navigator.clipboard.writeText(code);
      toast.success('Short code copied to clipboard');
    } catch {
      toast.error('Failed to copy to clipboard');
    }
  }

  async function confirmDelete() {
    if (!responseToDelete) return;
    const deleted = await cannedResponsesStore.delete(responseToDelete);
    if (deleted) toast.success('Canned response deleted');
    else
      toast.error(
        cannedResponsesStore.error ?? 'Failed to delete canned response'
      );
    responseToDelete = null;
    showDeleteDialog = false;
  }
</script>

<div class="space-y-6">
  <div
    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div>
      <h1 class="text-3xl font-bold">Canned Responses</h1>
      <p class="text-muted-foreground">Save time with pre-written responses</p>
    </div>
    <Button onclick={() => goto(`/app/accounts/${accountId}/settings/macros`)}>
      <Plus class="mr-2 h-4 w-4" />
      New Response
    </Button>
  </div>

  <div class="mb-6 flex gap-2">
    <div class="relative flex-1">
      <Search
        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
      />
      <Input
        type="text"
        placeholder="Search by short code or content..."
        bind:value={searchQuery}
        class="pl-10"
        onkeydown={e => e.key === 'Enter' && handleSearch()}
      />
    </div>
    <Button variant="outline" onclick={handleSearch}>Search</Button>
  </div>

  {#if isLoading}
    <div class="grid gap-4">
      {#each Array(6) as _}<Card.Root
          ><Card.Content class="p-4"
            ><Skeleton class="h-14 w-full" /></Card.Content
          ></Card.Root
        >{/each}
    </div>
  {:else if error}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <AlertCircle class="mb-4 h-12 w-12 text-destructive" />
        <h3 class="mb-2 text-lg font-semibold">
          Unable to load canned responses
        </h3>
        <p class="mb-4 text-sm text-muted-foreground">{error}</p>
        <Button
          variant="outline"
          onclick={() =>
            cannedResponsesStore.fetch({
              page: 1,
              perPage: 15,
              search: searchQuery.trim() || undefined,
            })}>Retry</Button
        >
      </Card.Content>
    </Card.Root>
  {:else if cannedResponses.length === 0}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <MessageSquare class="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-2 text-lg font-semibold">No canned responses found</h3>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="mb-4 text-sm text-muted-foreground">
      {cannedResponsesStore.total} responses
    </div>
    <div class="grid gap-4">
      {#each cannedResponses as response (response.id)}
        <Card.Root class="transition-colors hover:border-primary">
          <Card.Content class="p-4">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <Badge variant="secondary" class="mb-2 font-mono text-xs"
                  >{response.shortCode}</Badge
                >
                <p class="mb-3 text-sm text-muted-foreground">
                  {truncate(response.content, 120)}
                </p>
                <span class="text-xs text-muted-foreground"
                  >Updated {(() => {
                    const fallback = response.createdAt ?? 'N/A';
                    const timestamp = response.updatedAt ?? fallback;
                    const date = new Date(timestamp);
                    return Number.isNaN(date.getTime())
                      ? fallback
                      : date.toLocaleDateString();
                  })()}</span
                >
              </div>
              <div class="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onclick={() => copyShortCode(response.shortCode)}
                  ><Copy class="h-4 w-4" /></Button
                >
                <Button
                  variant="outline"
                  size="sm"
                  onclick={() =>
                    goto(
                      `/app/accounts/${accountId}/settings/macros?edit=${response.id}`
                    )}><Edit class="h-4 w-4" /></Button
                >
                <Button
                  variant="outline"
                  size="sm"
                  onclick={() => {
                    responseToDelete = response.id;
                    showDeleteDialog = true;
                  }}><Trash2 class="h-4 w-4" /></Button
                >
              </div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>

    <PaginationFooter
      class="mt-6 px-0"
      currentPage={cannedResponsesStore.currentPage}
      totalItems={cannedResponsesStore.total}
      itemsPerPage={cannedResponsesStore.perPage}
      onPageChange={nextPage =>
        cannedResponsesStore.fetch({
          page: nextPage,
          perPage: cannedResponsesStore.perPage,
          search: searchQuery.trim() || undefined,
        })}
    />
  {/if}
</div>

<Dialog.Root bind:open={showDeleteDialog}>
  <Dialog.Content>
    <Dialog.Header>
      <Dialog.Title>Delete canned response?</Dialog.Title>
      <Dialog.Description>This action cannot be undone.</Dialog.Description>
    </Dialog.Header>
    <Dialog.Footer>
      <Dialog.Cancel
        onclick={() => {
          responseToDelete = null;
          showDeleteDialog = false;
        }}>Cancel</Dialog.Cancel
      >
      <Dialog.Action onclick={confirmDelete} disabled={isDeleting}
        >Delete</Dialog.Action
      >
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
