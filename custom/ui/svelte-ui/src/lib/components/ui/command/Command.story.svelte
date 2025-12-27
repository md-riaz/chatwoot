<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Command } from './index';
  import { Button } from '$lib/components/ui/button';

  const commandItems = [
    {
      id: 'new-conversation',
      label: 'New Conversation',
      description: 'Start a new conversation',
      icon: '💬',
      shortcut: '⌘N',
      group: 'Actions',
    },
    {
      id: 'search-contacts',
      label: 'Search Contacts',
      description: 'Find a contact by name or email',
      icon: '👤',
      shortcut: '⌘K',
      group: 'Actions',
    },
    {
      id: 'toggle-theme',
      label: 'Toggle Theme',
      description: 'Switch between light and dark mode',
      icon: '🌓',
      shortcut: '⌘T',
      group: 'Settings',
    },
    {
      id: 'open-settings',
      label: 'Open Settings',
      description: 'Configure your preferences',
      icon: '⚙️',
      shortcut: '⌘,',
      group: 'Settings',
    },
    {
      id: 'goto-dashboard',
      label: 'Go to Dashboard',
      icon: '📊',
      group: 'Navigation',
    },
    {
      id: 'goto-reports',
      label: 'Go to Reports',
      icon: '📈',
      group: 'Navigation',
    },
  ];

  let commandOpen = $state(false);
</script>

<Hst.Story title="Navigation/Command" icon="lucide:terminal">
  <Hst.Variant title="Command Palette">
    <div class="p-4 bg-background min-h-[400px]">
      <div class="text-center">
        <p class="text-sm text-muted-foreground mb-4">
          Press <kbd class="px-1.5 py-0.5 text-xs bg-muted rounded border">⌘</kbd> + 
          <kbd class="px-1.5 py-0.5 text-xs bg-muted rounded border">K</kbd> or click the button
        </p>
        <Button onclick={() => commandOpen = true}>
          Open Command Palette
        </Button>
      </div>
    </div>

    <Command
      bind:open={commandOpen}
      items={commandItems}
      onSelect={(item) => console.log('Selected:', item)}
    />
  </Hst.Variant>
</Hst.Story>
