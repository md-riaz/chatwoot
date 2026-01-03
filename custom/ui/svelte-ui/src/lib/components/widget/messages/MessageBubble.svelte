<script lang="ts">
  import type { Message } from '$lib/widget/api/types';
  import AgentMessage from './AgentMessage.svelte';
  import UserMessage from './UserMessage.svelte';

  interface Props {
    message: Message;
  }

  let { message }: Props = $props();

  const isAgent = $derived(message.messageType === 0);
  const isUser = $derived(message.messageType === 1);

  function formatTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true,
    });
  }

  const timestamp = $derived(formatTime(message.createdAt));
</script>

<div class="message-bubble" class:agent={isAgent} class:user={isUser}>
  {#if isAgent}
    <AgentMessage {message} {timestamp} />
  {:else if isUser}
    <UserMessage {message} {timestamp} />
  {/if}
</div>

<style>
  .message-bubble {
    display: flex;
    max-width: 80%;
    animation: fadeIn 0.2s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .message-bubble.agent {
    align-self: flex-start;
  }

  .message-bubble.user {
    align-self: flex-end;
  }
</style>
