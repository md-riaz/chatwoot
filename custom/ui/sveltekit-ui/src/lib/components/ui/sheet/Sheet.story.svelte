<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Sheet } from './index';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';

  let openRight = $state(false);
  let openLeft = $state(false);
  let openTop = $state(false);
  let openBottom = $state(false);
</script>

<Hst.Story title="Overlays/Sheet" icon="lucide:panel-right">
  <Hst.Variant title="Sheet Positions">
    <div class="p-4 bg-background">
      <div class="flex flex-wrap gap-2">
        <Button onclick={() => openRight = true}>Open Right</Button>
        <Button onclick={() => openLeft = true} variant="outline">Open Left</Button>
        <Button onclick={() => openTop = true} variant="secondary">Open Top</Button>
        <Button onclick={() => openBottom = true} variant="ghost">Open Bottom</Button>
      </div>
    </div>

    <Sheet
      bind:open={openRight}
      side="right"
      title="Edit Profile"
      description="Make changes to your profile here."
    >
      {#snippet children()}
        <div class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input id="name" value="John Doe" />
          </div>
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" value="john@example.com" />
          </div>
          <Button class="w-full">Save changes</Button>
        </div>
      {/snippet}
    </Sheet>

    <Sheet
      bind:open={openLeft}
      side="left"
      title="Navigation"
      description="Browse your workspace"
    >
      {#snippet children()}
        <nav class="space-y-2">
          <a href="#" class="block px-3 py-2 rounded-md hover:bg-accent">Dashboard</a>
          <a href="#" class="block px-3 py-2 rounded-md hover:bg-accent">Conversations</a>
          <a href="#" class="block px-3 py-2 rounded-md hover:bg-accent">Contacts</a>
          <a href="#" class="block px-3 py-2 rounded-md hover:bg-accent">Settings</a>
        </nav>
      {/snippet}
    </Sheet>

    <Sheet
      bind:open={openTop}
      side="top"
      title="Search"
    >
      {#snippet children()}
        <Input placeholder="Search conversations, contacts, messages..." class="w-full" />
      {/snippet}
    </Sheet>

    <Sheet
      bind:open={openBottom}
      side="bottom"
      title="Quick Actions"
    >
      {#snippet children()}
        <div class="flex gap-2">
          <Button class="flex-1">New Conversation</Button>
          <Button variant="outline" class="flex-1">Add Contact</Button>
        </div>
      {/snippet}
    </Sheet>
  </Hst.Variant>
</Hst.Story>
