<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { getInstagramAuthorizationUrl } from '$lib/api/inboxes';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { ArrowLeft, Instagram } from 'lucide-svelte';

  let accountId = $derived(Number($page.params.accountId));
  let isStartingAuthorization = $state(false);
  let errorMessage = $derived($page.url.searchParams.get('error_message'));

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  async function startAuthorization() {
    isStartingAuthorization = true;

    try {
      const authorizationUrl = await getInstagramAuthorizationUrl(accountId);
      window.location.href = authorizationUrl;
    } catch (error: any) {
      isStartingAuthorization = false;
      const message = error?.message || 'Failed to start Instagram authorization.';
      await goto(
        `/app/accounts/${accountId}/settings/inboxes/new/instagram?error_message=${encodeURIComponent(message)}&code=500`,
        { replaceState: true, noScroll: true }
      );
    }
  }
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Instagram Inbox
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        Connect an Instagram professional account and continue to inbox setup.
      </p>
    </div>
  </div>

  <Card.Root>
    <Card.Content class="flex flex-col items-center justify-center gap-5 px-8 py-12 text-center">
      <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 via-rose-500 to-amber-400 text-white">
        <Instagram class="h-8 w-8" />
      </div>

      <div class="space-y-2">
        <h2 class="text-2xl font-semibold text-foreground">
          Connect Your Instagram Profile
        </h2>
        <p class="max-w-xl text-sm text-muted-foreground">
          Authorize Instagram to create or reconnect the inbox for your professional account.
        </p>
      </div>

      {#if errorMessage}
        <div class="w-full max-w-xl rounded border border-red-200 bg-red-50 px-4 py-3 text-left text-red-800">
          {errorMessage}
        </div>
      {/if}

      <Button
        class="rounded-full bg-gradient-to-r from-[#833AB4] via-[#FD1D1D] to-[#FCAF45] px-6 text-white hover:opacity-95"
        onclick={startAuthorization}
        disabled={isStartingAuthorization}
      >
        {isStartingAuthorization
          ? 'Redirecting...'
          : 'Continue with Instagram'}
      </Button>
    </Card.Content>
  </Card.Root>
</div>
