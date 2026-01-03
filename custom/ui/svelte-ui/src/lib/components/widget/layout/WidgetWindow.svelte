<script lang="ts">
  import type { Snippet } from 'svelte';
  import WidgetHeader from './WidgetHeader.svelte';

  interface Props {
    hasConversation: boolean;
    onclose?: () => void;
    children: Snippet;
  }

  let { hasConversation, onclose, children }: Props = $props();
</script>

<div class="widget-window">
  <WidgetHeader {onclose} />
  
  <div class="widget-content">
    {@render children()}
  </div>
</div>

<style>
  .widget-window {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 380px;
    height: 600px;
    max-height: calc(100vh - 40px);
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 9999;
    animation: slideUp 0.3s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .widget-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  @media (max-width: 768px) {
    .widget-window {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      width: 100%;
      height: 100%;
      max-height: 100vh;
      border-radius: 0;
    }
  }
</style>
