<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { customViewsStore } from '$lib/stores/customViews.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import ConversationList from '$lib/components/conversations/ConversationList.svelte';
  import { goto } from '$app/navigation';
  import MessageList from '$lib/components/messages/MessageList.svelte';
  import MessageComposer from '$lib/components/messages/MessageComposer.svelte';
  import { ArrowLeft } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';

  const accountId = $derived(Number($page.params.accountId));
  const viewId = $derived(Number($page.params.id));
  
  const selectedId = $derived(conversationsStore.selectedConversationId);
  const conversation = $derived(
    selectedId ? conversationsStore.allConversations.find(c => c.id === selectedId) : null
  );

  let isLoading = $state(false);
  let viewName = $state('');

  $effect(() => {
    if (viewId) {
      // Ensure custom views are loaded
      if (customViewsStore.allCustomViews.length === 0 && !customViewsStore.isLoading) {
          customViewsStore.fetchCustomViews().then(() => loadCustomView());
      } else {
          loadCustomView();
      }
    }
  });

  async function loadCustomView() {
    isLoading = true;
    try {
        let view = customViewsStore.allCustomViews.find(v => v.id === viewId);
        if (!view) {
            return;
        }
        viewName = view.name;
        
        // customView.query is the payload for filterConversations
        // The API expects { payload: [...] } or just [...] depending on how it's stored.
        // Chatwoot Laravel store returns query as is.
        // And filterConversations expects `payload` which is array.
        // If view.query has `payload` property, use it.
        
        const queryPayload = view.query.payload || view.query;
        
        await conversationsStore.fetchFilteredConversations(queryPayload);
    } catch (e) {
        console.error(e);
    } finally {
        isLoading = false;
    }
  }

  function handleConversationSelect(conversationId: number) {
    conversationsStore.setSelectedConversation(conversationId);
  }
  
  function handleBack() {
    conversationsStore.setSelectedConversation(null);
  }

  const showList = $derived(!selectedId || window.innerWidth >= 1024);
  const showDetail = $derived(!!selectedId);
</script>

<div class="flex h-full">
  <!-- Conversation List Sidebar -->
  <div class="w-96 border-r bg-background {showList ? 'block' : 'hidden lg:block'}">
    <div class="p-4 border-b font-medium flex items-center gap-2">
        <span class="text-lg font-semibold">{viewName || 'Custom View'}</span>
    </div>
    <ConversationList onConversationSelect={handleConversationSelect} />
  </div>
  
  <!-- Detail View -->
  <div class="flex-1 {showDetail ? 'flex flex-col' : 'hidden lg:flex lg:flex-col'}">
    {#if conversation}
       <div class="flex items-center gap-3 p-4 border-b bg-background">
        <Button variant="ghost" size="icon" onclick={handleBack} class="lg:hidden">
          <ArrowLeft class="h-5 w-5" />
        </Button>
        <div class="flex-1">
             <h2 class="text-lg font-semibold">{conversation.meta?.sender?.name || 'Conversation'}</h2>
             <p class="text-sm text-muted-foreground">Status: {conversation.status}</p>
        </div>
       </div>
       <div class="flex-1 overflow-hidden">
        <MessageList conversationId={conversation.id} />
       </div>
       <div class="border-t p-4 bg-background">
        <MessageComposer conversationId={conversation.id} />
       </div>
    {:else}
       <div class="flex items-center justify-center h-full bg-muted/10">
        <div class="text-center text-muted-foreground">
          <p class="text-lg mb-2">Select a conversation</p>
          <p class="text-sm">Choose a conversation from the list to view details</p>
        </div>
       </div>
    {/if}
  </div>
</div>
