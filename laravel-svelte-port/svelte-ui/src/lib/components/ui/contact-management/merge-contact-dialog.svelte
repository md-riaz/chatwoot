<script lang="ts">
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import { Search, Loader2, AlertTriangle } from 'lucide-svelte';
  import * as Avatar from '$lib/components/ui/avatar';
  import { debounce } from '$lib/utils';
  import type { Contact } from '$lib/api/contacts';
  import * as Alert from '$lib/components/ui/alert';

  interface Props {
    open: boolean;
    primaryContact: Contact;
  }

  let { open = $bindable(false), primaryContact }: Props = $props();

  let searchQuery = $state('');
  let searchResults = $state<Contact[]>([]);
  let isSearching = $state(false);
  let selectedContact = $state<Contact | null>(null);
  let isMerging = $state(false);
  let error = $state<string | null>(null);

  const debouncedSearch = debounce(async (query: string) => {
    if (!query.trim()) {
      searchResults = [];
      return;
    }

    isSearching = true;
    try {
      // searchContacts in store updates the store state,
      // but we probably want a direct API call or use the store's search result
      // without affecting the main contact list if possible.
      // But contactsStore.searchContacts updates allContacts.
      // We should use the API directly to avoid messing up the main list view if this dialog is open on top of it?
      // Actually, if we are on detail page, the list view might not be visible or it's fine.
      // But simpler to use API directly for this specific search.
      // I'll import the API function.
      const { searchContacts } = await import('$lib/api/contacts');
      const response = await searchContacts(
        contactsStore.currentAccountId,
        query
      );
      // Filter out the primary contact
      searchResults = (response.data || []).filter(
        c => c.id !== primaryContact.id
      );
    } catch (e) {
      console.error(e);
    } finally {
      isSearching = false;
    }
  }, 300);

  $effect(() => {
    debouncedSearch(searchQuery);
  });

  async function handleMerge() {
    if (!selectedContact) return;

    try {
      isMerging = true;
      error = null;
      // Merge selectedContact INTO primaryContact
      // So primaryContact is the one we keep
      // selectedContact is the one being deleted/merged
      const success = await contactsStore.mergeContacts(
        primaryContact.id,
        selectedContact.id
      );

      if (success) {
        open = false;
        // The store handles updating the primary contact in the list/store
        // But if we are on the page of the primary contact, it should reactively update?
        // Yes, if we use the store reference.
      } else {
        error = contactsStore.error || 'Failed to merge contacts';
      }
    } catch (e) {
      error = 'An unexpected error occurred';
    } finally {
      isMerging = false;
    }
  }

  function reset() {
    searchQuery = '';
    searchResults = [];
    selectedContact = null;
    error = null;
  }

  $effect(() => {
    if (!open) {
      // Reset state when closed
      // usage of setTimeout to allow animation to finish?
      // Or just reset immediately.
      reset();
    }
  });
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-[500px]">
    <Dialog.Header>
      <Dialog.Title>Merge Contacts</Dialog.Title>
      <Dialog.Description>
        Select a contact to merge into <strong>{primaryContact.name}</strong>.
        The selected contact will be deleted and its conversations will be moved
        to the primary contact.
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4 py-4">
      {#if error}
        <Alert.Root variant="destructive">
          <AlertTriangle class="h-4 w-4" />
          <Alert.Title>Error</Alert.Title>
          <Alert.Description>{error}</Alert.Description>
        </Alert.Root>
      {/if}

      <!-- Search -->
      <div class="space-y-2">
        <label for="merge-search" class="text-sm font-medium"
          >Search contact to merge</label
        >
        <div class="relative">
          <Search
            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"
          />
          <Input
            id="merge-search"
            bind:value={searchQuery}
            placeholder="Search by name, email, or phone..."
            class="pl-9"
          />
          {#if isSearching}
            <Loader2
              class="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 animate-spin text-muted-foreground"
            />
          {/if}
        </div>
      </div>

      <!-- Results or Selection -->
      <div class="min-h-[200px] border rounded-md p-2 overflow-y-auto">
        {#if selectedContact}
          <div
            class="flex items-center justify-between p-3 bg-muted/50 rounded-lg border border-primary/20"
          >
            <div class="flex items-center gap-3">
              <Avatar.Root class="h-10 w-10">
                <Avatar.Image
                  src={selectedContact.thumbnail}
                  alt={selectedContact.name}
                />
                <Avatar.Fallback
                  >{selectedContact.name
                    ?.charAt(0)
                    .toUpperCase()}</Avatar.Fallback
                >
              </Avatar.Root>
              <div>
                <p class="font-medium">{selectedContact.name}</p>
                <p class="text-xs text-muted-foreground">
                  {selectedContact.email}
                </p>
              </div>
            </div>
            <Button
              variant="ghost"
              size="sm"
              onclick={() => (selectedContact = null)}>Change</Button
            >
          </div>
          <div class="flex justify-center my-2">
            <span class="text-xs text-muted-foreground"
              >will be merged into</span
            >
          </div>
          <div class="flex items-center gap-3 p-3 border rounded-lg opacity-80">
            <Avatar.Root class="h-8 w-8">
              <Avatar.Image
                src={primaryContact.thumbnail}
                alt={primaryContact.name}
              />
              <Avatar.Fallback
                >{primaryContact.name?.charAt(0).toUpperCase()}</Avatar.Fallback
              >
            </Avatar.Root>
            <div>
              <p class="font-medium text-sm">{primaryContact.name} (Primary)</p>
            </div>
          </div>
        {:else if searchResults.length > 0}
          <div class="space-y-1">
            {#each searchResults as contact}
              <button
                class="w-full flex items-center gap-3 p-2 hover:bg-muted rounded-md transition-colors text-left"
                onclick={() => (selectedContact = contact)}
              >
                <Avatar.Root class="h-8 w-8">
                  <Avatar.Image src={contact.thumbnail} alt={contact.name} />
                  <Avatar.Fallback
                    >{contact.name?.charAt(0).toUpperCase()}</Avatar.Fallback
                  >
                </Avatar.Root>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-sm truncate">{contact.name}</p>
                  <p class="text-xs text-muted-foreground truncate">
                    {contact.email || contact.phoneNumber || 'No contact info'}
                  </p>
                </div>
              </button>
            {/each}
          </div>
        {:else if searchQuery}
          <p class="text-center text-sm text-muted-foreground py-8">
            No contacts found.
          </p>
        {:else}
          <p class="text-center text-sm text-muted-foreground py-8">
            Type to search for a contact.
          </p>
        {/if}
      </div>
    </div>

    <Dialog.Footer>
      <Button variant="outline" onclick={() => (open = false)}>Cancel</Button>
      <Button
        variant="destructive"
        disabled={!selectedContact || isMerging}
        onclick={handleMerge}
      >
        {isMerging ? 'Merging...' : 'Confirm Merge'}
      </Button>
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
