<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { ArrowLeft } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import MessageList from '$lib/components/messages/MessageList.svelte';
  import MessageComposer from '$lib/components/messages/MessageComposer.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';

  const accountId = $derived($page.params.accountId);
  const rawId = $derived($page.params.id);
  const conversationId = $derived(Number(rawId));
  const selectedConversation = $derived(conversationsStore.selectedConversation);

  let isLoading = $state(false);
  let error = $state<string | null>(null);

  onMount(async () => {
    if (!conversationId) return;

    conversationsStore.setSelectedConversation(conversationId);

    if (!selectedConversation) {
      try {
        isLoading = true;
        await conversationsStore.fetchConversation(conversationId);
      } catch (err) {
        console.error('Failed to load conversation for inbox view', err);
        error = 'Failed to load conversation';
      } finally {
        isLoading = false;
      }
    }
  });

  function handleBack() {
    conversationsStore.setSelectedConversation(null);
    goto(`/app/accounts/${accountId}/inbox-view`);
  }
</script>

{#if !conversationId}
  <div class="flex items-center justify-center h-full">
    <p class="text-sm text-muted-foreground">
      Invalid conversation.
    </p>
  </div>
{:else}
  <div class="flex flex-col h-full">
    <div class="flex items-center gap-3 p-4 border-b bg-background">
      <Button
        variant="ghost"
        size="icon"
        onclick={handleBack}
        class="lg:hidden"
      >
        <ArrowLeft class="h-5 w-5" />
      </Button>

      <div class="flex-1">
        <h2 class="text-sm font-semibold">
          Inbox View Conversation
        </h2>
        <p class="text-xs text-muted-foreground">
          Notifications opened from My Inbox.
        </p>
      </div>
    </div>

    {#if error}
      <div class="flex items-center justify-center h-full">
        <p class="text-sm text-destructive">{error}</p>
      </div>
    {:else if isLoading || !selectedConversation}
      <div class="flex items-center justify-center h-full">
        <p class="text-sm text-muted-foreground">Loading conversation...</p>
      </div>
    {:else}
      <div class="flex-1 overflow-hidden">
        <MessageList conversationId={selectedConversation.id} />
      </div>
      <div class="border-t p-4 bg-background">
        <MessageComposer conversationId={selectedConversation.id} />
      </div>
    {/if}
  </div>
{/if}

