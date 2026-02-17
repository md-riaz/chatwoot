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

  let { children } = $props();

  const accountId = $derived($page.params.accountId);

  // Determine if we're viewing a specific conversation (child route has [id])
  const activeConversationId = $derived(
    $page.params.id ? Number($page.params.id) : null
  );

  // custom_view routes render their own list, so hide the default one
  const isCustomView = $derived($page.url.pathname.includes('/custom_view/'));

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
