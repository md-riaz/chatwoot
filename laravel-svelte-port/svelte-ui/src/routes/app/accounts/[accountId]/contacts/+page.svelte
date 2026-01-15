<script lang="ts">
  /**
   * Contacts List Page
   * Browse and manage all contacts
   */
  
  import { onMount } from 'svelte';
  import { Search, Plus, Mail, Phone, Building } from '@lucide/svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import * as Skeleton from '$lib/components/ui/skeleton';
  
  // Reactive store access
  const contacts = $derived(contactsStore.allContacts);
  const isLoading = $derived(contactsStore.isLoading);
  
  // Local state
  let searchQuery = $state('');
  
  // Filtered contacts
  const filteredContacts = $derived(() => {
    if (!searchQuery.trim()) return contacts;
    const query = searchQuery.toLowerCase();
    return contacts.filter(c => 
      c.name?.toLowerCase().includes(query) ||
      c.email?.toLowerCase().includes(query) ||
      c.phoneNumber?.includes(query)
    );
  });
  
  // Load contacts on mount
  onMount(async () => {
    await contactsStore.fetchContacts();
  });
</script>

<div class="h-full flex flex-col">
  <!-- Header -->
  <div class="p-6 border-b">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-3xl font-bold">Contacts</h1>
        <p class="text-muted-foreground">
          Manage your contact list ({contacts.length} contacts)
        </p>
      </div>
      <Button class="gap-2">
        <Plus class="h-4 w-4" />
        New Contact
      </Button>
    </div>
    
    <!-- Search -->
    <div class="relative">
      <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
      <Input
        bind:value={searchQuery}
        placeholder="Search contacts by name, email, or phone..."
        class="pl-10"
      />
    </div>
  </div>

  <!-- Contacts Grid -->
  <div class="flex-1 overflow-y-auto p-6">
    {#if isLoading}
      <!-- Loading skeleton -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#each Array(6) as _}
          <Card.Root>
            <Card.Content class="p-6">
              <div class="flex items-start gap-4">
                <Skeleton.Root class="h-12 w-12 rounded-full" />
                <div class="flex-1 space-y-2">
                  <Skeleton.Root class="h-4 w-32" />
                  <Skeleton.Root class="h-3 w-40" />
                  <Skeleton.Root class="h-3 w-36" />
                </div>
              </div>
            </Card.Content>
          </Card.Root>
        {/each}
      </div>
    {:else if filteredContacts().length === 0}
      <!-- Empty state -->
      <div class="flex items-center justify-center h-full">
        <div class="text-center">
          {#if searchQuery}
            <p class="text-lg mb-2">No contacts found</p>
            <p class="text-sm text-muted-foreground">
              Try adjusting your search query
            </p>
          {:else}
            <p class="text-lg mb-2">No contacts yet</p>
            <p class="text-sm text-muted-foreground mb-4">
              Start by adding your first contact
            </p>
            <Button class="gap-2">
              <Plus class="h-4 w-4" />
              Add Contact
            </Button>
          {/if}
        </div>
      </div>
    {:else}
      <!-- Contacts Grid -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#each filteredContacts() as contact}
          <Card.Root class="hover:shadow-md transition-shadow cursor-pointer">
            <Card.Content class="p-6">
              <div class="flex items-start gap-4">
                <Avatar.Root class="h-12 w-12">
                  <Avatar.Image src={contact.thumbnail || contact.avatarUrl || ''} alt={contact.name} />
                  <Avatar.Fallback>
                    {contact.name?.charAt(0).toUpperCase() || '?'}
                  </Avatar.Fallback>
                </Avatar.Root>
                
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-2">
                    <h3 class="font-semibold truncate">
                      {contact.name || 'Unknown Contact'}
                    </h3>
                    {#if contact.availabilityStatus}
                      <Badge variant="secondary" class="text-xs">
                        {contact.availabilityStatus}
                      </Badge>
                    {/if}
                  </div>
                  
                  {#if contact.email}
                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                      <Mail class="h-3 w-3 flex-shrink-0" />
                      <span class="truncate">{contact.email}</span>
                    </div>
                  {/if}
                  
                  {#if contact.phoneNumber}
                    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-1">
                      <Phone class="h-3 w-3 flex-shrink-0" />
                      <span class="truncate">{contact.phoneNumber}</span>
                    </div>
                  {/if}
                  
                  {#if contact.company}
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                      <Building class="h-3 w-3 flex-shrink-0" />
                      <span class="truncate">{contact.company}</span>
                    </div>
                  {/if}
                </div>
              </div>
            </Card.Content>
          </Card.Root>
        {/each}
      </div>
    {/if}
  </div>
</div>