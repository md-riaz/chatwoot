<script lang="ts">
  import { onMount } from 'svelte';
  import { widgetConversationStore } from '$lib/widget/stores/conversation.svelte';
  import { widgetAgentStore } from '$lib/widget/stores/agent.svelte';
  import MessageBubble from './MessageBubble.svelte';
  import TypingIndicator from './TypingIndicator.svelte';
  import { ArrowDown } from 'lucide-svelte';

  interface Props {
    conversationId: number;
  }

  let { conversationId }: Props = $props();

  let scrollContainer: HTMLDivElement;
  let shouldAutoScroll = $state(true);
  let showScrollButton = $state(false);

  const messages = $derived(widgetConversationStore.sortedMessages);
  const isTyping = $derived(widgetAgentStore.isAnyAgentTyping);
  const unreadCount = $derived(widgetConversationStore.unreadCount);

  // Auto-scroll to bottom on new messages
  $effect(() => {
    if (messages.length > 0 && shouldAutoScroll && scrollContainer) {
      scrollToBottom();
    }
  });

  function scrollToBottom(smooth = true) {
    if (scrollContainer) {
      scrollContainer.scrollTo({
        top: scrollContainer.scrollHeight,
        behavior: smooth ? 'smooth' : 'auto',
      });
    }
  }

  function handleScroll() {
    if (!scrollContainer) return;

    const { scrollTop, scrollHeight, clientHeight } = scrollContainer;
    const distanceFromBottom = scrollHeight - scrollTop - clientHeight;

    shouldAutoScroll = distanceFromBottom < 100;
    showScrollButton = distanceFromBottom > 200;
  }

  // Mark messages as read when visible
  onMount(() => {
    scrollToBottom(false);

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            widgetConversationStore.markAsRead();
          }
        });
      },
      { threshold: 0.5 }
    );

    if (scrollContainer) {
      observer.observe(scrollContainer);
    }

    return () => observer.disconnect();
  });
</script>

<div class="message-list" bind:this={scrollContainer} onscroll={handleScroll}>
  {#if messages.length === 0}
    <div class="empty-state">
      <p>Start a conversation!</p>
    </div>
  {:else}
    {#each messages as message (message.id)}
      <MessageBubble {message} />
    {/each}

    {#if isTyping}
      <TypingIndicator />
    {/if}
  {/if}

  {#if showScrollButton && unreadCount > 0}
    <button class="scroll-to-bottom" onclick={() => scrollToBottom()}>
      <ArrowDown size={16} />
      {unreadCount} new message{unreadCount > 1 ? 's' : ''}
    </button>
  {/if}
</div>

<style>
  .message-list {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #f9fafb;
  }

  .empty-state {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    font-size: 14px;
  }

  .scroll-to-bottom {
    position: sticky;
    bottom: 16px;
    align-self: center;
    padding: 8px 16px;
    background: #1f93ff;
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .scroll-to-bottom:hover {
    background: #1e7fd9;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  /* Scrollbar styles */
  .message-list::-webkit-scrollbar {
    width: 6px;
  }

  .message-list::-webkit-scrollbar-track {
    background: transparent;
  }

  .message-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
  }

  .message-list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
  }
</style>
