<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import {
    createFacebookInbox,
    consumeFacebookCallbackToken,
    getFacebookAuthorizationUrl,
    getFacebookPages,
    type FacebookPageOption,
  } from '$lib/api/inboxes';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Label } from '$lib/components/ui/label';
  import { ArrowLeft, MessageCircle } from 'lucide-svelte';

  let accountId = $derived(Number($page.params.accountId));

  let isLoadingPages = $state(true);
  let isStartingAuth = $state(false);
  let isCreating = $state(false);
  let isConsumingCallback = $state(false);
  let pageOptions = $state<FacebookPageOption[]>([]);
  let selectedPageId = $state('');
  let userAccessToken = $state('');
  let errorMessage = $state<string | null>(null);
  let consumedTokenKey = $state<string | null>(null);

  const availablePages = $derived(pageOptions.filter(item => !item.exists));
  const selectedPage = $derived(
    availablePages.find(item => item.id === selectedPageId) ?? null
  );
  const tokenKey = $derived($page.url.searchParams.get('token_key'));
  const authStatus = $derived($page.url.searchParams.get('facebook_auth'));

  $effect(() => {
    if (tokenKey && tokenKey !== consumedTokenKey) {
      consumeCallback(tokenKey);
      return;
    }

    loadPages();
  });

  async function loadPages() {
    if (!userAccessToken) {
      pageOptions = [];
      isLoadingPages = false;
      return;
    }

    isLoadingPages = true;

    try {
      pageOptions = await getFacebookPages(accountId, userAccessToken || undefined);
    } catch (error: any) {
      errorMessage =
        error?.message || 'Failed to load Facebook pages for this account.';
    } finally {
      isLoadingPages = false;
    }
  }

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  async function startAuthorization() {
    isStartingAuth = true;
    errorMessage = null;

    try {
      const authorizationUrl = await getFacebookAuthorizationUrl(accountId);
      window.location.href = authorizationUrl;
    } catch (error: any) {
      errorMessage =
        error?.message || 'Failed to start Facebook authorization.';
      isStartingAuth = false;
    }
  }

  async function consumeCallback(token: string) {
    isConsumingCallback = true;
    errorMessage = authStatus === 'error'
      ? 'Facebook authorization did not complete successfully.'
      : null;

    try {
      userAccessToken = await consumeFacebookCallbackToken(accountId, token);
      consumedTokenKey = token;
      await goto(`/app/accounts/${accountId}/settings/inboxes/new/facebook`, {
        replaceState: true,
        noScroll: true,
      });
      await loadPages();
    } catch (error: any) {
      errorMessage =
        error?.message || 'Failed to resume Facebook authorization.';
    } finally {
      isConsumingCallback = false;
      isStartingAuth = false;
    }
  }

  async function handleCreateFromSelection() {
    if (!selectedPage?.id) {
      errorMessage = 'Select a Facebook page first.';
      return;
    }

    const pageAccessToken =
      selectedPage.pageAccessToken || '';

    if (!pageAccessToken) {
      errorMessage =
        'The selected page is missing a page access token.';
      return;
    }

    await createAndContinue({
      name: selectedPage.name.trim(),
      pageId: selectedPage.id,
      pageAccessToken,
      userAccessToken,
    });
  }

  async function createAndContinue(params: {
    name: string;
    pageId: string;
    pageAccessToken: string;
    userAccessToken: string;
  }) {
    isCreating = true;
    errorMessage = null;

    try {
      const inbox = await createFacebookInbox(accountId, params);
      inboxesStore.addOrUpdateInbox(inbox);
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
    } catch (error: any) {
      errorMessage =
        error?.message || 'Failed to create Facebook page inbox.';
    } finally {
      isCreating = false;
    }
  }
</script>

<div class="mx-auto max-w-4xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Facebook Page Inbox
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        Connect a Facebook page for Messenger conversations, then continue to agent assignment.
      </p>
    </div>
  </div>

  {#if errorMessage}
    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      {errorMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title class="flex items-center gap-2">
        <MessageCircle class="h-5 w-5" />
        Connect Facebook
      </Card.Title>
      <Card.Description>
        Authorize Facebook, choose a page, and create the Messenger inbox.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <Button
        class="w-full"
        variant="outline"
        onclick={startAuthorization}
        disabled={isStartingAuth || isCreating || isConsumingCallback}
      >
        {isStartingAuth ? 'Redirecting...' : 'Authorize Facebook'}
      </Button>

      <div class="rounded-lg border p-4">
        <div class="mb-2 font-medium">Available Pages</div>
        {#if isConsumingCallback}
          <p class="text-sm text-muted-foreground">
            Completing Facebook authorization...
          </p>
        {:else if isLoadingPages}
          <p class="text-sm text-muted-foreground">
            Loading Facebook pages...
          </p>
        {:else if !userAccessToken}
          <p class="text-sm text-muted-foreground">
            Start Facebook authorization to load the pages available for this account.
          </p>
        {:else if availablePages.length === 0}
          <p class="text-sm text-muted-foreground">
            No eligible Facebook pages were returned for this account.
          </p>
        {:else}
          <div class="space-y-3">
            <div class="space-y-2">
              <Label for="facebook-page-select">Page</Label>
              <select
                id="facebook-page-select"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                bind:value={selectedPageId}
              >
                <option value="">Select a page</option>
                {#each availablePages as pageOption}
                  <option value={pageOption.id}>{pageOption.name}</option>
                {/each}
              </select>
            </div>
            <Button
              class="w-full"
              onclick={handleCreateFromSelection}
              disabled={!selectedPageId || isCreating}
            >
              {isCreating ? 'Creating...' : 'Create from Selected Page'}
            </Button>
          </div>
        {/if}
      </div>
    </Card.Content>
  </Card.Root>
</div>
