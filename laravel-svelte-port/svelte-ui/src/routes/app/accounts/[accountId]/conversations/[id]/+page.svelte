<script lang="ts">
  /**
   * Conversation Detail Page
   * Shows message header, message list, composer, and right sidebar
   * The conversation list panel is provided by the parent +layout.svelte
   *
   * Vue parity: ConversationHeader.vue + MoreActions.vue + ResolveAction.vue
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import {
    ArrowLeft,
    ChevronDown,
    MoreVertical,
    VolumeOff,
    Volume1,
    Share,
    AlarmClockMinus,
    CircleDotDashed,
  } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
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

  // Find conversation in store
  const conversation = $derived(
    conversationsStore.allConversations.find(c => c.id == conversationId)
  );

  // Contact info — Rails/Laravel returns meta.sender, with contact as fallback
  const sender = $derived(conversation?.meta?.sender ?? conversation?.contact);

  // Status helpers (Vue parity: ResolveAction.vue)
  const isOpen = $derived(conversation?.status === 'open');
  const isResolved = $derived(conversation?.status === 'resolved');
  const isPending = $derived(conversation?.status === 'pending');
  const isSnoozed = $derived(conversation?.status === 'snoozed');
  const showAdditionalActions = $derived(!isPending && !isSnoozed);
  const showOpenButton = $derived(isPending || isSnoozed);

  // Main action button label (Vue parity)
  const resolveActionLabel = $derived(
    isOpen ? 'Resolve' : isResolved ? 'Reopen' : 'Open'
  );

  // Mute state
  let isMuted = $state(false);

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

  // Main resolve action (Vue parity)
  function handleResolveAction() {
    if (isOpen) {
      handleStatusChange('resolved');
    } else {
      handleStatusChange('open');
    }
  }

  function handleMuteToggle() {
    isMuted = !isMuted;
    // TODO: wire to conversationsStore.muteConversation / unmuteConversation
  }

  function handleSendTranscript() {
    // TODO: open email transcript modal
    console.log('Send transcript clicked');
  }
</script>

<div class="flex h-full">
  <!-- Center Panel: Header + Messages + Composer -->
  <div class="flex-1 flex flex-col min-w-0 bg-background">
    <!-- Header (Vue parity: ConversationHeader.vue) -->
    <div
      class="flex flex-col gap-3 items-center justify-between flex-1 w-full min-w-0 xl:flex-row px-3 py-2 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-950 shrink-0 h-auto xl:h-12"
    >
      <!-- Left side: back + avatar + name + inbox -->
      <div
        class="flex items-center justify-start w-full xl:w-auto max-w-full min-w-0 xl:flex-1"
      >
        <!-- Mobile back button -->
        <Button
          variant="ghost"
          size="icon"
          onclick={handleBack}
          class="lg:hidden shrink-0 h-8 w-8 mr-2"
        >
          <ArrowLeft class="h-4 w-4" />
        </Button>

        {#if sender}
          <!-- Contact avatar (Vue: 32px with availability status) -->
          <div class="relative shrink-0">
            <Avatar class="h-8 w-8">
              <AvatarImage src={sender.avatarUrl || ''} alt={sender.name} />
              <AvatarFallback
                class="text-xs font-medium bg-primary/10 text-primary"
              >
                {sender.name?.charAt(0)?.toUpperCase() || '?'}
              </AvatarFallback>
            </Avatar>
          </div>

          <!-- Contact name & inbox info -->
          <div class="min-w-0 flex flex-col items-start ml-2 overflow-hidden">
            <div class="flex flex-row items-center max-w-full gap-1 p-0 m-0">
              <span
                class="text-sm font-medium truncate leading-tight text-slate-900 dark:text-slate-100"
              >
                {sender.name || 'Customer'}
              </span>
            </div>
            <div
              class="flex items-center gap-2 overflow-hidden text-xs text-slate-500 dark:text-slate-400 text-ellipsis whitespace-nowrap"
            >
              {#if conversation?.inbox?.name}
                <span class="truncate">{conversation.inbox.name}</span>
              {/if}
              {#if isSnoozed}
                <span class="font-medium text-amber-600 dark:text-amber-400">
                  Snoozed until next reply
                </span>
              {/if}
            </div>
          </div>
        {:else}
          <!-- Loading skeleton -->
          <div class="flex items-center gap-3">
            <div
              class="h-8 w-8 bg-slate-100 dark:bg-slate-800 rounded-full animate-pulse shrink-0"
            ></div>
            <div class="space-y-1">
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

      <!-- Right side: ResolveAction + MoreActions (Vue parity) -->
      <div
        class="flex flex-row items-center justify-start xl:justify-end flex-shrink-0 gap-2 w-full xl:w-auto"
      >
        {#if conversation}
          <!-- ResolveAction button group (Vue parity: ResolveAction.vue) -->
          <div class="flex items-center">
            <div
              class="flex rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden"
            >
              <!-- Main action button -->
              <Button
                variant="ghost"
                size="sm"
                onclick={handleResolveAction}
                class="rounded-none border-0 h-8 px-3 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"
              >
                {resolveActionLabel}
              </Button>

              <!-- Chevron dropdown (only for open/resolved states) -->
              {#if showAdditionalActions}
                <DropdownMenu.Root>
                  <DropdownMenu.Trigger>
                    {#snippet child({ props })}
                      <Button
                        {...props}
                        variant="ghost"
                        size="icon"
                        class="rounded-none border-0 border-l border-slate-200 dark:border-slate-700 h-8 w-7 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800"
                      >
                        <ChevronDown class="h-3.5 w-3.5" />
                      </Button>
                    {/snippet}
                  </DropdownMenu.Trigger>
                  <DropdownMenu.Content
                    align="end"
                    class="min-w-[10rem] rounded-lg"
                  >
                    <DropdownMenu.Item
                      onclick={() => handleStatusChange('snoozed')}
                      class="gap-2 text-[13px]"
                    >
                      <AlarmClockMinus class="h-4 w-4 text-slate-500" />
                      Snooze until...
                    </DropdownMenu.Item>
                    {#if !isPending}
                      <DropdownMenu.Item
                        onclick={() => handleStatusChange('pending')}
                        class="gap-2 text-[13px]"
                      >
                        <CircleDotDashed class="h-4 w-4 text-slate-500" />
                        Mark as Pending
                      </DropdownMenu.Item>
                    {/if}
                  </DropdownMenu.Content>
                </DropdownMenu.Root>
              {/if}
            </div>
          </div>

          <!-- More Actions kebab menu (Vue parity: MoreActions.vue) -->
          <DropdownMenu.Root>
            <DropdownMenu.Trigger>
              {#snippet child({ props })}
                <Button
                  {...props}
                  variant="ghost"
                  size="icon"
                  class="h-8 w-8 rounded-md text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                >
                  <MoreVertical class="h-4 w-4" />
                </Button>
              {/snippet}
            </DropdownMenu.Trigger>
            <DropdownMenu.Content align="end" class="min-w-[10rem] rounded-lg">
              <DropdownMenu.Item
                onclick={handleMuteToggle}
                class="gap-2 text-[13px]"
              >
                {#if isMuted}
                  <Volume1 class="h-4 w-4 text-slate-500" />
                  Unmute
                {:else}
                  <VolumeOff class="h-4 w-4 text-slate-500" />
                  Mute
                {/if}
              </DropdownMenu.Item>
              <DropdownMenu.Item
                onclick={handleSendTranscript}
                class="gap-2 text-[13px]"
              >
                <Share class="h-4 w-4 text-slate-500" />
                Send Transcript
              </DropdownMenu.Item>
            </DropdownMenu.Content>
          </DropdownMenu.Root>
        {:else}
          <!-- Loading placeholder for actions -->
          <div
            class="h-8 w-24 bg-slate-100 dark:bg-slate-800 rounded-lg animate-pulse"
          ></div>
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
