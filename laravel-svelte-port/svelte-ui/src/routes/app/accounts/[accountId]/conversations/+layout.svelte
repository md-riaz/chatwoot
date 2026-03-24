<script lang="ts">
  /**
   * Conversations Layout
   * Provides persistent master-detail layout:
   *   Left panel: Conversation list (always visible on desktop)
   *   Right area: Child page content (empty state or conversation detail)
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import ConversationList from '$lib/components/conversations/ConversationList.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';

  let { children } = $props();

  const accountId = $derived($page.params.accountId);
  const pathname = $derived($page.url.pathname);

  // Determine if we're viewing a specific conversation (child route has [id])
  const activeConversationId = $derived(
    $page.params.id ? Number($page.params.id) : null
  );

  // custom_view routes render their own list, so hide the default one
  const isCustomView = $derived($page.url.pathname.includes('/custom_view/'));

  const routeScope = $derived.by(() => {
    const inboxId =
      pathname.includes('/conversations/inbox/') &&
      $page.params.inboxId &&
      !$page.params.id
        ? Number($page.params.inboxId)
        : null;
    const teamId =
      pathname.includes('/conversations/team/') && $page.params.teamId
        ? Number($page.params.teamId)
        : null;
    const label =
      pathname.includes('/conversations/label/') && $page.params.label
        ? decodeURIComponent($page.params.label)
        : null;

    return {
      mentionedOnly:
        pathname.includes('/conversations/mentions') && !$page.params.id,
      unattendedOnly:
        pathname.includes('/conversations/unattended') && !$page.params.id,
      currentInboxId: Number.isFinite(inboxId) ? inboxId : null,
      currentTeamId: Number.isFinite(teamId) ? teamId : null,
      currentLabel: label,
    };
  });

  let lastAppliedScope = {
    mentionedOnly: false,
    unattendedOnly: false,
    currentInboxId: null as number | null,
    currentTeamId: null as number | null,
    currentLabel: null as string | null,
  };

  $effect(() => {
    if (isCustomView) {
      return;
    }

    const nextScope = routeScope;
    const hasScopeChanged =
      nextScope.mentionedOnly !== lastAppliedScope.mentionedOnly ||
      nextScope.unattendedOnly !== lastAppliedScope.unattendedOnly ||
      nextScope.currentInboxId !== lastAppliedScope.currentInboxId ||
      nextScope.currentTeamId !== lastAppliedScope.currentTeamId ||
      nextScope.currentLabel !== lastAppliedScope.currentLabel;

    conversationsStore.mentionedOnly = nextScope.mentionedOnly;
    conversationsStore.unattendedOnly = nextScope.unattendedOnly;
    conversationsStore.currentInboxId = nextScope.currentInboxId;
    conversationsStore.currentTeamId = nextScope.currentTeamId;
    conversationsStore.currentLabel = nextScope.currentLabel;

    if (hasScopeChanged) {
      lastAppliedScope = { ...nextScope };
      conversationsStore.fetchConversations().catch(err => {
        console.error('Failed to fetch scoped conversations:', err);
      });
    }
  });

  function handleConversationSelect(conversationId: number) {
    goto(`/app/accounts/${accountId}/conversations/${conversationId}`);
  }
</script>

<section class="flex w-full h-full bg-background">
  <!-- Left Panel: Conversation List (persistent, hidden for custom_view routes) -->
  {#if !isCustomView}
    <div
      class="flex flex-col h-full w-full lg:min-w-[340px] lg:max-w-[340px] border-r border-border
             {activeConversationId ? 'hidden lg:flex' : 'flex'}"
    >
      <ConversationList
        onConversationSelect={handleConversationSelect}
        activeId={activeConversationId}
      />
    </div>
  {/if}

  <!-- Right Area: Child page (empty state or conversation detail) -->
  <div class="flex-1 min-w-0 flex flex-col h-full">
    {@render children()}
  </div>
</section>
