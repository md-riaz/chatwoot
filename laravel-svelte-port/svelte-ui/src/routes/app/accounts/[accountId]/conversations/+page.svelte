<script lang="ts">
  /**
   * Conversations Page
   * Main conversations inbox with list and detail view
   */
  
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import ConversationList from '$lib/components/conversations/ConversationList.svelte';
  import MessageList from '$lib/components/messages/MessageList.svelte';
  import MessageComposer from '$lib/components/messages/MessageComposer.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { ArrowLeft } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  
  // Get accountId from route params
  const accountId = $derived($page.params.accountId);
  
  // Get selected conversation from URL or store
  const selectedId = $derived(conversationsStore.selectedConversationId);
  const conversation = $derived(
    selectedId ? conversationsStore.allConversations.find(c => c.id === selectedId) : null
  );
  
  function handleConversationSelect(conversationId: number) {
    goto(`/app/accounts/${accountId}/conversations/${conversationId}`);
  }
  
  function handleBack() {
    conversationsStore.setSelectedConversation(null);
    goto(`/app/accounts/${accountId}/conversations`);
  }
  
  // Show detail view on mobile when conversation is selected
  const showList = $derived(!selectedId || window.innerWidth >= 1024);
  const showDetail = $derived(!!selectedId);
</script>

<div class="flex h-full">
  <!-- Conversation List Sidebar (hidden on mobile when detail is shown) -->
  <div class="w-96 border-r bg-background {showList ? 'block' : 'hidden lg:block'}">
    <ConversationList onConversationSelect={handleConversationSelect} />
  </div>
  
  <!-- Conversation Detail View -->
  <div class="flex-1 {showDetail ? 'flex flex-col' : 'hidden lg:flex lg:flex-col'}">
    {#if conversation}
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
            {conversation.meta?.sender?.name || 'Conversation'}
          </h2>
          <p class="text-sm text-muted-foreground">
            Status: {conversation.status}
          </p>
        </div>
      </div>
      
      <!-- Message List -->
      <div class="flex-1 overflow-hidden">
        <MessageList conversationId={conversation.id} />
      </div>
      
      <!-- Message Composer -->
      <div class="border-t p-4 bg-background">
        <MessageComposer conversationId={conversation.id} />
      </div>
    {:else}
      <!-- Empty state when no conversation selected -->
      <div class="flex items-center justify-center h-full bg-muted/10">
        <div class="text-center text-muted-foreground">
          <p class="text-lg mb-2">Select a conversation</p>
          <p class="text-sm">Choose a conversation from the list to view details</p>
        </div>
      </div>
    {/if}
  </div>
</div>
