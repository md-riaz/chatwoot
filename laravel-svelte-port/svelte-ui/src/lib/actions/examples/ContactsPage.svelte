<!--
  Example: Contact Management Page using Svelte 5 Action Pattern
  
  This demonstrates Vue-like composable functionality with:
  - Reactive state management
  - Optimistic updates
  - Error handling
  - Loading states
  - Debounced search
  - Bulk operations
-->
<script lang="ts">
  import { page } from '$app/stores';
  import { useContactActions } from '../contacts.svelte.ts';
  import type { Contact, CreateContactParams } from '$lib/api/contacts';
  
  // Get account ID from route params
  const accountId = parseInt($page.params.accountId || '0', 10);
  
  // Initialize contact actions (Vue-like composable)
  const contacts = useContactActions(accountId);
  
  // Local component state
  let searchQuery = $state('');
  let selectedContacts = $state<number[]>([]);
  let showCreateModal = $state(false);
  let showBulkActions = $state(false);
  
  // Form state for creating contacts
  let createForm = $state<CreateContactParams>({
    name: '',
    email: '',
    phoneNumber: '',
    company: ''
  });
  
  // Derived state from actions
  const contactList = $derived(contacts.list.data?.data || []);
  const isLoading = $derived(contacts.isAnyLoading);
  const hasError = $derived(contacts.hasAnyError);
  const totalContacts = $derived(contacts.list.data?.total || 0);
  const currentPage = $derived(contacts.list.data?.currentPage || 1);
  const totalPages = $derived(contacts.list.data?.lastPage || 1);
  
  // Search results
  const searchResults = $derived(contacts.search.data?.data || []);
  const isSearching = $derived(contacts.search.loading);
  
  // Display contacts (search results or regular list)
  const displayContacts = $derived(
    searchQuery.trim() ? searchResults : contactList
  );
  
  // Bulk actions state
  const canBulkDelete = $derived(selectedContacts.length > 0);
  const bulkLoading = $derived(contacts.bulk.loading);
  
  // Load initial contacts
  $effect(() => {
    contacts.fetchContacts({ page: 1, perPage: 20 });
  });
  
  // Debounced search effect
  $effect(() => {
    if (searchQuery.trim()) {
      contacts.searchContacts(searchQuery);
    }
  });
  
  /**
   * Handle contact creation
   */
  async function handleCreateContact() {
    const newContact = await contacts.createContact(createForm);
    
    if (newContact) {
      // Success - close modal and reset form
      showCreateModal = false;
      createForm = { name: '', email: '', phoneNumber: '', company: '' };
      
      // Refresh contact list
      await contacts.fetchContacts({ page: currentPage });
    }
    // Errors are automatically handled by the action
  }
  
  /**
   * Handle contact update with optimistic update
   */
  async function handleUpdateContact(contact: Contact, updates: Partial<Contact>) {
    await contacts.updateContact(contact.id, updates, contact);
    
    // Refresh list to ensure consistency
    if (contacts.update.success) {
      await contacts.fetchContacts({ page: currentPage });
    }
  }
  
  /**
   * Handle contact deletion
   */
  async function handleDeleteContact(contactId: number) {
    if (confirm('Are you sure you want to delete this contact?')) {
      await contacts.deleteContact(contactId);
      
      if (contacts.delete.success) {
        // Remove from selection if selected
        selectedContacts = selectedContacts.filter(id => id !== contactId);
        
        // Refresh list
        await contacts.fetchContacts({ page: currentPage });
      }
    }
  }
  
  /**
   * Handle bulk delete
   */
  async function handleBulkDelete() {
    if (confirm(`Delete ${selectedContacts.length} contacts?`)) {
      await contacts.bulkDeleteContacts(selectedContacts);
      
      if (contacts.bulk.success) {
        selectedContacts = [];
        showBulkActions = false;
        
        // Refresh list
        await contacts.fetchContacts({ page: currentPage });
      }
    }
  }
  
  /**
   * Handle pagination
   */
  async function handlePageChange(page: number) {
    await contacts.fetchContacts({ page, perPage: 20 });
  }
  
  /**
   * Toggle contact selection
   */
  function toggleContactSelection(contactId: number) {
    if (selectedContacts.includes(contactId)) {
      selectedContacts = selectedContacts.filter(id => id !== contactId);
    } else {
      selectedContacts = [...selectedContacts, contactId];
    }
    
    showBulkActions = selectedContacts.length > 0;
  }
  
  /**
   * Select all contacts
   */
  function selectAllContacts() {
    selectedContacts = displayContacts.map(c => c.id);
    showBulkActions = true;
  }
  
  /**
   * Clear selection
   */
  function clearSelection() {
    selectedContacts = [];
    showBulkActions = false;
  }
</script>

<!-- Page Header -->
<div class="flex justify-between items-center mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900">Contacts</h1>
    <p class="text-gray-600">{totalContacts} total contacts</p>
  </div>
  
  <button 
    onclick={() => showCreateModal = true}
    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
  >
    Add Contact
  </button>
</div>

<!-- Search Bar -->
<div class="mb-6">
  <div class="relative">
    <input
      bind:value={searchQuery}
      type="text"
      placeholder="Search contacts..."
      class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    />
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
    </div>
    
    {#if isSearching}
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
      </div>
    {/if}
  </div>
</div>

<!-- Bulk Actions Bar -->
{#if showBulkActions}
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex items-center justify-between">
      <span class="text-blue-800">
        {selectedContacts.length} contact{selectedContacts.length !== 1 ? 's' : ''} selected
      </span>
      
      <div class="flex gap-2">
        <button
          onclick={handleBulkDelete}
          disabled={bulkLoading}
          class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 disabled:opacity-50"
        >
          {bulkLoading ? 'Deleting...' : 'Delete Selected'}
        </button>
        
        <button
          onclick={clearSelection}
          class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700"
        >
          Clear Selection
        </button>
      </div>
    </div>
  </div>
{/if}

<!-- Loading State -->
{#if isLoading && !displayContacts.length}
  <div class="flex justify-center items-center py-12">
    <div class="animate-spin h-8 w-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
    <span class="ml-3 text-gray-600">Loading contacts...</span>
  </div>
{/if}

<!-- Error State -->
{#if hasError}
  <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <div class="flex items-center">
      <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="text-red-800">
        {contacts.list.error || contacts.search.error || 'An error occurred'}
      </span>
    </div>
  </div>
{/if}

<!-- Contacts Table -->
{#if displayContacts.length > 0}
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left">
            <input
              type="checkbox"
              onchange={selectAllContacts}
              class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            />
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Contact
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Email
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Phone
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Company
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        {#each displayContacts as contact (contact.id)}
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <input
                type="checkbox"
                checked={selectedContacts.includes(contact.id)}
                onchange={() => toggleContactSelection(contact.id)}
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                {#if contact.avatarUrl}
                  <img class="h-10 w-10 rounded-full" src={contact.avatarUrl} alt={contact.name} />
                {:else}
                  <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-700">
                      {contact.name?.charAt(0)?.toUpperCase() || '?'}
                    </span>
                  </div>
                {/if}
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{contact.name || 'Unknown'}</div>
                  <div class="text-sm text-gray-500">ID: {contact.id}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {contact.email || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {contact.phoneNumber || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {contact.company || '-'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                onclick={() => handleUpdateContact(contact, { name: contact.name + ' (Updated)' })}
                class="text-blue-600 hover:text-blue-900 mr-3"
              >
                Edit
              </button>
              <button
                onclick={() => handleDeleteContact(contact.id)}
                class="text-red-600 hover:text-red-900"
              >
                Delete
              </button>
            </td>
          </tr>
        {/each}
      </tbody>
    </table>
  </div>
  
  <!-- Pagination -->
  {#if totalPages > 1}
    <div class="flex justify-between items-center mt-6">
      <div class="text-sm text-gray-700">
        Showing page {currentPage} of {totalPages}
      </div>
      
      <div class="flex gap-2">
        <button
          onclick={() => handlePageChange(currentPage - 1)}
          disabled={currentPage <= 1 || isLoading}
          class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50"
        >
          Previous
        </button>
        
        <button
          onclick={() => handlePageChange(currentPage + 1)}
          disabled={currentPage >= totalPages || isLoading}
          class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>
  {/if}
{:else if !isLoading}
  <!-- Empty State -->
  <div class="text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">No contacts found</h3>
    <p class="mt-1 text-sm text-gray-500">
      {searchQuery.trim() ? 'Try adjusting your search terms.' : 'Get started by creating a new contact.'}
    </p>
  </div>
{/if}

<!-- Create Contact Modal -->
{#if showCreateModal}
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Contact</h3>
        
        <form onsubmit|preventDefault={handleCreateContact}>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input
              bind:value={createForm.name}
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            {#if contacts.create.validationErrors.name}
              <p class="text-red-600 text-sm mt-1">{contacts.create.validationErrors.name}</p>
            {/if}
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input
              bind:value={createForm.email}
              type="email"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            {#if contacts.create.validationErrors.email}
              <p class="text-red-600 text-sm mt-1">{contacts.create.validationErrors.email}</p>
            {/if}
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
            <input
              bind:value={createForm.phoneNumber}
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
            <input
              bind:value={createForm.company}
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          
          <div class="flex justify-end gap-3">
            <button
              type="button"
              onclick={() => showCreateModal = false}
              class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={contacts.create.loading}
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {contacts.create.loading ? 'Creating...' : 'Create Contact'}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
{/if}