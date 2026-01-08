<script lang="ts">
  import * as ReplyBox from './index.js';
  import { Button } from '$lib/components/ui/button';
  import * as Tabs from '$lib/components/ui/tabs';
  
  export let Hst: any;
  
  let message = $state('');
  let privateNote = $state('');
  let attachments = $state([
    { name: 'document.pdf', size: '256 KB', type: 'file' },
    { name: 'screenshot.png', size: '1.2 MB', type: 'image' }
  ]);
  
  function removeAttachment(index: number) {
    attachments = attachments.filter((_, i) => i !== index);
  }
</script>

<Hst.Story title="Chatwoot/ReplyBox" icon="lucide:send">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-lg">
      <ReplyBox.Root>
        <ReplyBox.Input bind:value={message} placeholder="Type your reply..." />
        <ReplyBox.Actions>
          <div class="flex items-center gap-1">
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </Button>
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
            </Button>
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
            </Button>
          </div>
          <Button size="sm" disabled={!message.trim()}>
            Send
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ml-1"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </Button>
        </ReplyBox.Actions>
      </ReplyBox.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Private Note">
    <div class="p-4 max-w-lg">
      <ReplyBox.Root mode="private">
        <div class="flex items-center gap-2 mb-2 text-warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span class="text-sm font-medium">Private Note</span>
        </div>
        <ReplyBox.Input bind:value={privateNote} placeholder="Add a private note visible only to agents..." />
        <ReplyBox.Actions>
          <div class="flex items-center gap-1">
            <Button variant="ghost" size="icon" class="h-8 w-8 text-warning hover:text-warning">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </Button>
          </div>
          <Button size="sm" class="bg-warning hover:bg-warning/90 text-warning-foreground">
            Add Note
          </Button>
        </ReplyBox.Actions>
      </ReplyBox.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Attachments">
    <div class="p-4 max-w-lg">
      <ReplyBox.Root>
        <ReplyBox.Attachments {attachments} onRemove={removeAttachment} />
        <ReplyBox.Input placeholder="Type your message..." />
        <ReplyBox.Actions>
          <div class="flex items-center gap-1">
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </Button>
          </div>
          <Button size="sm">Send</Button>
        </ReplyBox.Actions>
      </ReplyBox.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Reply Mode Tabs">
    <div class="p-4 max-w-lg">
      <Tabs.Root value="reply">
        <Tabs.List class="mb-2">
          <Tabs.Trigger value="reply">Reply</Tabs.Trigger>
          <Tabs.Trigger value="note">Private Note</Tabs.Trigger>
        </Tabs.List>
        <Tabs.Content value="reply">
          <ReplyBox.Root>
            <ReplyBox.Input placeholder="Type your reply to customer..." />
            <ReplyBox.Actions>
              <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon" class="h-8 w-8">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </Button>
                <Button variant="ghost" size="icon" class="h-8 w-8">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                </Button>
              </div>
              <div class="flex items-center gap-2">
                <Button variant="outline" size="sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-1"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
                  Record
                </Button>
                <Button size="sm">Send</Button>
              </div>
            </ReplyBox.Actions>
          </ReplyBox.Root>
        </Tabs.Content>
        <Tabs.Content value="note">
          <ReplyBox.Root mode="private">
            <ReplyBox.Input placeholder="Add a private note..." />
            <ReplyBox.Actions>
              <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon" class="h-8 w-8">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </Button>
              </div>
              <Button size="sm" class="bg-warning hover:bg-warning/90 text-warning-foreground">Add Note</Button>
            </ReplyBox.Actions>
          </ReplyBox.Root>
        </Tabs.Content>
      </Tabs.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Canned Response">
    <div class="p-4 max-w-lg">
      <ReplyBox.Root>
        <div class="flex items-center gap-2 mb-2 p-2 rounded bg-muted text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
          <span class="text-muted-foreground">Using template:</span>
          <span class="font-medium">Welcome Message</span>
          <button class="ml-auto text-muted-foreground hover:text-foreground">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <ReplyBox.Input value="Hello! Thank you for reaching out to us. How can I assist you today?" />
        <ReplyBox.Actions>
          <div class="flex items-center gap-1">
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
            </Button>
          </div>
          <Button size="sm">Send</Button>
        </ReplyBox.Actions>
      </ReplyBox.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
