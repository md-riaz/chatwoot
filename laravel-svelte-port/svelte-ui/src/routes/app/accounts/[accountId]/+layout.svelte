<script lang="ts">
  /**
   * Account-based Layout
   * Ensures user has access to the specified account
   * Updated with Svelte 5 runes and shadcn-svelte components
   */

  import { authStore } from '$lib/stores/auth.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { customViewsStore } from '$lib/stores/customViews.svelte';
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import { segmentsStore } from '$lib/stores/segments.svelte';

  let { children } = $props();

  // Fetch data when account context is active or changes
  $effect(() => {
    const accountId = authStore.currentAccountId;
    if (authStore.isLoggedIn && accountId) {
      // Fetch sidebar data
      inboxesStore.fetchInboxes();
      labelsStore.fetchLabels();
      teamsStore.fetchTeams();
      customViewsStore.fetchCustomViews();
      notificationsStore.fetchUnreadCount(accountId);
      segmentsStore.fetchSegments();
    }
  });
</script>

<!-- This layout will wrap all account-specific routes -->
{@render children()}
