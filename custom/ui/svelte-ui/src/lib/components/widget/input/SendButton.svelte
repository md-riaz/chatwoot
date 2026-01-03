<script lang="ts">
  import { Send, Loader2 } from 'lucide-svelte';

  interface Props {
    onclick: () => void;
    disabled?: boolean;
    loading?: boolean;
  }

  let { onclick, disabled = false, loading = false }: Props = $props();
</script>

<button
  class="send-button"
  {onclick}
  disabled={disabled || loading}
  aria-label="Send message"
>
  {#if loading}
    <Loader2 size={20} class="loading-icon" />
  {:else}
    <Send size={20} />
  {/if}
</button>

<style>
  .send-button {
    background: #1f93ff;
    border: none;
    padding: 10px;
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
    width: 40px;
    height: 40px;
  }

  .send-button:hover:not(:disabled) {
    background: #1e7fd9;
    transform: scale(1.05);
  }

  .send-button:active:not(:disabled) {
    transform: scale(0.95);
  }

  .send-button:disabled {
    background: #d1d5db;
    cursor: not-allowed;
  }

  :global(.loading-icon) {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }
    to {
      transform: rotate(360deg);
    }
  }
</style>
