<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Badge } from '$lib/components/ui/badge';

  interface Contact {
    id: string;
    name: string;
    email?: string;
    phone?: string;
    avatar?: string;
    conversationCount?: number;
  }

  interface Props {
    primaryContact: Contact;
    duplicateContacts: Contact[];
    onMerge?: (primaryId: string, duplicateIds: string[]) => void;
    onCancel?: () => void;
    class?: string;
  }

  let { primaryContact, duplicateContacts = [], onMerge, onCancel, class: className }: Props = $props();
  let selectedDuplicates = $state<string[]>([]);

  // Initialize selectedDuplicates when duplicateContacts changes
  $effect(() => {
    selectedDuplicates = duplicateContacts.map(c => c.id);
  });

  function toggleDuplicate(id: string) {
    if (selectedDuplicates.includes(id)) {
      selectedDuplicates = selectedDuplicates.filter(d => d !== id);
    } else {
      selectedDuplicates = [...selectedDuplicates, id];
    }
  }

  function handleMerge() {
    onMerge?.(primaryContact.id, selectedDuplicates);
  }
</script>

<div class={cn('space-y-4', className)}>
  <div>
    <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Primary Contact</div>
    <div class="flex items-center gap-3 p-3 rounded-lg border-2 border-woot-500 bg-woot-50 dark:bg-woot-900/20">
      <Avatar>
        <AvatarImage src={primaryContact.avatar} alt={primaryContact.name} />
        <AvatarFallback>{primaryContact.name.slice(0, 2).toUpperCase()}</AvatarFallback>
      </Avatar>
      <div class="flex-1">
        <div class="font-medium text-slate-900 dark:text-slate-100">{primaryContact.name}</div>
        <div class="text-sm text-slate-500">{primaryContact.email || primaryContact.phone}</div>
      </div>
      {#if primaryContact.conversationCount}
        <Badge variant="secondary">{primaryContact.conversationCount} conversations</Badge>
      {/if}
    </div>
  </div>

  <div>
    <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">
      Duplicate Contacts to Merge ({selectedDuplicates.length} selected)
    </div>
    <div class="space-y-2">
      {#each duplicateContacts as contact}
        <button
          class={cn(
            'w-full flex items-center gap-3 p-3 rounded-lg border transition-colors text-left',
            selectedDuplicates.includes(contact.id)
              ? 'border-woot-500 bg-woot-50 dark:bg-woot-900/20'
              : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800'
          )}
          onclick={() => toggleDuplicate(contact.id)}
        >
          <input
            type="checkbox"
            checked={selectedDuplicates.includes(contact.id)}
            class="h-4 w-4 rounded border-slate-300"
          />
          <Avatar size="sm">
            <AvatarImage src={contact.avatar} alt={contact.name} />
            <AvatarFallback>{contact.name.slice(0, 2).toUpperCase()}</AvatarFallback>
          </Avatar>
          <div class="flex-1">
            <div class="font-medium text-slate-900 dark:text-slate-100">{contact.name}</div>
            <div class="text-sm text-slate-500">{contact.email || contact.phone}</div>
          </div>
          {#if contact.conversationCount}
            <Badge variant="outline">{contact.conversationCount} chats</Badge>
          {/if}
        </button>
      {/each}
    </div>
  </div>

  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
    <Button variant="outline" onclick={onCancel}>Cancel</Button>
    <Button onclick={handleMerge} disabled={selectedDuplicates.length === 0}>
      Merge {selectedDuplicates.length + 1} Contacts
    </Button>
  </div>
</div>
