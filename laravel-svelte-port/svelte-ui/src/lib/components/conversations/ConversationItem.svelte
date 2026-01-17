<script lang="ts">
  /**
   * ConversationItem - Individual conversation card
   * Uses the existing conversation-card primitive with full UI/UX parity
   */
  
  import * as Avatar from '$lib/components/ui/avatar';
  import * as ConversationCard from '$lib/components/ui/conversation-card';
  import { Badge } from '$lib/components/ui/badge';
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
  
  let { conversation, contact, inbox, selected = false, onclick }: Props = $props();
  
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
    const lastMessage = conversation.messages?.[conversation.messages.length - 1];
    const content = lastMessage?.content || '';
    const maxLength = 80;
    return content.length > maxLength 
      ? content.substring(0, maxLength) + '...' 
      : content;
  });
</script>

<ConversationCard.Root {selected} unread={hasUnread} {onclick}>
  <ConversationCard.Header>
    <div class="flex items-start gap-3 w-full">
      <!-- Contact Avatar -->
      <Avatar.Root class="h-10 w-10 shrink-0">
        <Avatar.Image src={contactThumbnail} alt={contactName} />
        <Avatar.Fallback>
          {contactName.charAt(0).toUpperCase()}
        </Avatar.Fallback>
      </Avatar.Root>
      
      <!-- Conversation Details -->
      <div class="flex flex-col gap-1 flex-1 min-w-0">
        <!-- Header Row: Contact Name, Priority, Inbox Icon, Time -->
        <div class="flex items-center justify-between gap-2">
          <h4 class="text-sm font-semibold truncate text-foreground">
            {contactName}
          </h4>
          
          <div class="flex items-center gap-2 shrink-0">
            <!-- Priority Badge -->
            {#if priorityConfig()}
              <Badge variant={priorityConfig()?.variant as any} class="text-xs">
                {priorityConfig()?.label}
              </Badge>
            {/if}
            
            <!-- Inbox Icon -->
            {#if inbox}
              <div 
                class="flex items-center justify-center rounded-full bg-muted size-5"
                title={inboxName}
              >
                <span class="text-xs">{inboxIcon.charAt(0)}</span>
              </div>
            {/if}
            
            <!-- Last Activity Time -->
            <span class="text-xs text-muted-foreground whitespace-nowrap">
              {lastActivityTime()}
            </span>
          </div>
        </div>
        
        <!-- Message Preview -->
        <ConversationCard.Preview message={lastMessagePreview()} class="line-clamp-2" />
        
        <!-- Meta Row: Status, Labels, Unread Count -->
        <ConversationCard.Meta>
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-wrap">
              <!-- Status Badge -->
              <Badge variant={statusConfig().variant as any} class="text-xs">
                {statusConfig().label}
              </Badge>
              
              <!-- Labels -->
              {#each (conversation.labels || []).slice(0, 2) as label}
                <Badge variant="outline" class="text-xs">
                  {label}
                </Badge>
              {/each}
              
              {#if (conversation.labels || []).length > 2}
                <Badge variant="outline" class="text-xs">
                  +{(conversation.labels || []).length - 2}
                </Badge>
              {/if}
            </div>
            
            <!-- Unread Count -->
            {#if hasUnread}
              <Badge variant="default" class="ml-auto">
                {unreadCount > 99 ? '99+' : unreadCount}
              </Badge>
            {/if}
          </div>
        </ConversationCard.Meta>
      </div>
    </div>
  </ConversationCard.Header>
</ConversationCard.Root>
