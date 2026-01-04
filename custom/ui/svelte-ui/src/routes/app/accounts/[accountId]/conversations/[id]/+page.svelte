<script lang="ts">
  /**
   * Conversation Detail Page
   * Shows message list and composer for a specific conversation
   */
  
  import { page } from '$app/stores';
  import MessageList from '$lib/components/messages/MessageList.svelte';
  import MessageComposer from '$lib/components/messages/MessageComposer.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { ArrowLeft } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { goto } from '$app/navigation';
  
  // Get params from route
  const accountId = $derived($page.params.accountId);
  const conversationId = $derived(parseInt($page.params.id));
  const conversation = $derived(
    conversationsStore.allConversations.find(c => c.id === conversationId)
  );
  
  function handleBack() {
    goto(`/app/accounts/${accountId}/conversations`);
  }
</script>

<div class="flex flex-col h-full">
  <!-- Header -->
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
      <h2 class="text-lg font-semibold">
        {conversation?.meta?.sender?.name || 'Conversation'}
      </h2>
      {#if conversation}
        <p class="text-sm text-muted-foreground">
          Status: {conversation.status}
        </p>
      {/if}
    </div>
  </div>
  
  <!-- Message List -->
  <div class="flex-1 overflow-hidden">
    <MessageList {conversationId} />
  </div>
  
  <!-- Message Composer -->
  <div class="border-t p-4 bg-background">
    <MessageComposer {conversationId} />
  </div>
</div>
