<script lang="ts">
  /**
   * Conversation Detail Page
   * Shows message header, message list, composer, and right sidebar
   * The conversation list panel is provided by the parent +layout.svelte
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import {
    ArrowLeft,
    CheckCircle2,
    ChevronDown,
    Clock,
    RotateCcw,
    Archive,
  } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import {
    Avatar,
    AvatarFallback,
    AvatarImage,
  } from '$lib/components/ui/avatar';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import MessageList from '$lib/components/messages/MessageList.svelte';
  import MessageComposer from '$lib/components/messages/MessageComposer.svelte';
  import ConversationSidebar from '$lib/components/conversations/ConversationSidebar.svelte';
  import type { ConversationStatus } from '$lib/api/conversations';

  // Reactive params from URL
  const accountId = $derived(Number($page.params.accountId));
  const conversationId = $derived(Number($page.params.id));

  // Find conversation in store (use == for safety with number coercion)
  const conversation = $derived(
    conversationsStore.allConversations.find(c => c.id == conversationId)
  );

  // Contact info from conversation meta
  const sender = $derived(conversation?.meta?.sender);

  const statusLabels: Record<string, string> = {
    open: 'Open',
    resolved: 'Resolved',
    pending: 'Pending',
    snoozed: 'Snoozed',
  };

  // Load conversation data when page mounts or conversationId changes
  $effect(() => {
    if (conversationId && !conversation) {
      conversationsStore.fetchConversation(conversationId).catch((err: any) => {
        console.error('Failed to load conversation:', err);
      });
    }
  });

  // Also set selected conversation in the store for list highlighting
  $effect(() => {
    if (conversationId) {
      conversationsStore.setSelectedConversation(conversationId);
    }
  });

  function handleBack() {
    goto(`/app/accounts/${accountId}/conversations`);
  }

  async function handleStatusChange(status: ConversationStatus) {
    if (!conversation) return;
    try {
      await conversationsStore.updateStatus(conversation.id, status);
    } catch (error) {
      console.error('Failed to update status:', error);
    }
  }
</script>

<div class="flex h-full">
  <!-- Center Panel: Header + Messages + Composer -->
  <div class="flex-1 flex flex-col min-w-0 bg-background">
    <!-- Header -->
    <div
      class="flex items-center justify-between px-4 h-16 border-b bg-background shrink-0"
    >
      <div class="flex items-center gap-3 overflow-hidden">
        <!-- Mobile back button -->
        <Button
          variant="ghost"
          size="icon"
          onclick={handleBack}
          class="lg:hidden shrink-0"
        >
          <ArrowLeft class="h-5 w-5" />
        </Button>

        {#if sender}
          <!-- Contact avatar with status dot -->
          <div class="relative shrink-0">
            <Avatar class="h-10 w-10 border border-border">
              <AvatarImage src={sender.avatarUrl || ''} alt={sender.name} />
              <AvatarFallback class="text-sm">
                {sender.name?.charAt(0)?.toUpperCase() || '?'}
              </AvatarFallback>
            </Avatar>
            <!-- Online Status Dot (Mock logic for now) -->
            <span
              class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-background bg-slate-400"
            ></span>
          </div>

          <!-- Contact name & info -->
          <div class="min-w-0 flex flex-col justify-center">
            <div class="flex items-center gap-2">
              <span class="font-semibold text-sm truncate text-foreground">
                {sender.name || 'Customer'}
              </span>
              {#if conversation?.inbox?.name}
                <Badge
                  variant="outline"
                  class="text-[10px] h-5 px-1.5 font-medium text-muted-foreground"
                >
                  {conversation.inbox.name}
                </Badge>
              {/if}
              <Badge
                variant="outline"
                class="text-[10px] h-5 px-1.5 font-medium text-muted-foreground"
              >
                #{conversation.displayId}
              </Badge>
            </div>
            <div class="text-xs text-muted-foreground truncate">
              {sender.email || sender.phoneNumber || 'No contact info'}
            </div>
          </div>
        {:else}
          <!-- Loading skeleton -->
          <div class="flex items-center gap-3">
            <div
              class="h-10 w-10 bg-muted rounded-full animate-pulse shrink-0"
            ></div>
            <div class="space-y-1.5">
              <div class="h-4 w-32 bg-muted rounded animate-pulse"></div>
              <div class="h-3 w-24 bg-muted rounded animate-pulse"></div>
            </div>
          </div>
        {/if}
      </div>

      <!-- Status action -->
      <div class="flex items-center gap-2 shrink-0">
        {#if conversation}
          {#if conversation.status === 'snoozed'}
            <div
              class="hidden md:flex items-center gap-1.5 px-2 py-1 bg-yellow-100 text-yellow-800 rounded-md text-xs font-medium mr-2"
            >
              <Clock class="h-3.5 w-3.5" />
              <span>Snoozed</span>
            </div>
          {/if}

          <DropdownMenu.Root>
            <DropdownMenu.Trigger>
              {#snippet child({ props })}
                <Button
                  {...props}
                  variant="outline"
                  size="sm"
                  class="gap-1.5 h-8"
                >
                  {#if conversation.status === 'resolved'}
                    <CheckCircle2 class="h-4 w-4 text-green-600" />
                    <span class="text-green-700">Resolved</span>
                  {:else if conversation.status === 'pending'}
                    <Clock class="h-4 w-4 text-yellow-600" />
                    <span class="text-yellow-700">Pending</span>
                  {:else if conversation.status === 'snoozed'}
                    <Archive class="h-4 w-4 text-yellow-600" />
                    <span class="text-yellow-700">Snoozed</span>
                  {:else}
                    <RotateCcw class="h-4 w-4 text-blue-600" />
                    <span class="text-blue-700">Open</span>
                  {/if}
                  <ChevronDown class="h-3.5 w-3.5 opacity-50 ml-1" />
                </Button>
              {/snippet}
            </DropdownMenu.Trigger>
            <DropdownMenu.Content align="end">
              <DropdownMenu.Item onclick={() => handleStatusChange('open')}>
                <RotateCcw class="mr-2 h-4 w-4 text-blue-600" /> Open
              </DropdownMenu.Item>
              <DropdownMenu.Item onclick={() => handleStatusChange('resolved')}>
                <CheckCircle2 class="mr-2 h-4 w-4 text-green-600" /> Resolve
              </DropdownMenu.Item>
              <DropdownMenu.Item onclick={() => handleStatusChange('pending')}>
                <Clock class="mr-2 h-4 w-4 text-yellow-600" /> Pending
              </DropdownMenu.Item>
              <DropdownMenu.Separator />
              <DropdownMenu.Item onclick={() => handleStatusChange('snoozed')}>
                <Archive class="mr-2 h-4 w-4 text-orange-600" /> Snooze
              </DropdownMenu.Item>
            </DropdownMenu.Content>
          </DropdownMenu.Root>
        {/if}
      </div>
    </div>

    <!-- Message List -->
    <div class="flex-1 min-h-0 bg-slate-50 dark:bg-slate-900/30">
      <MessageList {conversationId} />
    </div>

    <!-- Message Composer -->
    <div class="border-t bg-background">
      <MessageComposer {conversationId} />
    </div>
  </div>

  <!-- Right Panel: Conversation Sidebar (visible on lg+) -->
  <ConversationSidebar {conversationId} class="hidden lg:flex shrink-0" />
</div>
