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
    if (message.messageType === 2) return 'activity'; // System activity
    if ((message as any).private) return 'private';
    if (message.messageType === 1) return 'incoming'; // Customer message
    return 'outgoing'; // Agent message
  });

  const senderName = $derived(message.sender?.name || 'Unknown');
  const senderAvatar = $derived(
    (message as MessagesMessage).sender?.avatarUrl ||
      (message as MessagesMessage).sender?.thumbnail ||
      ''
  );

  const timestamp = $derived(() => {
    const created = (message as any).createdAt;
    if (!created && created !== 0) return '';
    try {
      const date = new Date(typeof created === 'number' ? created : created);
      return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
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

{#if variant() === 'activity'}
  <div class="flex justify-center my-4">
    <div
      class="text-xs text-muted-foreground text-center max-w-[80%] bg-muted/50 px-3 py-1 rounded-full"
    >
      {@html formattedContent()}
    </div>
  </div>
{:else}
  <MessageBubble.Root
    variant={variant() === 'private' ? 'outgoing' : variant()}
    class="mb-3"
  >
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
      {#if variant() === 'incoming' && showAvatar}
        <div class="text-xs font-medium text-muted-foreground mb-1">
          {senderName}
        </div>
      {/if}

      <!-- Message Text -->
      <div
        class={`rounded-lg p-3 text-sm 
          ${variant() === 'outgoing' ? 'bg-primary text-primary-foreground' : ''}
          ${variant() === 'incoming' ? 'bg-muted' : ''}
          ${variant() === 'private' ? 'bg-amber-50 text-amber-900 border border-amber-200' : ''}
        `}
      >
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
      <div class="flex items-center gap-2 mt-1">
        <span class="text-[10px] text-muted-foreground">{timestamp()}</span>
        {#if (message as any).private}
          <Badge
            variant="outline"
            class="text-[10px] h-4 px-1 border-amber-200 text-amber-700 bg-amber-50"
            >Private</Badge
          >
        {/if}
      </div>
    </MessageBubble.Content>
  </MessageBubble.Root>
{/if}
