<script lang="ts">
  import type { Message } from '$lib/widget/api/types';
  import MessageAttachment from './MessageAttachment.svelte';

  interface Props {
    message: Message;
    timestamp: string;
  }

  let { message, timestamp }: Props = $props();

  const hasAttachments = $derived(message.attachments && message.attachments.length > 0);
</script>

<div class="user-message">
  <div class="message-bubble">
    <div class="message-text">
      {message.content}
    </div>

    {#if hasAttachments}
      <div class="attachments">
        {#each message.attachments || [] as attachment (attachment.id)}
          <MessageAttachment {attachment} />
        {/each}
      </div>
    {/if}

    <div class="message-time">{timestamp}</div>
  </div>
</div>

<style>
  .user-message {
    display: flex;
    justify-content: flex-end;
  }

  .message-bubble {
    background: #1f93ff;
    color: white;
    border-radius: 12px;
    padding: 10px 14px;
    max-width: 100%;
  }

  .message-text {
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
  }

  .attachments {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .message-time {
    margin-top: 4px;
    font-size: 11px;
    opacity: 0.8;
    text-align: right;
  }
</style>
