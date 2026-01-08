<script lang="ts">
  import type { Campaign } from '$lib/widget/api/types';
  import { X } from 'lucide-svelte';

  interface Props {
    campaign: Campaign;
    ondismiss?: () => void;
  }

  let { campaign, ondismiss }: Props = $props();

  const sender = $derived(campaign.sender);
</script>

<div class="campaign-banner">
  <div class="campaign-content">
    {#if sender?.avatarUrl}
      <img src={sender.avatarUrl} alt={sender.name} class="sender-avatar" />
    {:else}
      <div class="avatar-fallback">
        {sender?.name?.charAt(0).toUpperCase() || 'C'}
      </div>
    {/if}

    <div class="campaign-text">
      <div class="campaign-title">{campaign.title}</div>
      <div class="campaign-message">{campaign.message}</div>
    </div>

    <button class="dismiss-button" onclick={ondismiss} aria-label="Dismiss">
      <X size={16} />
    </button>
  </div>
</div>

<style>
  .campaign-banner {
    margin: 16px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    animation: slideIn 0.3s ease;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .campaign-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
  }

  .sender-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
  }

  .avatar-fallback {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--widget-color, #1f93ff);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 16px;
    flex-shrink: 0;
  }

  .campaign-text {
    flex: 1;
    min-width: 0;
  }

  .campaign-title {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
  }

  .campaign-message {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
  }

  .dismiss-button {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  .dismiss-button:hover {
    background: #f3f4f6;
    color: #6b7280;
  }
</style>
