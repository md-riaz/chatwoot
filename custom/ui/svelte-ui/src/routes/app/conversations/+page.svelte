<script lang="ts">
  /**
   * Conversations Page
   * Main conversations inbox with list and detail view
   */
  
  import { goto } from '$app/navigation';
  import ConversationList from '$lib/components/conversations/ConversationList.svelte';
  
  function handleConversationSelect(conversationId: number) {
    goto(`/app/conversations/${conversationId}`);
  }
</script>

<div class="flex h-full">
  <!-- Conversation List Sidebar -->
  <div class="w-96 border-r bg-background">
    <ConversationList onConversationSelect={handleConversationSelect} />
  </div>
  
  <!-- Conversation Detail View (placeholder) -->
  <div class="flex-1 flex items-center justify-center bg-muted/10">
    <div class="text-center text-muted-foreground">
      <p class="text-lg mb-2">Select a conversation</p>
      <p class="text-sm">Choose a conversation from the list to view details</p>
    </div>
  </div>
</div>
