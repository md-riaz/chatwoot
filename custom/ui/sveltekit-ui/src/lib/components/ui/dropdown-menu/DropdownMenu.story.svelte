<script lang="ts">
  import * as DropdownMenu from './index.js';
  import { Button } from '$lib/components/ui/button';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/DropdownMenu" icon="lucide:more-horizontal">
  <Hst.Variant title="Default">
    <div class="flex items-center justify-center p-16">
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          <Button variant="outline">Open Menu</Button>
        </DropdownMenu.Trigger>
        <DropdownMenu.Content>
          <DropdownMenu.Label>My Account</DropdownMenu.Label>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>Profile</DropdownMenu.Item>
          <DropdownMenu.Item>Settings</DropdownMenu.Item>
          <DropdownMenu.Item>Billing</DropdownMenu.Item>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>Log out</DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Shortcuts">
    <div class="flex items-center justify-center p-16">
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          <Button variant="outline">Actions</Button>
        </DropdownMenu.Trigger>
        <DropdownMenu.Content class="w-56">
          <DropdownMenu.Label>Actions</DropdownMenu.Label>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>
            New Conversation
            <DropdownMenu.Shortcut>⌘N</DropdownMenu.Shortcut>
          </DropdownMenu.Item>
          <DropdownMenu.Item>
            Search
            <DropdownMenu.Shortcut>⌘K</DropdownMenu.Shortcut>
          </DropdownMenu.Item>
          <DropdownMenu.Item>
            Settings
            <DropdownMenu.Shortcut>⌘,</DropdownMenu.Shortcut>
          </DropdownMenu.Item>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>
            Help
            <DropdownMenu.Shortcut>?</DropdownMenu.Shortcut>
          </DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="More Actions">
    <div class="flex items-center justify-center p-16">
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          <Button variant="ghost" size="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
          </Button>
        </DropdownMenu.Trigger>
        <DropdownMenu.Content>
          <DropdownMenu.Item>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </DropdownMenu.Item>
          <DropdownMenu.Item>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Duplicate
          </DropdownMenu.Item>
          <DropdownMenu.Item>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Share
          </DropdownMenu.Item>
          <DropdownMenu.Separator />
          <DropdownMenu.Item class="text-destructive focus:text-destructive">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Delete
          </DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Actions">
    <div class="flex items-center justify-center p-16">
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          <Button variant="outline" size="sm">
            Actions
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ml-2"><polyline points="6 9 12 15 18 9"/></svg>
          </Button>
        </DropdownMenu.Trigger>
        <DropdownMenu.Content class="w-48">
          <DropdownMenu.Label>Conversation</DropdownMenu.Label>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>Mark as Resolved</DropdownMenu.Item>
          <DropdownMenu.Item>Snooze</DropdownMenu.Item>
          <DropdownMenu.Item>Assign to...</DropdownMenu.Item>
          <DropdownMenu.Item>Add Label</DropdownMenu.Item>
          <DropdownMenu.Separator />
          <DropdownMenu.Item>Mute Notifications</DropdownMenu.Item>
          <DropdownMenu.Item class="text-destructive focus:text-destructive">
            Block Contact
          </DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
