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
      class="flex items-center justify-between px-4 h-14 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950 shrink-0"
    >
      <div class="flex items-center gap-3 overflow-hidden">
        <!-- Mobile back button -->
        <Button
          variant="ghost"
          size="icon"
          onclick={handleBack}
          class="lg:hidden shrink-0 h-8 w-8"
        >
          <ArrowLeft class="h-4 w-4" />
        </Button>

        {#if sender}
          <!-- Contact avatar with status dot -->
          <div class="relative shrink-0">
            <Avatar class="h-9 w-9 ring-2 ring-slate-100 dark:ring-slate-800">
              <AvatarImage src={sender.avatarUrl || ''} alt={sender.name} />
              <AvatarFallback
                class="text-xs font-semibold bg-primary/10 text-primary"
              >
                {sender.name?.charAt(0)?.toUpperCase() || '?'}
              </AvatarFallback>
            </Avatar>
            <!-- Online Status Dot -->
            <span
              class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-white dark:border-slate-950 bg-slate-400"
            ></span>
          </div>

          <!-- Contact name & info -->
          <div class="min-w-0 flex flex-col justify-center">
            <div class="flex items-center gap-1.5">
              <span
                class="font-semibold text-[13px] truncate text-slate-900 dark:text-slate-100 leading-tight"
              >
                {sender.name || 'Customer'}
              </span>
              {#if conversation?.inbox?.name}
                <Badge
                  variant="outline"
                  class="text-[10px] h-[18px] px-1.5 font-medium text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 rounded"
                >
                  {conversation.inbox.name}
                </Badge>
              {/if}
              <Badge
                variant="outline"
                class="text-[10px] h-[18px] px-1.5 font-medium text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-700 rounded"
              >
                #{conversation.displayId}
              </Badge>
            </div>
            <div
              class="text-[11px] text-slate-500 dark:text-slate-400 truncate leading-tight mt-0.5"
            >
              {sender.email || sender.phoneNumber || 'No contact info'}
            </div>
          </div>
        {:else}
          <!-- Loading skeleton -->
          <div class="flex items-center gap-3">
            <div
              class="h-9 w-9 bg-slate-100 dark:bg-slate-800 rounded-full animate-pulse shrink-0"
            ></div>
            <div class="space-y-1.5">
              <div
                class="h-3.5 w-28 bg-slate-100 dark:bg-slate-800 rounded animate-pulse"
              ></div>
              <div
                class="h-2.5 w-20 bg-slate-100 dark:bg-slate-800 rounded animate-pulse"
              ></div>
            </div>
          </div>
        {/if}
      </div>

      <!-- Status action -->
      <div class="flex items-center gap-2 shrink-0">
        {#if conversation}
          {#if conversation.status === 'snoozed'}
            <div
              class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-md text-[11px] font-semibold mr-1"
            >
              <Clock class="h-3 w-3" />
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
                  class="gap-1.5 h-8 rounded-lg border-slate-200 dark:border-slate-700 shadow-sm text-xs font-semibold"
                >
                  {#if conversation.status === 'resolved'}
                    <CheckCircle2 class="h-3.5 w-3.5 text-green-600" />
                    <span class="text-green-700 dark:text-green-400"
                      >Resolved</span
                    >
                  {:else if conversation.status === 'pending'}
                    <Clock class="h-3.5 w-3.5 text-amber-600" />
                    <span class="text-amber-700 dark:text-amber-400"
                      >Pending</span
                    >
                  {:else if conversation.status === 'snoozed'}
                    <Archive class="h-3.5 w-3.5 text-amber-600" />
                    <span class="text-amber-700 dark:text-amber-400"
                      >Snoozed</span
                    >
                  {:else}
                    <RotateCcw class="h-3.5 w-3.5 text-blue-600" />
                    <span class="text-blue-700 dark:text-blue-400">Open</span>
                  {/if}
                  <ChevronDown class="h-3 w-3 opacity-40 ml-0.5" />
                </Button>
              {/snippet}
            </DropdownMenu.Trigger>
            <DropdownMenu.Content align="end" class="min-w-[140px] rounded-lg">
              <DropdownMenu.Item
                onclick={() => handleStatusChange('open')}
                class="gap-2 text-[13px]"
              >
                <RotateCcw class="h-3.5 w-3.5 text-blue-600" /> Open
              </DropdownMenu.Item>
              <DropdownMenu.Item
                onclick={() => handleStatusChange('resolved')}
                class="gap-2 text-[13px]"
              >
                <CheckCircle2 class="h-3.5 w-3.5 text-green-600" /> Resolve
              </DropdownMenu.Item>
              <DropdownMenu.Item
                onclick={() => handleStatusChange('pending')}
                class="gap-2 text-[13px]"
              >
                <Clock class="h-3.5 w-3.5 text-amber-600" /> Pending
              </DropdownMenu.Item>
              <DropdownMenu.Separator />
              <DropdownMenu.Item
                onclick={() => handleStatusChange('snoozed')}
                class="gap-2 text-[13px]"
              >
                <Archive class="h-3.5 w-3.5 text-orange-600" /> Snooze
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
