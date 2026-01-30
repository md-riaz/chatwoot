<script lang="ts">
  /**
   * Contact List Component
   * Reusable component for displaying contact lists (all, label, segment)
   */
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
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
    X,
    Save,
  } from 'lucide-svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import { debounce } from '$lib/utils';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as Card from '$lib/components/ui/card';
  import * as Dialog from '$lib/components/ui/dialog';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Badge } from '$lib/components/ui/badge';
  import { Button, buttonVariants } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { Input } from '$lib/components/ui/input';
  import * as Skeleton from '$lib/components/ui/skeleton';
  import { PaginationFooter } from '$lib/components/ui/pagination';
  import ContactForm from '$lib/components/ui/contact-management/contact-form/contact-form.svelte';
  import AdvancedFilter from '$lib/components/ui/contact-management/advanced-filter.svelte';
  import ImportDialog from '$lib/components/ui/contact-management/import-dialog.svelte';
  import ExportDialog from '$lib/components/ui/contact-management/export-dialog.svelte';
  import BulkActionBar from '$lib/components/ui/contact-management/bulk-action-bar.svelte';
  import type { Contact } from '$lib/api/contacts';
  import CreateSegmentDialog from '$lib/components/ui/contact-management/create-segment-dialog.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';

  // Props
  interface Props {
    title?: string;
    accountId: number;
    initialFetchParams?: Record<string, any>;
    segmentId?: number | null;
  }

  let {
    title = 'Contacts',
    accountId,
    initialFetchParams = {},
    segmentId = null,
  }: Props = $props();

  // Reactive store access
  const contacts = $derived(contactsStore.allContacts);
  const isLoading = $derived(contactsStore.isLoading);
  const currentPage = $derived(contactsStore.currentPage);
  const hasMorePages = $derived(contactsStore.hasMorePages);

  // Local state
  let searchQuery = $state('');
  let showCreateModal = $state(false);
  let contactFormInstance = $state<any>(null); // Type 'any' for now or import the type if exported
  let showDeleteDialog = $state(false);
  let showFilterDialog = $state(false);
  let showImportDialog = $state(false);
  let showExportDialog = $state(false);
  let showCreateSegmentDialog = $state(false);
  let isCreating = $state(false);
  let isBulkProcessing = $state(false);
  let sortBy = $state('last_activity_at');
  let sortOrder = $state<'asc' | 'desc'>('desc');
  let selectedIds = $state<number[]>([]);

  let activeFiltersArray = $state<
    Array<{
      attributeKey: string;
      filterOperator: string;
      values: string[]; // Array for Vue/API parity
      queryOperator: 'and' | 'or';
      attributeModel: string;
    }>
  >([]);

  // Derive hasActiveFilters from the filters array
  const hasActiveFilters = $derived(
    activeFiltersArray.length > 0 &&
      activeFiltersArray.some(
        f =>
          f.values.length > 0 ||
          ['is_present', 'is_not_present'].includes(f.filterOperator)
      )
  );

  // Merge params
  const currentParams = $derived({
    ...initialFetchParams,
    sort: sortBy,
    sort_direction: sortOrder,
  });

  // Helper to fetch contacts based on mode
  async function fetchContactsData(params: any = {}) {
    if (segmentId) {
      await contactsStore.fetchSegmentContacts(segmentId, {
        ...currentParams,
        ...params,
      });
    } else {
      await contactsStore.fetchContacts({ ...currentParams, ...params });
    }
  }

  // Sort options
  const sortOptions = [
    { key: 'name', label: 'Name' },
    { key: 'last_activity_at', label: 'Last Activity' },
    { key: 'created_at', label: 'Created Date' },
    { key: 'email', label: 'Email' },
  ];

  // Derive current sort label
  const currentSortLabel = $derived(
    sortOptions.find(opt => opt.key === sortBy)?.label || 'Sort'
  );

  // Selection state
  const hasSelection = $derived(selectedIds.length > 0);
  const allSelected = $derived(
    contacts.length > 0 && contacts.every(c => selectedIds.includes(c.id))
  );

  // Total items for pagination (approximate from store state)
  const totalItems = $derived(
    hasMorePages ? currentPage * 15 + 1 : contacts.length
  );

  // Debounced search function
  const debouncedSearch = debounce(async (query: string) => {
    if (query.trim()) {
      // Search ignores segment context for now as per typical implementation, or should it?
      // Typically search is global or within context. The store.searchContacts is global.
      // If we want search within segment, we might need API support or just filter locally if possible (but pagination makes it hard).
      // For now, let's use global search which is what searchContacts does.
      await contactsStore.searchContacts(query);
    } else {
      await fetchContactsData();
    }
  }, 300);

  // Effect to trigger search when query changes
  $effect(() => {
    debouncedSearch(searchQuery);
  });

  // Watch for initialFetchParams or segmentId changes and re-fetch
  $effect(() => {
    // This effect runs when initialFetchParams or segmentId changes
    // We trigger a fresh fetch
    if (!searchQuery) {
      fetchContactsData({ page: 1 });
    }
  });

  // Handle sort change
  async function handleSortChange(sort: string) {
    sortBy = sort;
    await fetchContactsData({
      sort: sortBy,
      page: 1,
    });
  }

  // Handle filter apply - call the filter API with payload
  async function handleFilterApply(
    event: CustomEvent<typeof activeFiltersArray>
  ) {
    const filters = event.detail;
    activeFiltersArray = filters;

    // Call the filter API with the payload
    if (
      filters.length > 0 &&
      filters.some(
        f =>
          f.values.length > 0 ||
          ['is_present', 'is_not_present'].includes(f.filterOperator)
      )
    ) {
      await contactsStore.filterContacts(filters, 1, sortBy);
    } else {
      // No active filters, fetch all contacts
      await fetchContactsData({ page: 1 });
    }
  }

  // Handle filter clear
  function handleFilterClear() {
    activeFiltersArray = [];
    fetchContactsData({ page: 1 });
  }

  // Toggle sort order
  async function toggleSortOrder() {
    sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
    await fetchContactsData({
      sort_direction: sortOrder,
      page: 1,
    });
  }

  // Handle page change
  async function handlePageChange(page: number) {
    await fetchContactsData({
      page,
    });
  }

  // Toggle contact selection
  function toggleSelection(contactId: number) {
    if (selectedIds.includes(contactId)) {
      selectedIds = selectedIds.filter(id => id !== contactId);
    } else {
      selectedIds = [...selectedIds, contactId];
    }
  }

  // Toggle select all
  function toggleSelectAll(selectAll: boolean) {
    if (!selectAll) {
      selectedIds = [];
    } else {
      selectedIds = contacts.map(c => c.id);
    }
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

  // Handle bulk assign labels
  async function handleBulkAssignLabels(labels: string[]) {
    if (labels.length === 0) return;

    try {
      isBulkProcessing = true;
      const contactIds = selectedIds;

      const success = await contactsStore.bulkAssignLabels(contactIds, labels);
      if (success) {
        selectedIds = []; // Clear selection
      }
    } catch (error) {
      console.error('Failed to assign labels', error);
    } finally {
      isBulkProcessing = false;
    }
  }

  // Handle bulk delete
  async function handleBulkDelete() {
    try {
      isBulkProcessing = true;
      const success = await contactsStore.bulkDelete(selectedIds);
      if (success) {
        showDeleteDialog = false;
        selectedIds = []; // Clear selection
      }
    } catch (error) {
      console.error('Failed to delete contacts', error);
    } finally {
      isBulkProcessing = false;
    }
  }

  // Load labels on mount (contacts loaded by effect)
  // Load labels on mount (contacts loaded by effect)
  onMount(async () => {
    try {
      // Fetch labels for the bulk action bar
      await labelsStore.fetchLabels();
    } catch (e) {
      console.error('Failed to fetch labels', e);
    }
  });
</script>

<div class="h-full flex flex-col bg-background">
  <!-- Sticky Header -->
  <header class="sticky top-0 z-10 bg-background">
    <div
      class="flex items-start sm:items-center justify-between w-full py-6 px-6 gap-2 mx-auto max-w-[60rem]"
    >
      <span class="text-xl font-medium truncate text-foreground">
        {title}
        {#if hasSelection}
          <Badge variant="secondary" class="ml-2"
            >{selectedIds.length} selected</Badge
          >
        {/if}
      </span>

      <div class="flex items-center flex-col sm:flex-row flex-shrink-0 gap-4">
        <!-- Search Input -->
        <div class="flex items-center gap-2 w-full">
          <div class="relative w-48">
            <Search
              class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"
            />
            <Input
              bind:value={searchQuery}
              type="search"
              placeholder="Search..."
              class="h-8 pl-8 pr-2 py-1 text-sm !border-0 bg-slate-100 dark:bg-slate-800/50 rounded-lg outline outline-1 outline-slate-200 dark:outline-slate-700 focus:outline-blue-600 focus:outline-1 transition-all duration-200"
            />
          </div>
        </div>

        <div class="flex items-center flex-shrink-0 gap-4">
          <div class="flex items-center gap-2">
            <!-- Filter button -->
            <div class="relative">
              <Button
                id="toggleContactsFilterButton"
                variant="ghost"
                size="sm"
                class="h-8 w-8 text-muted-foreground"
                onclick={() => (showFilterDialog = !showFilterDialog)}
              >
                <Filter class="h-4 w-4" />
              </Button>
              {#if hasActiveFilters}
                <div
                  class="absolute top-0 right-0 w-2 h-2 rounded-full bg-blue-600"
                ></div>
              {/if}

              <!-- Advanced Filter Panel -->
              <AdvancedFilter
                bind:open={showFilterDialog}
                bind:filters={activeFiltersArray}
                on:apply={handleFilterApply}
                on:clear={handleFilterClear}
              />
            </div>

            <!-- Save segment button (shown when filters are active) -->
            {#if hasActiveFilters}
              <Button
                variant="ghost"
                size="sm"
                class="h-8 w-8 text-muted-foreground"
                onclick={() => (showCreateSegmentDialog = true)}
              >
                <Save class="h-4 w-4" />
              </Button>
            {/if}

            <!-- Sort dropdown -->
            <DropdownMenu.Root>
              <DropdownMenu.Trigger
                class={buttonVariants({ variant: 'ghost', size: 'sm' }) +
                  ' h-8 w-8 text-muted-foreground'}
              >
                <ArrowUpDown class="h-4 w-4" />
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
              <DropdownMenu.Trigger
                class={buttonVariants({ variant: 'ghost', size: 'sm' }) +
                  ' h-8 w-8 text-muted-foreground'}
              >
                <MoreVertical class="h-4 w-4" />
              </DropdownMenu.Trigger>
              <DropdownMenu.Content align="end">
                <DropdownMenu.Item onclick={() => (showCreateModal = true)}>
                  <Plus class="h-4 w-4 mr-2" />
                  Add contact
                </DropdownMenu.Item>
                <DropdownMenu.Item onclick={() => (showImportDialog = true)}
                  >Import contacts</DropdownMenu.Item
                >
                <DropdownMenu.Item onclick={() => (showExportDialog = true)}
                  >Export contacts</DropdownMenu.Item
                >
              </DropdownMenu.Content>
            </DropdownMenu.Root>
          </div>

          <!-- Divider -->
          <div class="w-px h-4 bg-border"></div>

          <!-- Message button (primary action) -->
          <Button class="bg-blue-600 hover:bg-blue-700 text-white" size="sm">
            Message
          </Button>
        </div>
      </div>
    </div>
  </header>

  <!-- Bulk Action Bar -->
  <BulkActionBar
    visibleContactIds={contacts.map(c => c.id)}
    bind:selectedContactIds={selectedIds}
    isLoading={isBulkProcessing}
    onClearSelection={() => (selectedIds = [])}
    onToggleAll={toggleSelectAll}
    onAssignLabels={handleBulkAssignLabels}
    onDeleteSelected={() => (showDeleteDialog = true)}
  />

  <!-- Contacts List - Row-based layout -->
  <main class="flex-1 overflow-y-auto">
    <div class="w-full mx-auto max-w-[60rem]">
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
              <Skeleton.Root class="h-4 w-20" />
            </div>
          {/each}
        </div>
      {:else if contacts.length === 0}
        <!-- Empty state -->
        <div
          class="relative w-full max-w-[60rem] mx-auto overflow-hidden h-full min-h-[28rem] flex flex-col items-center justify-center"
        >
          {#if !searchQuery}
            <!-- Ghost Background for Empty State -->
            <div
              class="w-full h-full space-y-0 overflow-y-hidden opacity-50 pointer-events-none"
            >
              <!-- Mock Contact 1: Candice Matherson -->
              <div class="flex items-center gap-4 px-6 py-4 border-b">
                <div
                  class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-sm"
                >
                  CM
                </div>
                <div class="flex-1 min-w-0 flex items-center gap-4">
                  <div class="flex flex-col min-w-0">
                    <span class="font-medium text-sm">Candice Matherson</span>
                    <span class="text-xs text-muted-foreground truncate"
                      >candice.matherson@lumora.com</span
                    >
                  </div>
                  <span class="text-xs text-muted-foreground">Lumora</span>
                  <span class="text-xs text-muted-foreground">+14155552671</span
                  >
                  <span class="text-xs text-muted-foreground"
                    >🇺🇸 Los Angeles, United States</span
                  >
                </div>
                <span class="text-xs text-blue-600">View details</span>
              </div>
              <!-- Mock Contact 2: Ophelia Folkard -->
              <div class="flex items-center gap-4 px-6 py-4 border-b">
                <div
                  class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-medium text-sm"
                >
                  OF
                </div>
                <div class="flex-1 min-w-0 flex items-center gap-4">
                  <div class="flex flex-col min-w-0">
                    <span class="font-medium text-sm">Ophelia Folkard</span>
                    <span class="text-xs text-muted-foreground truncate"
                      >ophelia.folkard@designify.com</span
                    >
                  </div>
                  <span class="text-xs text-muted-foreground">Designify</span>
                  <span class="text-xs text-muted-foreground">+14155552672</span
                  >
                  <span class="text-xs text-muted-foreground"
                    >🇺🇸 San Francisco, United States</span
                  >
                </div>
                <span class="text-xs text-blue-600">View details</span>
              </div>
              <!-- Mock Contact 3: Willy Castelot -->
              <div class="flex items-center gap-4 px-6 py-4 border-b">
                <div
                  class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-medium text-sm"
                >
                  WC
                </div>
                <div class="flex-1 min-w-0 flex items-center gap-4">
                  <div class="flex flex-col min-w-0">
                    <span class="font-medium text-sm">Willy Castelot</span>
                    <span class="text-xs text-muted-foreground truncate"
                      >willy.castelot@codehub.io</span
                    >
                  </div>
                  <span class="text-xs text-muted-foreground">CodeHub</span>
                  <span class="text-xs text-muted-foreground">+14155552673</span
                  >
                  <span class="text-xs text-muted-foreground"
                    >🇺🇸 Austin, United States</span
                  >
                </div>
                <span class="text-xs text-blue-600">View details</span>
              </div>
            </div>
          {/if}

          <div
            class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-end w-full h-full pb-20 bg-gradient-to-t from-background from-25% to-transparent"
          >
            <div class="flex flex-col items-center justify-center gap-6">
              {#if searchQuery}
                <div class="flex flex-col items-center text-center">
                  <div
                    class="mb-4 h-12 w-12 rounded-full bg-muted flex items-center justify-center"
                  >
                    <Search class="h-6 w-6 text-muted-foreground" />
                  </div>
                  <h3 class="text-lg font-semibold mb-2">No contacts found</h3>
                  <p class="text-sm text-muted-foreground max-w-md">
                    We couldn't find any contacts matching "{searchQuery}". Try
                    adjusting your search query.
                  </p>
                </div>
              {:else}
                <div class="flex flex-col items-center justify-center gap-3">
                  <h2 class="text-3xl font-medium text-center text-foreground">
                    No contacts found in this account
                  </h2>
                  <p
                    class="max-w-xl text-base text-center text-muted-foreground"
                  >
                    Start adding new contacts by clicking on the button below
                  </p>
                </div>
                <Button
                  class="gap-2 bg-blue-600 hover:bg-blue-700 text-white min-w-[140px]"
                  onclick={() => (showCreateModal = true)}
                >
                  <Plus class="h-4 w-4" />
                  Add Contact
                </Button>
              {/if}
            </div>
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
                  checked={selectedIds.includes(contact.id)}
                  class={selectedIds.includes(contact.id) ? 'opacity-100' : ''}
                />
              </div>

              <!-- Avatar -->
              <Avatar.Root class="h-10 w-10">
                <Avatar.Image
                  src={contact.thumbnail || contact.avatarUrl || ''}
                  alt={contact.name}
                />
                <Avatar.Fallback>
                  {contact.name?.charAt(0).toUpperCase() || '?'}
                </Avatar.Fallback>
              </Avatar.Root>

              <!-- Name and Email -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h3 class="font-medium truncate">
                    {contact.name || 'Unknown Contact'}
                  </h3>
                  {#if contact.availabilityStatus === 'online'}
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                  {/if}
                </div>
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

              <!-- Location -->
              <div
                class="hidden xl:flex items-center gap-2 text-sm text-muted-foreground w-32"
              >
                {#if contact.city || contact.country}
                  <MapPin class="h-3 w-3 shrink-0" />
                  <span class="truncate"
                    >{[contact.city, contact.country]
                      .filter(Boolean)
                      .join(', ')}</span
                  >
                {/if}
              </div>

              <!-- Chevron -->
              <ChevronRight
                class="h-4 w-4 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity"
              />
            </div>
          {/each}
        </div>
      {/if}
    </div>
  </main>

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
    <Dialog.Content class="sm:max-w-2xl">
      <Dialog.Header>
        <Dialog.Title>Create New Contact</Dialog.Title>
      </Dialog.Header>
      <ContactForm
        bind:this={contactFormInstance}
        on:save={handleCreateContact}
        on:cancel={() => (showCreateModal = false)}
        serverErrors={contactsStore.validationErrors}
      />
      <Dialog.Footer>
        <Button variant="ghost" onclick={() => (showCreateModal = false)}
          >Cancel</Button
        >
        <Button
          onclick={() => contactFormInstance?.submit()}
          disabled={isCreating}
        >
          {isCreating ? 'Saving...' : 'Save Contact'}
        </Button>
      </Dialog.Footer>
    </Dialog.Content>
  </Dialog.Root>

  <!-- Assign Labels Dialog -->
  <!-- Bulk Delete Confirmation Dialog -->
  <Dialog.Root bind:open={showDeleteDialog}>
    <Dialog.Content class="sm:max-w-[400px]">
      <Dialog.Header>
        <Dialog.Title class="text-destructive">Delete Contacts</Dialog.Title>
        <Dialog.Description>
          Are you sure you want to delete {selectedIds.length} contact{selectedIds.length >
          1
            ? 's'
            : ''}? This action cannot be undone.
        </Dialog.Description>
      </Dialog.Header>
      <Dialog.Footer>
        <Button variant="outline" onclick={() => (showDeleteDialog = false)}>
          Cancel
        </Button>
        <Button
          variant="destructive"
          onclick={handleBulkDelete}
          disabled={isBulkProcessing}
        >
          {isBulkProcessing
            ? 'Deleting...'
            : `Delete ${selectedIds.length} Contact${selectedIds.length !== 1 ? 's' : ''}`}
        </Button>
      </Dialog.Footer>
    </Dialog.Content>
  </Dialog.Root>

  <!-- Old Filter Dialog removed - now using inline AdvancedFilter panel in header -->

  <!-- Import Dialog -->
  <ImportDialog
    bind:open={showImportDialog}
    on:imported={() => {
      contactsStore.fetchContacts(currentParams);
    }}
  />

  <!-- Export Dialog -->
  <ExportDialog bind:open={showExportDialog} contactCount={contacts.length} />

  <!-- Create Segment Dialog -->
  {#if segmentId === undefined || segmentId === null}
    <CreateSegmentDialog
      bind:open={showCreateSegmentDialog}
      query={currentParams}
    />
  {/if}
</div>
