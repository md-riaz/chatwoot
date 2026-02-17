<script lang="ts">
  /**
   * MessageBubble - Individual message display
   * Shows message content, sender info, timestamp, and status
   */

  import * as Avatar from '$lib/components/ui/avatar';
  import * as MessageBubble from '$lib/components/ui/message-bubble';
  import { Badge } from '$lib/components/ui/badge';
  import { cn } from '$lib/utils';
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
  <div class="flex justify-center my-6">
    <div
      class="text-[11px] font-medium text-muted-foreground/80 text-center max-w-[90%] bg-secondary/30 px-4 py-1.5 rounded-full border border-secondary/20"
    >
      {@html formattedContent()}
    </div>
  </div>
{:else}
  <MessageBubble.Root
    variant={variant() === 'private' ? 'outgoing' : (variant() as any)}
    class="mb-4"
  >
    <!-- Avatar -->
    {#if showAvatar}
      <MessageBubble.Avatar>
        <Avatar.Root class="h-8 w-8 shadow-sm">
          <Avatar.Image src={senderAvatar} alt={senderName} />
          <Avatar.Fallback class="text-[10px] bg-slate-100 dark:bg-slate-800">
            {senderName.charAt(0).toUpperCase()}
          </Avatar.Fallback>
        </Avatar.Root>
      </MessageBubble.Avatar>
    {/if}

    <!-- Message Content -->
    <MessageBubble.Content variant={variant() as any} class="group relative">
      <!-- Sender Name (for incoming messages) -->
      {#if variant() === 'incoming' && showAvatar}
        <div
          class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1 ml-1"
        >
          {senderName}
        </div>
      {/if}

      <!-- Message Text -->
      <div class="prose prose-sm dark:prose-invert max-w-none">
        {@html formattedContent()}
      </div>

      <!-- Attachments -->
      {#if message.attachments && message.attachments.length > 0}
        <div class="mt-3 space-y-2">
          {#each message.attachments as attachment}
            {#if (attachment as any).fileType === 'image'}
              <img
                src={(attachment as any).dataUrl}
                alt="Attachment"
                class="max-w-xs rounded-xl shadow-sm border border-black/5"
              />
            {:else}
              <a
                href={(attachment as any).dataUrl}
                class="flex items-center gap-3 p-3 bg-background/50 backdrop-blur-sm rounded-xl border shadow-sm text-sm hover:bg-accent transition-colors"
              >
                <div
                  class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary"
                >
                  <!-- Lucide icon placeholder logic if needed, but keeping it simple -->
                  <span class="text-[10px] font-bold">FILE</span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium truncate text-xs">File</div>
                  {#if (attachment as any).fileSize}
                    <div class="text-[10px] text-muted-foreground">
                      {((attachment as any).fileSize / 1024).toFixed(1)} KB
                    </div>
                  {/if}
                </div>
              </a>
            {/if}
          {/each}
        </div>
      {/if}

      <!-- Metadata -->
      <div
        class={cn(
          'flex items-center gap-2 mt-1.5 px-1',
          variant() === 'outgoing' || variant() === 'private'
            ? 'justify-end'
            : 'justify-start'
        )}
      >
        <span
          class="text-[10px] font-medium text-slate-400 dark:text-slate-500 uppercase tracking-tight"
          >{timestamp()}</span
        >
        {#if (message as any).private}
          <div
            class="flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-500 uppercase tracking-wider"
          >
            <span class="h-1 w-1 rounded-full bg-current"></span>
            Private
          </div>
        {/if}
      </div>
    </MessageBubble.Content>
  </MessageBubble.Root>
{/if}
