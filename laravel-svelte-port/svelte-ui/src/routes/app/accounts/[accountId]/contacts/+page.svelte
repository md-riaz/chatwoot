<script lang="ts">
  /**
   * Contacts List Page
   * Browse and manage all contacts
   */
  import { page } from '$app/stores';
  import ContactList from './_components/ContactList.svelte';

  const accountId = $derived(parseInt($page.params.accountId ?? '0', 10));
</script>

<ContactList title="Contacts" {accountId} initialFetchParams={{}} />
