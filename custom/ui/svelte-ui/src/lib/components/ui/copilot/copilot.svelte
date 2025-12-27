<script lang="ts">
  import { cn } from '$lib/utils';
  import { Avatar, AvatarImage, AvatarFallback } from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Textarea } from '$lib/components/ui/textarea';
  import { ScrollArea } from '$lib/components/ui/scroll-area';
  import { Spinner } from '$lib/components/ui/spinner';

  interface Message {
    id: number;
    role: 'user' | 'assistant';
    content: string;
  }

  interface Agent {
    available_name: string;
    avatar_url?: string;
  }

  let {
    supportAgent,
    messages = [],
    isCaptainTyping = false,
    class: className = '',
    onSendMessage = (_message: string) => {},
    ...restProps
  }: {
    supportAgent?: Agent;
    messages?: Message[];
    isCaptainTyping?: boolean;
    class?: string;
    onSendMessage?: (message: string) => void;
  } = $props();

  let inputValue = $state('');

  function handleSend() {
    if (inputValue.trim()) {
      onSendMessage(inputValue.trim());
      inputValue = '';
    }
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      handleSend();
    }
  }
</script>

<div
  class={cn('flex flex-col h-full bg-card border rounded-lg', className)}
  {...restProps}
>
  <!-- Header -->
  <div class="flex items-center gap-3 p-4 border-b">
    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
      <span class="text-lg">🤖</span>
    </div>
    <div>
      <h3 class="font-medium">Captain AI</h3>
      <p class="text-xs text-muted-foreground">Your AI assistant</p>
    </div>
  </div>

  <!-- Messages -->
  <ScrollArea class="flex-1 p-4">
    <div class="space-y-4">
      {#each messages as message}
        <div
          class={cn(
            'flex gap-3',
            message.role === 'user' ? 'flex-row-reverse' : ''
          )}
        >
          {#if message.role === 'assistant'}
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
              <span class="text-sm">🤖</span>
            </div>
          {:else if supportAgent}
            <Avatar class="h-8 w-8 shrink-0">
              {#if supportAgent.avatar_url}
                <AvatarImage src={supportAgent.avatar_url} alt={supportAgent.available_name} />
              {/if}
              <AvatarFallback>
                {supportAgent.available_name.charAt(0).toUpperCase()}
              </AvatarFallback>
            </Avatar>
          {/if}
          
          <div
            class={cn(
              'max-w-[80%] rounded-lg p-3 text-sm',
              message.role === 'user'
                ? 'bg-primary text-primary-foreground'
                : 'bg-muted'
            )}
          >
            {message.content}
          </div>
        </div>
      {/each}

      {#if isCaptainTyping}
        <div class="flex gap-3">
          <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
            <span class="text-sm">🤖</span>
          </div>
          <div class="bg-muted rounded-lg p-3">
            <Spinner size="sm" />
          </div>
        </div>
      {/if}
    </div>
  </ScrollArea>

  <!-- Input -->
  <div class="p-4 border-t">
    <div class="flex gap-2">
      <Textarea
        bind:value={inputValue}
        placeholder="Ask Captain a question..."
        class="min-h-[40px] max-h-[120px] resize-none"
        onkeydown={handleKeyDown}
      />
      <Button onclick={handleSend} disabled={!inputValue.trim() || isCaptainTyping}>
        Send
      </Button>
    </div>
  </div>
</div>
