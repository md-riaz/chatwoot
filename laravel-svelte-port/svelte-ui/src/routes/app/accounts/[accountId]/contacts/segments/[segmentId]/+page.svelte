<script lang="ts">
  /**
   * Segment Contacts Page
   */
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import ContactList from '../../../_components/ContactList.svelte';
  import { getSegment } from '$lib/api/segments';

  const accountId = $derived(parseInt($page.params.accountId, 10));
  const segmentId = $derived(parseInt($page.params.segmentId, 10));

  let segmentName = $state('Segment');
  let isLoading = $state(true);

  $effect(() => {
    // Fetch segment details to set title
    if (accountId && segmentId) {
      isLoading = true;
      getSegment(accountId, segmentId)
        .then(segment => {
          segmentName = segment.name;
        })
        .catch(err => {
          console.error('Failed to fetch segment', err);
          segmentName = 'Segment Not Found';
        })
        .finally(() => {
          isLoading = false;
        });
    }
  });
</script>

<ContactList title={segmentName} {accountId} initialFetchParams={{}} />
