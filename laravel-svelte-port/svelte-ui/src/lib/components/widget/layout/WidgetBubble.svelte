<script lang="ts">
  import { MessageCircle } from 'lucide-svelte';

  interface Props {
    onclick?: () => void;
    unreadCount?: number;
    color?: string;
  }

  let { onclick, unreadCount = 0, color = '#1f93ff' }: Props = $props();
</script>

<button
  class="widget-bubble"
  style:--widget-color={color}
  onclick={onclick}
  aria-label="Open chat"
>
  <MessageCircle size={28} strokeWidth={2} />
  
  {#if unreadCount > 0}
    <span class="unread-badge">
      {unreadCount > 99 ? '99+' : unreadCount}
    </span>
  {/if}
</button>

<style>
  .widget-bubble {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--widget-color);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
    z-index: 9999;
  }

  .widget-bubble:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  }

  .widget-bubble:active {
    transform: scale(0.95);
  }

  .widget-bubble :global(svg) {
    color: white;
  }

  .unread-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ef4444;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }

  @media (max-width: 768px) {
    .widget-bubble {
      bottom: 16px;
      right: 16px;
      width: 56px;
      height: 56px;
    }

    .widget-bubble :global(svg) {
      width: 24px;
      height: 24px;
    }
  }
</style>
