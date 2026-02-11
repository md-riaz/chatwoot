<!--
  Typing Indicator Component
  Shows when users are typing in a conversation
-->
<script lang="ts">
  import { presenceStore } from '$lib/websocket/presence-store.svelte.js';

  interface Props {
    conversationId: number;
    class?: string;
  }

  let { conversationId, class: className = '' }: Props = $props();

  // Reactive values
  let typingUsers = $derived(
    presenceStore.getTypingUserDetails(conversationId)
  );
  let isAnyoneTyping = $derived(presenceStore.isAnyoneTyping(conversationId));
  let typingSummary = $derived(presenceStore.getTypingSummary(conversationId));
</script>

{#if isAnyoneTyping}
  <div class="typing-indicator {className}" role="status" aria-live="polite">
    <div class="typing-indicator__content">
      <div class="typing-indicator__dots">
        <span class="typing-indicator__dot"></span>
        <span class="typing-indicator__dot"></span>
        <span class="typing-indicator__dot"></span>
      </div>
      <span class="typing-indicator__text">
        {typingSummary}
      </span>
    </div>
  </div>
{/if}

<style lang="postcss">
  .typing-indicator {
    @apply flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground;
  }

  .typing-indicator__content {
    @apply flex items-center gap-2;
  }

  .typing-indicator__dots {
    @apply flex items-center gap-1;
  }

  .typing-indicator__dot {
    @apply w-1.5 h-1.5 bg-muted-foreground rounded-full;
    animation: typing-pulse 1.4s infinite ease-in-out;
  }

  .typing-indicator__dot:nth-child(1) {
    animation-delay: -0.32s;
  }

  .typing-indicator__dot:nth-child(2) {
    animation-delay: -0.16s;
  }

  .typing-indicator__text {
    @apply text-xs;
  }

  @keyframes typing-pulse {
    0%,
    80%,
    100% {
      @apply opacity-30 scale-75;
    }
    40% {
      @apply opacity-100 scale-100;
    }
  }
</style>
