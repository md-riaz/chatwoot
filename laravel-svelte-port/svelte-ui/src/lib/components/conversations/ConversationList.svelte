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
  const statusCounts = $derived(conversationsStore.statusCounts);

  // Local state
  let scrollContainer: HTMLElement | undefined;
  let currentFilters = $state<ConversationFilterOptions>({});

  $effect(() => {
    currentFilters = {
      ...(conversationsStore.statusFilter
        ? { status: conversationsStore.statusFilter }
        : {}),
      ...(conversationsStore.currentInboxId
        ? { inboxId: conversationsStore.currentInboxId }
        : {}),
      ...(conversationsStore.assigneeTypeFilter
        ? { assigneeType: conversationsStore.assigneeTypeFilter }
        : {}),
      ...(conversationsStore.currentTeamId
        ? { teamId: conversationsStore.currentTeamId }
        : {}),
    };
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
    // Prefer contact (Laravel API) or meta.sender (Rails API)
    const contactInfo =
      conversation.contact || conversation.meta?.sender || conversation.sender;

    if (contactInfo) {
      return {
        id: contactInfo.id,
        name: contactInfo.name,
        email: contactInfo.email,
        thumbnail:
          contactInfo.thumbnail ||
          contactInfo.avatarUrl || // Handle both camelCase and snake_case/Rails styles
          contactInfo.avatar_url ||
          '',
        availabilityStatus: contactInfo.availabilityStatus,
      };
    }
    if (!conversation.contactId) return undefined;
    return contactsStore.allContacts.find(c => c.id === conversation.contactId);
  }

  function getInbox(inboxId?: number) {
    if (!inboxId) return undefined;
    return inboxesStore.allInboxes.find(i => i.id === inboxId);
  }

  // Initialize
  onMount(async () => {
    // Fetch initial data
    await Promise.all([
      conversationsStore.fetchConversations(),
      contactsStore.fetchContacts(),
      inboxesStore.fetchInboxes(),
    ]);
  });
</script>

<div class="flex h-full flex-col bg-white">
  <!-- Filters -->
  <div class="shrink-0">
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
      <div class="flex flex-col px-2 pb-5">
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
