<script lang="ts">
  import type { Message } from '$lib/widget/api/types';
  import MessageAttachment from './MessageAttachment.svelte';

  interface Props {
    message: Message;
    timestamp: string;
  }

  let { message, timestamp }: Props = $props();

  const sender = $derived(message.sender);
  const hasAttachments = $derived(message.attachments && message.attachments.length > 0);
</script>

<div class="agent-message">
  <div class="message-avatar">
    {#if sender?.avatarUrl}
      <img src={sender.avatarUrl} alt={sender.name} />
    {:else}
      <div class="avatar-fallback">
        {sender?.name?.charAt(0).toUpperCase() || 'A'}
      </div>
    {/if}
  </div>

  <div class="message-content">
    <div class="message-header">
      <span class="sender-name">{sender?.name || 'Agent'}</span>
    </div>

    <div class="message-bubble">
      <div class="message-text">
        {@html message.content}
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
</div>

<style>
  .agent-message {
    display: flex;
    gap: 8px;
    align-items: flex-start;
  }

  .message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
  }

  .message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

  .message-content {
    flex: 1;
    min-width: 0;
  }

  .message-header {
    margin-bottom: 4px;
  }

  .sender-name {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
  }

  .message-bubble {
    background: white;
    border-radius: 12px;
    padding: 10px 14px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
  }

  .message-text {
    font-size: 14px;
    line-height: 1.5;
    color: #1f2937;
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
    color: #9ca3af;
  }
</style>
