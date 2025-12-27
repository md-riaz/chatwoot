<script lang="ts">
  import * as Dialog from './index.js';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  
  export let Hst: any;
  
  let open1 = $state(false);
  let open2 = $state(false);
  let open3 = $state(false);
</script>

<Hst.Story title="Components/Dialog" icon="lucide:square-stack">
  <Hst.Variant title="Default">
    <div class="p-4">
      <Dialog.Root bind:open={open1}>
        <Dialog.Trigger>
          <Button variant="outline">Open Dialog</Button>
        </Dialog.Trigger>
        <Dialog.Content>
          <Dialog.Header>
            <Dialog.Title>Dialog Title</Dialog.Title>
            <Dialog.Description>
              This is a description of the dialog. It provides additional context.
            </Dialog.Description>
          </Dialog.Header>
          <div class="py-4">
            <p>Dialog content goes here.</p>
          </div>
          <Dialog.Footer>
            <Button variant="outline" onclick={() => (open1 = false)}>Cancel</Button>
            <Button onclick={() => (open1 = false)}>Confirm</Button>
          </Dialog.Footer>
        </Dialog.Content>
      </Dialog.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Edit Profile">
    <div class="p-4">
      <Dialog.Root bind:open={open2}>
        <Dialog.Trigger>
          <Button>Edit Profile</Button>
        </Dialog.Trigger>
        <Dialog.Content class="sm:max-w-[425px]">
          <Dialog.Header>
            <Dialog.Title>Edit Profile</Dialog.Title>
            <Dialog.Description>
              Make changes to your profile here. Click save when you're done.
            </Dialog.Description>
          </Dialog.Header>
          <div class="grid gap-4 py-4">
            <div class="grid grid-cols-4 items-center gap-4">
              <Label class="text-right">Name</Label>
              <Input class="col-span-3" value="John Doe" />
            </div>
            <div class="grid grid-cols-4 items-center gap-4">
              <Label class="text-right">Email</Label>
              <Input class="col-span-3" value="john@example.com" />
            </div>
          </div>
          <Dialog.Footer>
            <Button variant="outline" onclick={() => (open2 = false)}>Cancel</Button>
            <Button onclick={() => (open2 = false)}>Save changes</Button>
          </Dialog.Footer>
        </Dialog.Content>
      </Dialog.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Confirm Delete">
    <div class="p-4">
      <Dialog.Root bind:open={open3}>
        <Dialog.Trigger>
          <Button variant="destructive">Delete Item</Button>
        </Dialog.Trigger>
        <Dialog.Content class="sm:max-w-[400px]">
          <Dialog.Header>
            <Dialog.Title>Are you sure?</Dialog.Title>
            <Dialog.Description>
              This action cannot be undone. This will permanently delete this item
              and remove it from our servers.
            </Dialog.Description>
          </Dialog.Header>
          <Dialog.Footer class="pt-4">
            <Button variant="outline" onclick={() => (open3 = false)}>Cancel</Button>
            <Button variant="destructive" onclick={() => (open3 = false)}>Delete</Button>
          </Dialog.Footer>
        </Dialog.Content>
      </Dialog.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="New Conversation">
    <div class="p-4">
      <Dialog.Root>
        <Dialog.Trigger>
          <Button>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Conversation
          </Button>
        </Dialog.Trigger>
        <Dialog.Content class="sm:max-w-[500px]">
          <Dialog.Header>
            <Dialog.Title>Start New Conversation</Dialog.Title>
            <Dialog.Description>
              Select a contact or enter details to start a new conversation.
            </Dialog.Description>
          </Dialog.Header>
          <div class="grid gap-4 py-4">
            <div class="space-y-2">
              <Label>Contact Email</Label>
              <Input type="email" placeholder="contact@example.com" />
            </div>
            <div class="space-y-2">
              <Label>Subject</Label>
              <Input placeholder="What's this about?" />
            </div>
            <div class="space-y-2">
              <Label>Initial Message</Label>
              <textarea
                class="flex min-h-[100px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Type your message..."
              ></textarea>
            </div>
          </div>
          <Dialog.Footer>
            <Button variant="outline">Cancel</Button>
            <Button>Start Conversation</Button>
          </Dialog.Footer>
        </Dialog.Content>
      </Dialog.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
