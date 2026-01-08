<script lang="ts">
  import { widgetConfigStore } from '$lib/widget/stores/config.svelte';
  import { widgetConversationStore } from '$lib/widget/stores/conversation.svelte';
  import WidgetBubble from '$lib/components/widget/layout/WidgetBubble.svelte';
  import WidgetWindow from '$lib/components/widget/layout/WidgetWindow.svelte';
  import MessageList from '$lib/components/widget/messages/MessageList.svelte';
  import MessageInput from '$lib/components/widget/input/MessageInput.svelte';
  import { notifyWidgetOpened, notifyWidgetClosed } from '$lib/widget/utils/iframe';

  const isOpen = $derived(widgetConfigStore.open);
  const unreadCount = $derived(widgetConfigStore.unread);
  const widgetColor = $derived(widgetConfigStore.widgetColor);
  const hasConversation = $derived(widgetConversationStore.hasConversation);
  const conversationId = $derived(widgetConversationStore.conversationId);

  function handleToggle() {
    widgetConfigStore.toggle();
    
    if (widgetConfigStore.open) {
      notifyWidgetOpened();
    } else {
      notifyWidgetClosed();
    }
  }

  function handleClose() {
    widgetConfigStore.close();
    notifyWidgetClosed();
  }
</script>

{#if isOpen}
  <WidgetWindow {hasConversation} onclose={handleClose}>
    {#if hasConversation && conversationId}
      <MessageList {conversationId} />
      <MessageInput {conversationId} />
    {:else}
      <div class="welcome-screen">
        <h2>Welcome!</h2>
        <p>Start a conversation with our team</p>
        <button class="start-button" onclick={() => {}}>
          Start Conversation
        </button>
      </div>
    {/if}
  </WidgetWindow>
{:else}
  <WidgetBubble onclick={handleToggle} {unreadCount} color={widgetColor} />
{/if}

<style>
  .welcome-screen {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
  }

  .welcome-screen h2 {
    margin: 0 0 8px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
  }

  .welcome-screen p {
    margin: 0 0 24px 0;
    font-size: 14px;
    color: #6b7280;
  }

  .start-button {
    padding: 12px 24px;
    background: var(--widget-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .start-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
</style>
