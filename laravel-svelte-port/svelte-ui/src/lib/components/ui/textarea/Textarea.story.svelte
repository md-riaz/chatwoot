<script lang="ts">
  import { Textarea } from './index.js';
  
  export let Hst: any;
  
  let message = $state('');
  let bio = $state('I am a software developer based in San Francisco.');
</script>

<Hst.Story title="Primitives/Textarea" icon="lucide:align-left">
  <Hst.Variant title="Default">
    <div class="flex flex-col gap-4 p-4 max-w-md">
      <Textarea placeholder="Type your message here..." bind:value={message} />
      <p class="text-sm text-muted-foreground">Characters: {message.length}</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="States">
    <div class="flex flex-col gap-4 p-4 max-w-md">
      <div>
        <label class="text-sm font-medium mb-1 block">Default</label>
        <Textarea placeholder="Enter text..." />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">With Value</label>
        <Textarea bind:value={bio} />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Disabled</label>
        <Textarea placeholder="Disabled textarea" disabled />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">Read-only</label>
        <Textarea value="This is read-only content" readonly />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Different Rows">
    <div class="flex flex-col gap-4 p-4 max-w-md">
      <div>
        <label class="text-sm font-medium mb-1 block">2 Rows</label>
        <Textarea rows={2} placeholder="Small textarea" />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">5 Rows</label>
        <Textarea rows={5} placeholder="Medium textarea" />
      </div>
      <div>
        <label class="text-sm font-medium mb-1 block">8 Rows</label>
        <Textarea rows={8} placeholder="Large textarea" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Message Composer">
    <div class="p-4 max-w-md">
      <div class="border rounded-lg p-3 space-y-3">
        <Textarea 
          placeholder="Type your reply..." 
          rows={4}
          class="border-0 focus-visible:ring-0 p-0 resize-none"
        />
        <div class="flex items-center justify-between">
          <div class="flex gap-2">
            <button class="p-2 hover:bg-accent rounded-md text-muted-foreground">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </button>
            <button class="p-2 hover:bg-accent rounded-md text-muted-foreground">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
            </button>
          </div>
          <button class="px-4 py-2 bg-primary text-primary-foreground rounded-md text-sm font-medium hover:bg-primary/90">
            Send
          </button>
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
