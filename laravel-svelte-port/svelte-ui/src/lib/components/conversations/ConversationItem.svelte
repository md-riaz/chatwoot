<script lang="ts">
  import * as Avatar from '$lib/components/ui/avatar';
  import { cn } from '$lib/utils';
  import type { Conversation } from '$lib/api/conversations';
  import type { Contact } from '$lib/api/contacts';
  import type { Inbox } from '$lib/api/inboxes';

  interface Props {
    conversation: Conversation;
    contact?: Contact;
    inbox?: Inbox;
    selected?: boolean;
    onclick?: () => void;
  }

  let {
    conversation,
    contact,
    inbox,
    selected = false,
    onclick,
  }: Props = $props();

  const contactName = $derived(contact?.name || 'Unknown');
  const contactThumbnail = $derived(contact?.thumbnail || '');
  const inboxName = $derived(inbox?.name || '');
  const unreadCount = $derived(conversation.unreadCount || 0);
  const hasUnread = $derived(unreadCount > 0);

  const lastActivityTime = $derived(() => {
    const timestamp = conversation.lastActivityAt || conversation.updatedAt;
    if (!timestamp) return '';

    try {
      const date = new Date(timestamp);
      const now = new Date();
      const diffMs = now.getTime() - date.getTime();
      const diffMinutes = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMinutes < 1) return 'now';
      if (diffMinutes < 60) return `${diffMinutes}m`;
      if (diffHours < 24) return `${diffHours}h`;
      if (diffDays < 30) return `${diffDays}d`;

      return date.toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
      });
    } catch {
      return '';
    }
  });

  const lastMessagePreview = $derived(() => {
    const lastMessage =
      conversation.messages?.[conversation.messages.length - 1]?.content ||
      conversation.additionalAttributes?.lastMessage ||
      '';

    const normalized = String(lastMessage).trim();
    if (!normalized) {
      return 'No messages yet';
    }

    const maxLength = 72;
    return normalized.length > maxLength
      ? `${normalized.slice(0, maxLength)}...`
      : normalized;
  });
</script>

<button
  type="button"
  class={cn(
    'w-full border-b border-slate-200 text-left transition-colors last:border-b-0',
    selected && 'rounded-xl bg-slate-100'
  )}
  onclick={onclick}
>
  <div class="flex items-start gap-3 px-3 py-4">
    <Avatar.Root class="mt-0.5 h-12 w-12 shrink-0 border border-slate-200 bg-slate-50">
      <Avatar.Image src={contactThumbnail} alt={contactName} />
      <Avatar.Fallback class="bg-amber-100 text-sm font-semibold text-amber-700">
        {contactName.charAt(0).toUpperCase()}
      </Avatar.Fallback>
    </Avatar.Root>

    <div class="min-w-0 flex-1">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div
            class={cn(
              'truncate text-[1.05rem] leading-6 text-slate-900',
              hasUnread ? 'font-semibold' : 'font-medium'
            )}
          >
            {contactName}
          </div>
          <div
            class={cn(
              'mt-1 truncate text-sm',
              hasUnread ? 'font-medium text-slate-700' : 'text-slate-600'
            )}
          >
            {lastMessagePreview()}
          </div>
        </div>

        <div class="flex shrink-0 items-center gap-2 pt-1 text-xs text-slate-500">
          {#if hasUnread}
            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
          {/if}
          {#if lastActivityTime()}
            <span>{lastActivityTime()}</span>
          {/if}
        </div>
      </div>

      <div class="mt-3 flex items-center justify-between gap-3">
        <div class="min-w-0">
          {#if inboxName}
            <span class="inline-flex max-w-full items-center rounded-md bg-slate-100 px-2.5 py-1 text-[0.7rem] font-semibold uppercase tracking-[0.08em] text-slate-600">
              <span class="truncate">{inboxName}</span>
            </span>
          {/if}
        </div>

        {#if unreadCount > 0}
          <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[0.65rem] font-semibold text-white">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        {/if}
      </div>
    </div>
  </div>
</button>
