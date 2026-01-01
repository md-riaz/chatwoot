<script lang="ts">
  import * as MessageBubble from './index.js';
  
  export let Hst: any;
  
  const messages = [
    { id: 1, content: 'Hello! How can I help you today?', variant: 'incoming', time: '10:30 AM', sender: 'Support Agent' },
    { id: 2, content: 'Hi, I have a question about my order #12345', variant: 'outgoing', time: '10:31 AM', status: 'read' },
    { id: 3, content: "Of course! Let me look that up for you. One moment please...", variant: 'incoming', time: '10:32 AM', sender: 'Support Agent' },
    { id: 4, content: 'I found your order. It was shipped yesterday and should arrive by Friday.', variant: 'incoming', time: '10:33 AM', sender: 'Support Agent' },
    { id: 5, content: 'Thank you so much! That helps a lot.', variant: 'outgoing', time: '10:34 AM', status: 'delivered' }
  ];
</script>

<Hst.Story title="Chatwoot/MessageBubble" icon="lucide:message-circle">
  <Hst.Variant title="Incoming Message">
    <div class="p-4 max-w-md bg-background">
      <MessageBubble.Root variant="incoming">
        <MessageBubble.Avatar fallback="SA" />
        <div>
          <MessageBubble.Content variant="incoming">
            <p class="text-sm">Hello! How can I help you today?</p>
          </MessageBubble.Content>
          <MessageBubble.Timestamp time="10:30 AM" />
        </div>
      </MessageBubble.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Outgoing Message">
    <div class="p-4 max-w-md bg-background">
      <MessageBubble.Root variant="outgoing">
        <div class="text-right">
          <MessageBubble.Content variant="outgoing">
            <p class="text-sm">Hi, I have a question about my order.</p>
          </MessageBubble.Content>
          <div class="flex items-center justify-end gap-1 mt-1">
            <MessageBubble.Timestamp time="10:31 AM" />
            <MessageBubble.Status status="read" />
          </div>
        </div>
      </MessageBubble.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Private Note">
    <div class="p-4 max-w-md bg-background">
      <MessageBubble.Root variant="incoming">
        <MessageBubble.Avatar fallback="JD" />
        <div>
          <span class="text-xs text-warning font-medium">Private Note</span>
          <MessageBubble.Content variant="private">
            <p class="text-sm">Customer seems frustrated. Handle with care.</p>
          </MessageBubble.Content>
          <MessageBubble.Timestamp time="10:35 AM" />
        </div>
      </MessageBubble.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Bot Message">
    <div class="p-4 max-w-md bg-background">
      <MessageBubble.Root variant="incoming">
        <MessageBubble.Avatar fallback="🤖" />
        <div>
          <span class="text-xs text-info font-medium">Captain AI</span>
          <MessageBubble.Content variant="bot">
            <p class="text-sm">I can help you with that! Here are some suggestions based on the customer's history...</p>
          </MessageBubble.Content>
          <MessageBubble.Timestamp time="10:36 AM" />
        </div>
      </MessageBubble.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Message Status">
    <div class="p-4 max-w-md bg-background space-y-4">
      <div class="flex items-center gap-4 text-sm">
        <span class="text-muted-foreground">Sent:</span>
        <MessageBubble.Status status="sent" />
      </div>
      <div class="flex items-center gap-4 text-sm">
        <span class="text-muted-foreground">Delivered:</span>
        <MessageBubble.Status status="delivered" />
      </div>
      <div class="flex items-center gap-4 text-sm">
        <span class="text-muted-foreground">Read:</span>
        <MessageBubble.Status status="read" />
      </div>
      <div class="flex items-center gap-4 text-sm">
        <span class="text-muted-foreground">Failed:</span>
        <MessageBubble.Status status="failed" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Thread">
    <div class="p-4 max-w-md bg-background space-y-4">
      {#each messages as msg}
        <MessageBubble.Root variant={msg.variant}>
          {#if msg.variant === 'incoming'}
            <MessageBubble.Avatar fallback={msg.sender?.slice(0, 2) || 'SA'} />
          {/if}
          <div class={msg.variant === 'outgoing' ? 'text-right' : ''}>
            <MessageBubble.Content variant={msg.variant}>
              <p class="text-sm">{msg.content}</p>
            </MessageBubble.Content>
            <div class={`flex items-center gap-1 mt-1 ${msg.variant === 'outgoing' ? 'justify-end' : ''}`}>
              <MessageBubble.Timestamp time={msg.time} />
              {#if msg.status}
                <MessageBubble.Status status={msg.status} />
              {/if}
            </div>
          </div>
        </MessageBubble.Root>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Attachments">
    <div class="p-4 max-w-md bg-background space-y-4">
      <MessageBubble.Root variant="incoming">
        <MessageBubble.Avatar fallback="SA" />
        <div>
          <MessageBubble.Content variant="incoming">
            <p class="text-sm mb-2">Here's the document you requested:</p>
            <div class="flex items-center gap-2 p-2 bg-background/50 rounded border">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              <div>
                <p class="text-sm font-medium">invoice.pdf</p>
                <p class="text-xs text-muted-foreground">256 KB</p>
              </div>
            </div>
          </MessageBubble.Content>
          <MessageBubble.Timestamp time="10:40 AM" />
        </div>
      </MessageBubble.Root>
      
      <MessageBubble.Root variant="outgoing">
        <div class="text-right">
          <MessageBubble.Content variant="outgoing">
            <img src="https://picsum.photos/200/150" alt="Attachment" class="rounded mb-2 max-w-[200px]" />
            <p class="text-sm">Here's a screenshot of the issue</p>
          </MessageBubble.Content>
          <div class="flex items-center justify-end gap-1 mt-1">
            <MessageBubble.Timestamp time="10:41 AM" />
            <MessageBubble.Status status="sent" />
          </div>
        </div>
      </MessageBubble.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
