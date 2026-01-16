<script lang="ts">
  /**
   * MessageBubble - Individual message display
   * Shows message content, sender info, timestamp, and status
   */
  
  import * as Avatar from '$lib/components/ui/avatar';
  import * as MessageBubble from '$lib/components/ui/message-bubble';
  import { Badge } from '$lib/components/ui/badge';
  import type { Message as MessagesMessage } from '$lib/api/messages';
  import type { Message as ConversationsMessage } from '$lib/api/conversations';
  
  interface Props {
    message: MessagesMessage | ConversationsMessage;
    isOutgoing?: boolean;
    showAvatar?: boolean;
  }
  
  let { message, isOutgoing = false, showAvatar = true }: Props = $props();
  
  // Derived values
  const variant = $derived(() => {
    if (message.private) return 'private';
    if (message.messageType === 1) return 'incoming'; // Customer message
    return 'outgoing'; // Agent message
  });
  
  const senderName = $derived(message.sender?.name || 'Unknown');
  const senderAvatar = $derived((message as MessagesMessage).sender?.avatarUrl || (message as MessagesMessage).sender?.thumbnail || '');
  
  const timestamp = $derived(() => {
    const created = (message as any).createdAt;
    if (!created && created !== 0) return '';
    try {
      const date = new Date(typeof created === 'number' ? created : created);
      return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } catch {
      return '';
    }
  });
  
  const formattedContent = $derived(() => {
    // Simple formatting - convert newlines to <br>
    return message.content?.replace(/\n/g, '<br>') || '';
  });
</script>

<MessageBubble.Root variant={variant()} class="mb-3">
  <!-- Avatar -->
  {#if showAvatar}
    <MessageBubble.Avatar>
      <Avatar.Root class="h-8 w-8">
        <Avatar.Image src={senderAvatar} alt={senderName} />
        <Avatar.Fallback>
          {senderName.charAt(0).toUpperCase()}
        </Avatar.Fallback>
      </Avatar.Root>
    </MessageBubble.Avatar>
  {/if}
  
  <!-- Message Content -->
  <MessageBubble.Content>
    <!-- Sender Name (for incoming messages) -->
    {#if !isOutgoing && showAvatar}
      <div class="text-xs font-medium text-muted-foreground mb-1">
        {senderName}
      </div>
    {/if}
    
    <!-- Message Text -->
    <div class="rounded-lg p-3 {isOutgoing ? 'bg-primary text-primary-foreground' : 'bg-muted'}">
      {@html formattedContent()}
    </div>
    
    <!-- Attachments -->
    {#if message.attachments && message.attachments.length > 0}
      <div class="mt-2 space-y-2">
        {#each message.attachments as attachment}
          {#if (attachment as any).fileType === 'image'}
            <img 
              src={(attachment as any).dataUrl} 
              alt="Attachment"
              class="max-w-xs rounded-lg"
            />
          {:else}
            <a 
              href={(attachment as any).dataUrl}
              class="flex items-center gap-2 p-2 bg-background rounded-md border text-sm hover:bg-accent"
            >
              <span class="flex-1 truncate">File</span>
              {#if (attachment as any).fileSize}
              <Badge variant="outline" class="text-xs">
                {((attachment as any).fileSize / 1024).toFixed(1)} KB
              </Badge>
              {/if}
            </a>
          {/if}
        {/each}
      </div>
    {/if}
    
    <!-- Metadata -->
    <MessageBubble.Timestamp time={timestamp()} class="inline-flex items-center gap-2 mt-1" />
    {#if (message as any).private}
      <Badge variant="secondary" class="text-xs">Private</Badge>
    {/if}
  </MessageBubble.Content>
</MessageBubble.Root>
