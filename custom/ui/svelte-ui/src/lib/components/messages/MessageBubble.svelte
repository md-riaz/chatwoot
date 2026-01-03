<script lang="ts">
  /**
   * MessageBubble - Individual message display
   * Shows message content, sender info, timestamp, and status
   */
  
  import * as Avatar from '$lib/components/ui/avatar';
  import * as MessageBubble from '$lib/components/ui/message-bubble';
  import { Badge } from '$lib/components/ui/badge';
  import type { Message } from '$lib/api/messages';
  
  interface Props {
    message: Message;
    isOutgoing?: boolean;
    showAvatar?: boolean;
  }
  
  let { message, isOutgoing = false, showAvatar = true }: Props = $props();
  
  // Derived values
  const variant = $derived(() => {
    if (message.private) return 'private';
    if (message.message_type === 1) return 'incoming'; // Customer message
    return 'outgoing'; // Agent message
  });
  
  const senderName = $derived(message.sender?.name || 'Unknown');
  const senderAvatar = $derived(message.sender?.avatar || '');
  
  const timestamp = $derived(() => {
    if (!message.created_at) return '';
    try {
      const date = new Date(message.created_at * 1000);
      return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
      });
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
          {#if attachment.file_type === 'image'}
            <img 
              src={attachment.data_url} 
              alt={attachment.file_name || 'Image'}
              class="max-w-xs rounded-lg"
            />
          {:else}
            <a 
              href={attachment.data_url}
              download={attachment.file_name}
              class="flex items-center gap-2 p-2 bg-background rounded-md border text-sm hover:bg-accent"
            >
              <span class="flex-1 truncate">{attachment.file_name}</span>
              <Badge variant="outline" class="text-xs">
                {(attachment.file_size / 1024).toFixed(1)} KB
              </Badge>
            </a>
          {/if}
        {/each}
      </div>
    {/if}
    
    <!-- Metadata -->
    <MessageBubble.Timestamp>
      <div class="flex items-center gap-2 mt-1">
        <span class="text-xs text-muted-foreground">{timestamp()}</span>
        {#if message.private}
          <Badge variant="secondary" class="text-xs">Private</Badge>
        {/if}
      </div>
    </MessageBubble.Timestamp>
  </MessageBubble.Content>
</MessageBubble.Root>
