<script lang="ts">
  import { Paperclip } from 'lucide-svelte';

  interface Props {
    onattach: (files: File[]) => void;
    disabled?: boolean;
  }

  let { onattach, disabled = false }: Props = $props();

  let fileInput: HTMLInputElement;

  function handleClick() {
    fileInput?.click();
  }

  function handleChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
      onattach(Array.from(target.files));
      target.value = ''; // Reset input
    }
  }
</script>

<button
  class="attachment-button"
  onclick={handleClick}
  {disabled}
  aria-label="Attach file"
>
  <Paperclip size={20} />
</button>

<input
  type="file"
  bind:this={fileInput}
  onchange={handleChange}
  multiple
  accept="image/*,video/*,.pdf,.doc,.docx,.txt"
  class="file-input"
/>

<style>
  .attachment-button {
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

  .attachment-button:hover:not(:disabled) {
    background: #f3f4f6;
    color: #374151;
  }

  .attachment-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .file-input {
    display: none;
  }
</style>
