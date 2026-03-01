<script lang="ts">
  /**
   * Canned Responses Management Page
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/canned/Index.vue
   */

  import { goto } from '$app/navigation';
  import { page } from '$app/state';
  import {
    Search,
    Pen,
    Trash2,
    Copy,
    Plus,
    AlertCircle,
    ChevronUp,
    ChevronDown,
  } from '@lucide/svelte';
  import * as Dialog from '$lib/components/ui/alert-dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { PaginationFooter } from '$lib/components/ui/pagination';
  import { cannedResponsesStore } from '$lib/stores/cannedResponses.svelte';
  import { toast } from 'svelte-sonner';
  import { truncate } from '$lib/utils/format';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';

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

<div class="flex flex-col w-full h-full gap-8">
  <BaseSettingsHeader
    title="Canned Responses"
    description="Canned Responses are saved reply templates which can be used to quickly send out a reply to a conversation. Create canned responses for frequently asked questions to save time."
    linkText="Learn more about canned responses"
    linkUrl="https://www.chatwoot.com/hc/user-guide/articles/1677579680-how-to-add-or-edit-canned-responses"
  >
    {#snippet actions()}
      <Button
        onclick={() => goto(`/app/accounts/${accountId}/settings/macros`)}
      >
        <Plus class="mr-2 h-4 w-4" />
        Add Canned Response
      </Button>
    {/snippet}
  </BaseSettingsHeader>

  <main>
    {#if isLoading}
      <div class="flex justify-center items-center py-20">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
        ></div>
      </div>
    {:else if error}
      <p
        class="flex flex-col items-center justify-center h-full text-base text-muted-foreground py-8"
      >
        Unable to load canned responses. {error}
      </p>
    {:else if cannedResponses.length === 0}
      <p
        class="flex-1 py-20 text-foreground flex items-center justify-center text-base"
      >
        No canned responses found.
      </p>
    {:else}
      <table class="min-w-full overflow-x-auto divide-y divide-border">
        <thead>
          <tr>
            <th class="py-4 pr-4 text-left font-semibold text-muted-foreground">
              Short Code
            </th>
            <th class="py-4 pr-4 text-left font-semibold text-muted-foreground">
              Content
            </th>
            <th class="py-4 text-right font-semibold text-muted-foreground">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border text-foreground">
          {#each cannedResponses as response (response.id)}
            <tr>
              <td
                class="py-4 pr-4 truncate max-w-xs font-medium"
                title={response.shortCode}
              >
                {response.shortCode}
              </td>
              <td
                class="py-4 pr-4 md:break-all whitespace-normal text-muted-foreground"
              >
                {truncate(response.content, 120)}
              </td>
              <td class="py-4 flex justify-end gap-1">
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-muted-foreground hover:text-foreground"
                  title="Copy short code"
                  onclick={() => copyShortCode(response.shortCode)}
                >
                  <Copy class="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-muted-foreground hover:text-foreground"
                  title="Edit"
                  onclick={() =>
                    goto(
                      `/app/accounts/${accountId}/settings/macros?edit=${response.id}`
                    )}
                >
                  <Pen class="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                  title="Delete"
                  onclick={() => {
                    responseToDelete = response.id;
                    showDeleteDialog = true;
                  }}
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>

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
  </main>
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
