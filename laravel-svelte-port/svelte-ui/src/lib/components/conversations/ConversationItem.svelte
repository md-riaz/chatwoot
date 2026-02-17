<script lang="ts">
  /**
   * ConversationItem - Individual conversation card
   * Uses the existing conversation-card primitive with full UI/UX parity
   */

  import * as Avatar from '$lib/components/ui/avatar';
  import * as ConversationCard from '$lib/components/ui/conversation-card';
  import { Badge } from '$lib/components/ui/badge';
  import { cn } from '$lib/utils';
  import { getInboxIconByType } from '$lib/utils/inbox';
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

  // Computed values
  const contactName = $derived(contact?.name || 'Unknown');
  const contactThumbnail = $derived(contact?.thumbnail || '');
  const contactStatus = $derived(contact?.availabilityStatus);
  const inboxName = $derived(inbox?.name || '');
  const inboxIcon = $derived(
    inbox ? getInboxIconByType(inbox.channelType) : 'inbox'
  );

  const lastActivityTime = $derived(() => {
    if (!conversation.timestamp) return '';
    try {
      const date = new Date(conversation.timestamp * 1000);
      const now = new Date();
      const diffMs = now.getTime() - date.getTime();
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return 'Just now';
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return `${diffHours}h ago`;
      if (diffDays < 7) return `${diffDays}d ago`;
      return date.toLocaleDateString();
    } catch {
      return '';
    }
  });

  const unreadCount = $derived(conversation.unreadCount || 0);
  const hasUnread = $derived(unreadCount > 0);

  const priorityConfig = $derived(() => {
    const priority = conversation.priority;
    if (!priority) return null;

    const configs: Record<string, { label: string; variant: string }> = {
      urgent: { label: 'Urgent', variant: 'destructive' },
      high: { label: 'High', variant: 'warning' },
      medium: { label: 'Medium', variant: 'secondary' },
      low: { label: 'Low', variant: 'outline' },
    };

    return configs[priority] || null;
  });

  const statusConfig = $derived(() => {
    const status = conversation.status;
    const configs: Record<string, { label: string; variant: string }> = {
      open: { label: 'Open', variant: 'default' },
      resolved: { label: 'Resolved', variant: 'success' },
      pending: { label: 'Pending', variant: 'warning' },
      snoozed: { label: 'Snoozed', variant: 'secondary' },
    };

    return configs[status] || configs.open;
  });

  const lastMessagePreview = $derived(() => {
    // Get last message from messages array if available
    const lastMessage =
      conversation.messages?.[conversation.messages.length - 1];
    const content = lastMessage?.content || '';
    const maxLength = 80;
    return content.length > maxLength
      ? content.substring(0, maxLength) + '...'
      : content;
  });
</script>

<ConversationCard.Root {selected} unread={hasUnread} {onclick}>
  <ConversationCard.Header>
    <div class="flex items-start gap-4 w-full">
      <!-- Contact Avatar -->
      <div class="relative shrink-0">
        <Avatar.Root
          class="h-12 w-12 shadow-sm border border-slate-100 dark:border-slate-800"
        >
          <Avatar.Image src={contactThumbnail} alt={contactName} />
          <Avatar.Fallback
            class="bg-slate-100 dark:bg-slate-800 text-slate-500 text-sm font-bold"
          >
            {contactName.charAt(0).toUpperCase()}
          </Avatar.Fallback>
        </Avatar.Root>
        {#if unreadCount > 0}
          <div
            class="absolute -top-1 -right-1 h-3.5 w-3.5 bg-primary rounded-full border-2 border-white dark:border-slate-900 shadow-sm"
          ></div>
        {/if}
      </div>

      <!-- Conversation Details -->
      <div class="flex flex-col gap-0.5 flex-1 min-w-0">
        <!-- Header Row: Contact Name, Priority, Time -->
        <div class="flex items-center justify-between gap-2">
          <h4
            class={cn(
              'text-[14px] truncate transition-colors',
              hasUnread
                ? 'font-bold text-slate-900 dark:text-slate-100'
                : 'font-semibold text-slate-700 dark:text-slate-300'
            )}
          >
            {contactName}
          </h4>

          <div class="flex items-center gap-1.5 shrink-0">
            <!-- Priority Icon/indicator if needed -->
            {#if priorityConfig()}
              <div
                class={cn(
                  'h-2 w-2 rounded-full',
                  conversation.priority === 'urgent'
                    ? 'bg-destructive shadow-sm shadow-destructive/20'
                    : conversation.priority === 'high'
                      ? 'bg-amber-500'
                      : 'bg-slate-300'
                )}
                title={priorityConfig()?.label}
              ></div>
            {/if}

            <!-- Last Activity Time -->
            <span
              class="text-[11px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-tighter"
            >
              {lastActivityTime()}
            </span>
          </div>
        </div>

        <!-- Message Preview -->
        <ConversationCard.Preview
          message={lastMessagePreview()}
          class={cn(
            'text-[13px] leading-tight line-clamp-2 mt-0.5',
            hasUnread
              ? 'text-slate-600 dark:text-slate-300 font-medium'
              : 'text-slate-500 dark:text-slate-400 font-normal'
          )}
        />

        <!-- Meta Row: Inbox Icon, Labels -->
        <ConversationCard.Meta class="mt-2">
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <!-- Inbox Icon/Name -->
              {#if inbox}
                <div
                  class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400"
                  title={inboxName}
                >
                  <span class="text-[10px] font-bold uppercase tracking-wider"
                    >{inboxName}</span
                  >
                </div>
              {/if}

              <!-- Labels -->
              {#each (conversation.labels || []).slice(0, 2) as label}
                <Badge
                  variant="outline"
                  class="text-[10px] h-5 py-0 px-2 font-medium bg-transparent border-slate-200 dark:border-slate-700 text-slate-500"
                >
                  {label}
                </Badge>
              {/each}
            </div>

            <!-- Unread Count (if preferred over dot) -->
            {#if unreadCount > 1}
              <div
                class="h-5 min-w-5 flex items-center justify-center px-1.5 rounded-full bg-primary text-[10px] font-bold text-primary-foreground shadow-sm"
              >
                {unreadCount > 99 ? '99+' : unreadCount}
              </div>
            {/if}
          </div>
        </ConversationCard.Meta>
      </div>
    </div>
  </ConversationCard.Header>
</ConversationCard.Root>
