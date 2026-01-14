<script lang="ts">
  /**
   * Conversation Detail Page
   * Shows message list and composer for a specific conversation
   * Updated with Svelte 5 runes and shadcn-svelte components
   */
  
  import { page } from '$app/stores';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { ArrowLeft } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Input } from '$lib/components/ui/input';
  import { goto } from '$app/navigation';
  import { MessageBubble } from '$lib/components/chat/MessageBubble.svelte';
  import { onMount } from 'svelte';
  
  // Using Svelte 5 runes
  let accountId = $state<number>(parseInt($page.params.accountId));
  let conversationId = $state<number>(parseInt($page.params.id));
  let conversation = $derived(
    conversationsStore.allConversations.find(c => c.id === conversationId)
  );
  let messageText = $state('');
  
  // Load conversation if not already loaded
  onMount(async () => {
    if (!conversation) {
      try {
        await conversationsStore.fetchConversation(conversationId);
      } catch (error) {
        console.error('Failed to load conversation:', error);
      }
    }
  });
  
  function handleBack() {
    goto(`/app/accounts/${accountId}/conversations`);
  }
  
  async function handleSendMessage() {
    if (messageText.trim() === '') return;
    
    // In a real implementation, this would send the message via API
    console.log('Sending message:', messageText);
    
    // Clear the message text after sending
    messageText = '';
  }
</script>

<div class="flex flex-col h-full max-h-[calc(100vh-4rem)]">
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
    
    <div class="flex items-center gap-3 flex-1">
      {#if conversation?.meta?.sender}
        <Avatar>
          <AvatarImage src={conversation.meta.sender.avatarUrl || "https://github.com/shadcn.png"} alt={conversation.meta.sender.name} />
          <AvatarFallback>{conversation.meta.sender.name.charAt(0)}</AvatarFallback>
        </Avatar>
        
        <div>
          <div class="font-medium">
            {conversation.meta.sender.name || 'Customer'}
          </div>
          <div class="flex items-center gap-2">
            <Badge variant="outline">{conversation?.inbox?.name || 'Website'}</Badge>
            <Badge 
              variant={conversation?.status === 'open' ? 'default' : 
                     conversation?.status === 'resolved' ? 'secondary' : 
                     conversation?.status === 'pending' ? 'outline' : 
                     'secondary'}
            >
              {conversation?.status || 'open'}
            </Badge>
          </div>
        </div>
      {/if}
    </div>
  </div>
  
  <div class="flex-1 flex flex-col">
    <!-- Message List -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4">
      {#if conversation?.messages}
        {#each conversation.messages as message}
          {#if message.messageType !== 2} <!-- Skip activity messages -->
            <MessageBubble 
              message={message.content}
              sender={message.sender?.type === 'User' ? 'agent' : 'customer'}
              timestamp={new Date(message.createdAt * 1000).toISOString()}
              isOwn={message.sender?.type === 'User'}
            />
          {/if}
        {/each}
      {/if}
    </div>
    
    <!-- Message Composer -->
    <div class="border-t p-4 bg-background">
      <div class="flex gap-2">
        <Input 
          placeholder="Type your message..." 
          bind:value={messageText}
          onkeypress={(e: KeyboardEvent) => e.key === 'Enter' && handleSendMessage()}
        />
        <Button onclick={handleSendMessage}>Send</Button>
      </div>
    </div>
  </div>
</div>
