<script lang="ts">
  import { widgetConversationStore } from '$lib/widget/stores/conversation.svelte';
  import AttachmentButton from './AttachmentButton.svelte';
  import EmojiButton from './EmojiButton.svelte';
  import SendButton from './SendButton.svelte';
  import { X } from 'lucide-svelte';

  interface Props {
    conversationId: number;
    disabled?: boolean;
  }

  let { conversationId, disabled = false }: Props = $props();

  let message = $state('');
  let attachments = $state<File[]>([]);
  let textareaEl: HTMLTextAreaElement;

  const isSending = $derived(widgetConversationStore.sending);
  const canSend = $derived((message.trim() || attachments.length > 0) && !isSending);

  // Auto-resize textarea
  $effect(() => {
    if (textareaEl && message) {
      textareaEl.style.height = 'auto';
      textareaEl.style.height = `${Math.min(textareaEl.scrollHeight, 120)}px`;
    }
  });

  async function handleSend() {
    if (!canSend || disabled) return;

    const success = await widgetConversationStore.sendMessage(message, attachments);

    if (success) {
      // Clear form
      message = '';
      attachments = [];
      if (textareaEl) {
        textareaEl.style.height = 'auto';
        textareaEl.focus();
      }
    }
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  }

  function handleAttachment(files: File[]) {
    attachments = [...attachments, ...files];
  }

  function removeAttachment(index: number) {
    attachments = attachments.filter((_, i) => i !== index);
  }

  function handleEmojiSelect(emoji: string) {
    message += emoji;
    textareaEl?.focus();
  }
</script>

<div class="message-input">
  {#if attachments.length > 0}
    <div class="attachments-preview">
      {#each attachments as file, i (i)}
        <div class="attachment-chip">
          <span class="file-name">{file.name}</span>
          <button
            class="remove-button"
            onclick={() => removeAttachment(i)}
            aria-label="Remove attachment"
          >
            <X size={14} />
          </button>
        </div>
      {/each}
    </div>
  {/if}

  <div class="input-row">
    <AttachmentButton onattach={handleAttachment} disabled={disabled || isSending} />

    <textarea
      bind:this={textareaEl}
      bind:value={message}
      placeholder="Type your message..."
      rows="1"
      disabled={disabled || isSending}
      onkeydown={handleKeyDown}
      class="message-textarea"
    />

    <EmojiButton onselect={handleEmojiSelect} disabled={disabled || isSending} />
    <SendButton onclick={handleSend} disabled={!canSend || disabled} loading={isSending} />
  </div>
</div>

<style>
  .message-input {
    padding: 12px 16px;
    background: white;
    border-top: 1px solid #e5e7eb;
  }

  .attachments-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
  }

  .attachment-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: #f3f4f6;
    border-radius: 12px;
    font-size: 13px;
    color: #374151;
  }

  .file-name {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .remove-button {
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s ease;
  }

  .remove-button:hover {
    color: #ef4444;
  }

  .input-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
  }

  .message-textarea {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    max-height: 120px;
    transition: border-color 0.2s ease;
  }

  .message-textarea:focus {
    outline: none;
    border-color: #1f93ff;
  }

  .message-textarea:disabled {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
  }
</style>
