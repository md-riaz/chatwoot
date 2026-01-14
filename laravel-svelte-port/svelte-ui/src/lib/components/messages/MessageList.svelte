<script lang="ts">
  /**
   * MessageList - Display list of messages in a conversation
   * Features: Auto-scroll, grouping by date, loading states
   */
  
  import { onMount, tick } from 'svelte';
  import { messagesStore } from '$lib/stores/messages.svelte';
  import * as messagesApi from '$lib/api/messages';
  import MessageBubble from './MessageBubble.svelte';
  import * as Skeleton from '$lib/components/ui/skeleton';
  import { Button } from '$lib/components/ui/button';
  
  interface Props {
    conversationId: number;
  }
  
  let { conversationId }: Props = $props();
  
  // Reactive store access
  const messages = $derived(messagesStore.sortedMessages);
  const isLoading = $derived(messagesStore.isLoading);
  
  // Local state
  let scrollContainer: HTMLElement | undefined;
  let shouldAutoScroll = $state(true);
  let isAtBottom = $state(true);
  
  // Group messages by date
  const messagesByDate = $derived(() => {
    const groups: Record<string, typeof messages> = {};
    
    messages.forEach(message => {
      if (!message.createdAt) return;
      
      const date = new Date(message.createdAt * 1000);
      const dateKey = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      });
      
      if (!groups[dateKey]) {
        groups[dateKey] = [];
      }
      groups[dateKey].push(message);
    });
    
    return groups;
  });
  
  // Auto-scroll to bottom on new messages
  async function scrollToBottom(smooth = true) {
    await tick();
    if (scrollContainer) {
      scrollContainer.scrollTo({
        top: scrollContainer.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto',
      });
    }
  }
  
  // Handle scroll event
  function handleScroll() {
    if (!scrollContainer) return;
    
    const { scrollTop, scrollHeight, clientHeight } = scrollContainer;
    const distanceFromBottom = scrollHeight - scrollTop - clientHeight;
    
    isAtBottom = distanceFromBottom < 100;
    shouldAutoScroll = isAtBottom;
    
    // Load more messages when scrolled to top
    if (scrollTop < 100 && !isLoading && messages.length > 0) {
      // TODO: Load previous messages
      console.log('Load previous messages');
    }
  }
  
  // Scroll to bottom when new messages arrive
  $effect(() => {
    if (messages.length > 0 && shouldAutoScroll) {
      scrollToBottom();
    }
  });
  
  // Load messages on mount
  onMount(async () => {
    try {
      const loadedMessages = await messagesApi.getMessages(conversationId);
      messagesStore.setMessages(loadedMessages);
      scrollToBottom(false);
    } catch (err) {
      console.error('Failed to load messages:', err);
    }
  });
  
  // Update when conversation changes
  $effect(() => {
    if (conversationId) {
      messagesApi.getMessages(conversationId).then(loadedMessages => {
        messagesStore.setMessages(loadedMessages);
      }).catch(err => {
        console.error('Failed to load messages:', err);
      });
    }
  });
</script>

<div class="flex flex-col h-full">
  <!-- Messages Container -->
  <div 
    class="flex-1 overflow-y-auto p-4 space-y-6"
    bind:this={scrollContainer}
    onscroll={handleScroll}
  >
    {#if isLoading && messages.length === 0}
      <!-- Loading skeleton -->
      <div class="space-y-4">
        {#each Array(5) as _, i (i)}
          <div class="flex gap-2 {i % 2 === 0 ? '' : 'flex-row-reverse'}">
            <Skeleton.Root class="h-8 w-8 rounded-full flex-shrink-0" />
            <div class="flex flex-col gap-2 flex-1 max-w-md">
              <Skeleton.Root class="h-4 w-24" />
              <Skeleton.Root class="h-16 w-full rounded-lg" />
              <Skeleton.Root class="h-3 w-16" />
            </div>
          </div>
        {/each}
      </div>
    {:else if messages.length === 0}
      <!-- Empty state -->
      <div class="flex items-center justify-center h-full text-center">
        <div class="text-muted-foreground">
          <p class="text-lg mb-2">No messages yet</p>
          <p class="text-sm">Start the conversation by sending a message below</p>
        </div>
      </div>
    {:else}
      <!-- Messages grouped by date -->
      {#each Object.entries(messagesByDate()) as [date, dateMessages]}
        <div class="space-y-3">
          <!-- Date separator -->
          <div class="flex items-center justify-center my-4">
            <div class="px-3 py-1 bg-muted rounded-full text-xs text-muted-foreground">
              {date}
            </div>
          </div>
          
          <!-- Messages for this date -->
          {#each dateMessages as message (message.id)}
            <MessageBubble
              {message}
              isOutgoing={message.messageType === 0}
              showAvatar={true}
            />
          {/each}
        </div>
      {/each}
      
      <!-- Loading more indicator -->
      {#if isLoading}
        <div class="flex justify-center py-2">
          <Skeleton.Root class="h-8 w-24" />
        </div>
      {/if}
    {/if}
  </div>
  
  <!-- Scroll to bottom button -->
  {#if !isAtBottom && messages.length > 0}
    <div class="absolute bottom-4 right-4">
      <Button
        variant="secondary"
        size="icon"
        class="rounded-full shadow-lg"
        onclick={() => scrollToBottom()}
      >
        ↓
      </Button>
    </div>
  {/if}
</div>
