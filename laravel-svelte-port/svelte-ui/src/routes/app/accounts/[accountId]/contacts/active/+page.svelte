<script lang="ts">
  /**
   * Active Contacts Page
   * Browse contacts that are currently online/active
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import {
    Search,
    Plus,
    Mail,
    Phone,
    Building,
    MapPin,
    Filter,
    ArrowUpDown,
    MoreVertical,
    Check,
    ChevronRight,
    Users,
  } from 'lucide-svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import { debounce } from '$lib/utils';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as Card from '$lib/components/ui/card';
  import * as Dialog from '$lib/components/ui/dialog';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Input } from '$lib/components/ui/input';
  import * as Skeleton from '$lib/components/ui/skeleton';
  import { PaginationFooter } from '$lib/components/ui/pagination';
  import ContactForm from '$lib/components/ui/contact-management/contact-form/contact-form.svelte';
  import type { Contact } from '$lib/api/contacts';

  // Route params
  const accountId = $derived(parseInt($page.params.accountId ?? '', 10));

  // Reactive store access
  const contacts = $derived(contactsStore.allContacts);
  const isLoading = $derived(contactsStore.isLoading);
  const currentPage = $derived(contactsStore.currentPage);
  const hasMorePages = $derived(contactsStore.hasMorePages);

  // Local state
  let searchQuery = $state('');
  let showCreateModal = $state(false);
  let isCreating = $state(false);
  let sortBy = $state('last_activity_at');
  let sortOrder = $state<'asc' | 'desc'>('desc');
  let selectedIds = $state<Set<number>>(new Set());

  // Sort options
  const sortOptions = [
    { key: 'name', label: 'Name' },
    { key: 'last_activity_at', label: 'Last Activity' },
    { key: 'created_at', label: 'Created Date' },
  ];

  // Derive current sort label
  const currentSortLabel = $derived(
    sortOptions.find(opt => opt.key === sortBy)?.label || 'Sort'
  );

  // Selection state
  const hasSelection = $derived(selectedIds.size > 0);
  const allSelected = $derived(
    contacts.length > 0 && contacts.every(c => selectedIds.has(c.id))
  );

  // Total items for pagination
  const totalItems = $derived(
    hasMorePages ? currentPage * 15 + 1 : contacts.length
  );

  // Debounced search function
  const debouncedSearch = debounce(async (query: string) => {
    if (query.trim()) {
      await contactsStore.searchContacts(query);
    } else {
      await contactsStore.fetchActiveContacts({
        sort: sortBy,
        sort_direction: sortOrder,
      });
    }
  }, 300);

  // Effect to trigger search when query changes
  $effect(() => {
    debouncedSearch(searchQuery);
  });

  // Handle sort change
  async function handleSortChange(sort: string) {
    sortBy = sort;
    await contactsStore.fetchActiveContacts({
      sort: sortBy,
      sort_direction: sortOrder,
      page: 1,
    });
  }

  // Toggle sort order
  async function toggleSortOrder() {
    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
    await contactsStore.fetchActiveContacts({
      sort: sortBy,
      sort_direction: sortOrder,
      page: 1,
    });
  }

  // Handle page change
  async function handlePageChange(page: number) {
    await contactsStore.fetchActiveContacts({
      sort: sortBy,
      sort_direction: sortOrder,
      page,
    });
  }

  // Toggle contact selection
  function toggleSelection(contactId: number) {
    if (selectedIds.has(contactId)) {
      selectedIds.delete(contactId);
    } else {
      selectedIds.add(contactId);
    }
    selectedIds = new Set(selectedIds);
  }

  // Toggle select all
  function toggleSelectAll() {
    if (allSelected) {
      selectedIds.clear();
    } else {
      contacts.forEach(c => selectedIds.add(c.id));
    }
    selectedIds = new Set(selectedIds);
  }

  // Navigate to contact detail
  function viewContactDetails(contact: Contact) {
    goto(`/app/accounts/${accountId}/contacts/${contact.id}`);
  }

  // Handle contact creation
  async function handleCreateContact(event: CustomEvent<any>) {
    const contactData = event.detail;
    const avatarFile = contactData._avatarFile;
    const { _avatarFile, ...apiData } = contactData;

    try {
      isCreating = true;
      const newContact = await contactsStore.createContact(apiData);

      if (newContact) {
        if (avatarFile) {
          await contactsStore.updateContact(newContact.id, {
            avatar: avatarFile,
          });
        }
        showCreateModal = false;
      }
    } catch (error) {
      console.error('Failed to create contact', error);
    } finally {
      isCreating = false;
    }
  }

  // Load active contacts on mount
  onMount(async () => {
    await contactsStore.fetchActiveContacts({
      sort: sortBy,
      sort_direction: sortOrder,
    });
  });
</script>

<div class="h-full flex flex-col bg-background max-w-[60rem] w-full mx-auto">
  <!-- Header -->
  <div class="flex items-center justify-between px-6 py-4 border-b">
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <Users class="h-5 w-5 text-green-500" />
        <h1 class="text-xl font-medium">Active Contacts</h1>
      </div>
      {#if hasSelection}
        <Badge variant="secondary">{selectedIds.size} selected</Badge>
      {/if}
    </div>

    <div class="flex items-center gap-2">
      <!-- Search -->
      <div class="relative w-64">
        <Search
          class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"
        />
        <Input
          bind:value={searchQuery}
          placeholder="Search active contacts..."
          class="pl-9 h-9"
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1 border-l pl-2 ml-2">
        <!-- Filter button -->
        <Button
          variant="ghost"
          size="icon"
          class="h-9 w-9 text-muted-foreground"
        >
          <Filter class="h-4 w-4" />
        </Button>

        <!-- Sort dropdown -->
        <DropdownMenu.Root>
          <DropdownMenu.Trigger>
            {#snippet child({ props })}
              <Button
                {...props}
                variant="ghost"
                size="sm"
                class="h-9 text-muted-foreground gap-1"
              >
                <ArrowUpDown class="h-4 w-4" />
                <span class="text-xs">{currentSortLabel}</span>
              </Button>
            {/snippet}
          </DropdownMenu.Trigger>
          <DropdownMenu.Content align="end" class="w-48">
            <DropdownMenu.Label>Sort by</DropdownMenu.Label>
            <DropdownMenu.Separator />
            {#each sortOptions as option}
              <DropdownMenu.Item
                onclick={() => handleSortChange(option.key)}
                class="flex items-center justify-between"
              >
                {option.label}
                {#if sortBy === option.key}
                  <Check class="h-4 w-4" />
                {/if}
              </DropdownMenu.Item>
            {/each}
            <DropdownMenu.Separator />
            <DropdownMenu.Item onclick={toggleSortOrder}>
              Order: {sortOrder === 'asc' ? 'Ascending ↑' : 'Descending ↓'}
            </DropdownMenu.Item>
          </DropdownMenu.Content>
        </DropdownMenu.Root>

        <!-- More actions dropdown -->
        <DropdownMenu.Root>
          <DropdownMenu.Trigger>
            {#snippet child({ props })}
              <Button
                {...props}
                variant="ghost"
                size="icon"
                class="h-9 w-9 text-muted-foreground"
              >
                <MoreVertical class="h-4 w-4" />
              </Button>
            {/snippet}
          </DropdownMenu.Trigger>
          <DropdownMenu.Content align="end">
            <DropdownMenu.Item>Export active contacts</DropdownMenu.Item>
          </DropdownMenu.Content>
        </DropdownMenu.Root>
      </div>

      <Button
        class="gap-2 ml-2 bg-blue-600 hover:bg-blue-700 text-white"
        onclick={() => (showCreateModal = true)}
      >
        <Plus class="h-4 w-4" />
        New Contact
      </Button>
    </div>
  </div>

  <!-- Bulk Action Bar -->
  {#if hasSelection}
    <div class="flex items-center gap-4 px-6 py-3 bg-muted/50 border-b">
      <Checkbox checked={allSelected} onCheckedChange={toggleSelectAll} />
      <span class="text-sm text-muted-foreground"
        >{selectedIds.size} contacts selected</span
      >
      <div class="flex-1"></div>
      <Button variant="outline" size="sm">Assign Labels</Button>
      <Button variant="destructive" size="sm">Delete</Button>
      <Button
        variant="ghost"
        size="sm"
        onclick={() => {
          selectedIds.clear();
          selectedIds = new Set();
        }}
      >
        Clear
      </Button>
    </div>
  {/if}

  <!-- Contacts List - Row-based layout -->
  <div class="flex-1 overflow-y-auto">
    {#if isLoading && contacts.length === 0}
      <!-- Loading skeleton -->
      <div class="divide-y">
        {#each Array(8) as _}
          <div class="flex items-center gap-4 px-6 py-4">
            <Skeleton.Root class="h-10 w-10 rounded-full" />
            <div class="flex-1 space-y-2">
              <Skeleton.Root class="h-4 w-48" />
              <Skeleton.Root class="h-3 w-32" />
            </div>
            <Skeleton.Root class="h-4 w-24" />
          </div>
        {/each}
      </div>
    {:else if contacts.length === 0}
      <!-- Empty state -->
      <div class="flex flex-col items-center justify-center h-full">
        <div class="text-center">
          <div
            class="mb-4 h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto"
          >
            <Users class="h-8 w-8 text-muted-foreground" />
          </div>
          <h2 class="text-xl font-medium mb-2">No active contacts</h2>
          <p class="text-muted-foreground max-w-md">
            {#if searchQuery}
              No active contacts match "{searchQuery}".
            {:else}
              There are currently no contacts online or recently active.
            {/if}
          </p>
        </div>
      </div>
    {:else}
      <!-- Contacts List - Row Layout -->
      <div class="divide-y">
        {#each contacts as contact (contact.id)}
          <div
            class="group flex items-center gap-4 px-6 py-4 hover:bg-muted/50 transition-colors cursor-pointer"
            onclick={() => viewContactDetails(contact)}
            onkeydown={e => e.key === 'Enter' && viewContactDetails(contact)}
            tabindex="0"
            role="button"
          >
            <!-- Selection checkbox (visible on hover) -->
            <div
              class="opacity-0 group-hover:opacity-100 transition-opacity"
              onclick={e => {
                e.stopPropagation();
                toggleSelection(contact.id);
              }}
              role="button"
              tabindex="-1"
              onkeydown={e => {
                if (e.key === 'Enter') {
                  e.stopPropagation();
                  toggleSelection(contact.id);
                }
              }}
            >
              <Checkbox
                checked={selectedIds.has(contact.id)}
                class={selectedIds.has(contact.id) ? 'opacity-100' : ''}
              />
            </div>

            <!-- Avatar with online indicator -->
            <div class="relative">
              <Avatar.Root class="h-10 w-10">
                <Avatar.Image
                  src={contact.thumbnail || contact.avatarUrl || ''}
                  alt={contact.name}
                />
                <Avatar.Fallback>
                  {contact.name?.charAt(0).toUpperCase() || '?'}
                </Avatar.Fallback>
              </Avatar.Root>
              <!-- Online indicator -->
              <span
                class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-green-500 border-2 border-background"
              ></span>
            </div>

            <!-- Name and Email -->
            <div class="flex-1 min-w-0">
              <h3 class="font-medium truncate">
                {contact.name || 'Unknown Contact'}
              </h3>
              {#if contact.email}
                <p class="text-sm text-muted-foreground truncate">
                  {contact.email}
                </p>
              {/if}
            </div>

            <!-- Phone -->
            <div
              class="hidden md:flex items-center gap-2 text-sm text-muted-foreground w-36"
            >
              {#if contact.phoneNumber}
                <Phone class="h-3 w-3 shrink-0" />
                <span class="truncate">{contact.phoneNumber}</span>
              {/if}
            </div>

            <!-- Company -->
            <div
              class="hidden lg:flex items-center gap-2 text-sm text-muted-foreground w-40"
            >
              {#if contact.company}
                <Building class="h-3 w-3 shrink-0" />
                <span class="truncate">{contact.company}</span>
              {/if}
            </div>

            <!-- Status badge -->
            <Badge variant="default" class="bg-green-500 text-white"
              >Online</Badge
            >

            <!-- Chevron -->
            <ChevronRight
              class="h-4 w-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity"
            />
          </div>
        {/each}
      </div>
    {/if}
  </div>

  <!-- Pagination Footer -->
  {#if contacts.length > 0}
    <PaginationFooter
      {currentPage}
      {totalItems}
      itemsPerPage={15}
      onPageChange={handlePageChange}
    />
  {/if}

  <!-- Create Contact Dialog -->
  <Dialog.Root bind:open={showCreateModal}>
    <Dialog.Content class="sm:max-w-[600px]">
      <Dialog.Header>
        <Dialog.Title>Create New Contact</Dialog.Title>
      </Dialog.Header>
      <ContactForm
        on:save={handleCreateContact}
        on:cancel={() => (showCreateModal = false)}
        serverErrors={contactsStore.validationErrors}
      />
    </Dialog.Content>
  </Dialog.Root>
</div>
