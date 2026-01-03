<script lang="ts">
  import { Smile } from 'lucide-svelte';

  interface Props {
    onselect: (emoji: string) => void;
    disabled?: boolean;
  }

  let { onselect, disabled = false }: Props = $props();

  let isOpen = $state(false);

  const emojis = ['😊', '👍', '❤️', '😂', '🎉', '🙏', '👏', '✨', '🔥', '💯'];

  function handleSelect(emoji: string) {
    onselect(emoji);
    isOpen = false;
  }
</script>

<div class="emoji-button-container">
  <button
    class="emoji-button"
    onclick={() => (isOpen = !isOpen)}
    {disabled}
    aria-label="Add emoji"
  >
    <Smile size={20} />
  </button>

  {#if isOpen}
    <div class="emoji-picker">
      {#each emojis as emoji}
        <button class="emoji-item" onclick={() => handleSelect(emoji)}>
          {emoji}
        </button>
      {/each}
    </div>
  {/if}
</div>

<style>
  .emoji-button-container {
    position: relative;
  }

  .emoji-button {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s ease;
  }

  .emoji-button:hover:not(:disabled) {
    background: #f3f4f6;
    color: #374151;
  }

  .emoji-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .emoji-picker {
    position: absolute;
    bottom: 100%;
    right: 0;
    margin-bottom: 8px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 8px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 10;
  }

  .emoji-item {
    background: none;
    border: none;
    font-size: 24px;
    padding: 8px;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.2s ease;
  }

  .emoji-item:hover {
    background: #f3f4f6;
  }
</style>
