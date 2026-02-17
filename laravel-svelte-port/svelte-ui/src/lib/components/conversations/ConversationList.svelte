<script lang="ts">
  /**
   * ConversationList - Main conversation list component
   * Features: Filtering, sorting, infinite scroll, real-time updates
   */

  import { onMount } from 'svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import ConversationItem from './ConversationItem.svelte';
  import ConversationFilters from './ConversationFilters.svelte';
  import ConversationEmpty from './ConversationEmpty.svelte';
  import ConversationSkeleton from './ConversationSkeleton.svelte';
  import type {
    ConversationFilterOptions,
    ConversationSortOptions,
  } from './types';

  interface Props {
    onConversationSelect?: (conversationId: number) => void;
    activeId?: number | null;
  }

  let { onConversationSelect, activeId = null }: Props = $props();

  // Reactive store access
  const conversations = $derived(conversationsStore.sortedConversations);
  const selectedId = $derived(conversationsStore.selectedConversationId);
  const isLoading = $derived(conversationsStore.isLoading);

  // Local state
  let scrollContainer: HTMLElement | undefined;
  let currentFilters = $state<ConversationFilterOptions>({});
  let statusCounts = $state({
    all: 0,
    mine: 0,
    unassigned: 0,
    open: 0,
    resolved: 0,
  });

  // Handle filter changes
  function handleFiltersChange(newFilters: ConversationFilterOptions) {
    currentFilters = newFilters;

    // Update store filters
    if (newFilters.status) {
      conversationsStore.statusFilter = newFilters.status;
    }
    if (newFilters.inboxId) {
      conversationsStore.currentInboxId = newFilters.inboxId;
    }

    conversationsStore.fetchConversations();
  }

  // Handle sort changes
  function handleSortChange(sort: ConversationSortOptions) {
    conversationsStore.sortFilter = sort.sortBy;
  }

  // Handle conversation selection
  function handleConversationClick(conversationId: number) {
    conversationsStore.setSelectedConversation(conversationId);
    onConversationSelect?.(conversationId);
  }

  // Handle clear filters
  function handleClearFilters() {
    currentFilters = {};
    conversationsStore.statusFilter = 'open';
    conversationsStore.currentInboxId = null;
    conversationsStore.fetchConversations();
  }

  // Infinite scroll handler
  function handleScroll(e: Event) {
    const target = e.target as HTMLElement;
    const scrollPercentage =
      (target.scrollTop + target.clientHeight) / target.scrollHeight;

    // Load more when scrolled 80%
    if (scrollPercentage > 0.8 && !isLoading && conversations.length > 0) {
      // TODO: Implement pagination
      console.log('Load more conversations');
    }
  }

  // Get contact from meta.sender or contacts store
  function getContact(conversation: any): any {
    // Prefer meta.sender (comes from API) over separate contacts store
    if (conversation.meta?.sender) {
      return {
        id: conversation.meta.sender.id,
        name: conversation.meta.sender.name,
        email: conversation.meta.sender.email,
        thumbnail:
          conversation.meta.sender.avatarUrl ||
          conversation.meta.sender.thumbnail ||
          '',
        availabilityStatus: conversation.meta.sender.availabilityStatus,
      };
    }
    if (!conversation.contactId) return undefined;
    return contactsStore.allContacts.find(c => c.id === conversation.contactId);
  }

  function getInbox(inboxId?: number) {
    if (!inboxId) return undefined;
    return inboxesStore.allInboxes.find(i => i.id === inboxId);
  }

  // Update status counts (mock data for now)
  function updateStatusCounts() {
    statusCounts = {
      all: conversations.length,
      mine: conversations.filter(c => c.meta?.assignee?.id === 1).length,
      unassigned: conversations.filter(c => !c.meta?.assignee).length,
      open: conversations.filter(c => c.status === 'open').length,
      resolved: conversations.filter(c => c.status === 'resolved').length,
    };
  }

  // Initialize
  onMount(async () => {
    // Fetch initial data
    await Promise.all([
      conversationsStore.fetchConversations(),
      contactsStore.fetchContacts(),
      inboxesStore.fetchInboxes(),
    ]);

    updateStatusCounts();
  });

  // Update counts when conversations change
  $effect(() => {
    if (conversations) {
      updateStatusCounts();
    }
  });
</script>

<div class="flex flex-col h-full">
  <!-- Filters -->
  <div class="p-4">
    <ConversationFilters
      filters={currentFilters}
      {statusCounts}
      onFiltersChange={handleFiltersChange}
      onSortChange={handleSortChange}
    />
  </div>

  <!-- Conversation List -->
  <div
    class="flex-1 overflow-y-auto"
    bind:this={scrollContainer}
    onscroll={handleScroll}
  >
    {#if isLoading && conversations.length === 0}
      <!-- Loading skeleton -->
      <ConversationSkeleton count={5} />
    {:else if conversations.length === 0}
      <!-- Empty state -->
      <ConversationEmpty onAction={handleClearFilters} />
    {:else}
      <!-- Conversation items -->
      <div class="flex flex-col">
        {#each conversations as conv (conv.id)}
          <ConversationItem
            conversation={conv}
            contact={getContact(conv)}
            inbox={getInbox(conv.inboxId)}
            selected={activeId != null
              ? conv.id == activeId
              : conv.id === selectedId}
            onclick={() => handleConversationClick(conv.id)}
          />
        {/each}
      </div>

      <!-- Loading more indicator -->
      {#if isLoading}
        <div class="p-4">
          <ConversationSkeleton count={2} />
        </div>
      {/if}
    {/if}
  </div>
</div>
