<script lang="ts">
  import { X, Minimize2 } from 'lucide-svelte';
  import { widgetConfigStore } from '$lib/widget/stores/config.svelte';

  interface Props {
    onclose?: () => void;
  }

  let { onclose }: Props = $props();

  const config = $derived(widgetConfigStore.configuration);
  const businessName = $derived(config?.businessName || 'Support Team');
  const widgetColor = $derived(config?.widgetColor || '#1f93ff');
</script>

<div class="widget-header" style:background={widgetColor}>
  <div class="header-content">
    <div class="header-info">
      <h3 class="business-name">{businessName}</h3>
      <p class="tagline">We're here to help</p>
    </div>
    
    <div class="header-actions">
      <button
        class="header-button"
        onclick={() => widgetConfigStore.minimize()}
        aria-label="Minimize"
      >
        <Minimize2 size={18} />
      </button>
      <button
        class="header-button"
        onclick={onclose}
        aria-label="Close"
      >
        <X size={18} />
      </button>
    </div>
  </div>
</div>

<style>
  .widget-header {
    padding: 16px 20px;
    color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }

  .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .header-info {
    flex: 1;
  }

  .business-name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
  }

  .tagline {
    margin: 2px 0 0 0;
    font-size: 13px;
    opacity: 0.9;
  }

  .header-actions {
    display: flex;
    gap: 8px;
  }

  .header-button {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 6px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    transition: background 0.2s ease;
  }

  .header-button:hover {
    background: rgba(255, 255, 255, 0.3);
  }

  .header-button:active {
    background: rgba(255, 255, 255, 0.4);
  }
</style>
