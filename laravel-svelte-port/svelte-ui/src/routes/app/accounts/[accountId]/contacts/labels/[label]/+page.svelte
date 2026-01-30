<script lang="ts">
  /**
   * Label Contacts Page
   */
  import { page } from '$app/stores';
  import ContactList from '../../_components/ContactList.svelte';

  const accountId = $derived(parseInt($page.params.accountId ?? '', 10));
  const label = $derived($page.params.label ?? '');

  // Decoding the label from URL is handled by SvelteKit routing,
  // but we might need to be careful if it defaults to encoded.
  // Generally $page.params.label is the raw param value (decoded),
  // but if it contains spaces it might be helpful to double check.
  // Assuming standard behavior.
</script>

<ContactList
  title={`Label: ${label}`}
  {accountId}
  initialFetchParams={{ labels: [label] }}
/>
