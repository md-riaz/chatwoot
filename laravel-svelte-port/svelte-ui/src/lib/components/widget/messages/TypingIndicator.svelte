<script lang="ts">
  import { widgetAgentStore } from '$lib/widget/stores/agent.svelte';

  const typingAgents = $derived(widgetAgentStore.typingAgentNames);
  const hasTypingAgents = $derived(typingAgents.length > 0);

  const typingText = $derived(() => {
    if (typingAgents.length === 0) return '';
    if (typingAgents.length === 1) return `${typingAgents[0]} is typing`;
    if (typingAgents.length === 2) return `${typingAgents[0]} and ${typingAgents[1]} are typing`;
    return `${typingAgents.length} agents are typing`;
  });
</script>

{#if hasTypingAgents}
  <div class="typing-indicator">
    <div class="typing-avatar">
      <div class="avatar-fallback">
        {typingAgents[0]?.charAt(0).toUpperCase() || 'A'}
      </div>
    </div>

    <div class="typing-bubble">
      <div class="typing-dots">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </div>
      <div class="typing-text">{typingText()}</div>
    </div>
  </div>
{/if}

<style>
  .typing-indicator {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    animation: fadeIn 0.2s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .typing-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
  }

  .avatar-fallback {
    width: 100%;
    height: 100%;
    background: #1f93ff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
  }

  .typing-bubble {
    background: white;
    border-radius: 12px;
    padding: 10px 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
  }

  .typing-dots {
    display: flex;
    gap: 4px;
    align-items: center;
    height: 20px;
  }

  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #9ca3af;
    animation: bounce 1.4s infinite ease-in-out both;
  }

  .dot:nth-child(1) {
    animation-delay: -0.32s;
  }

  .dot:nth-child(2) {
    animation-delay: -0.16s;
  }

  @keyframes bounce {
    0%,
    80%,
    100% {
      transform: scale(0);
    }
    40% {
      transform: scale(1);
    }
  }

  .typing-text {
    margin-top: 4px;
    font-size: 11px;
    color: #9ca3af;
  }
</style>
