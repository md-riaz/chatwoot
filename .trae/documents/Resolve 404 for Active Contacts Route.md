I will resolve the 404 error by implementing the missing route and supporting API/store logic.

1.  **Update API Client (`src/lib/api/contacts.ts`)**:
    *   Add `getActiveContacts` function to fetch from `/api/v1/accounts/{accountId}/contacts/active`.

2.  **Update Store (`src/lib/stores/contacts.svelte.ts`)**:
    *   Add `fetchActiveContacts` action to call the new API endpoint and update the `allContacts` state.

3.  **Create Active Contacts Page**:
    *   Create directory: `src/routes/app/accounts/[accountId]/contacts/active`.
    *   Create `+page.svelte` in that directory.
    *   Replicate the functionality of the main contacts page but invoke `contactsStore.fetchActiveContacts()` on mount.
    *   Update the page title to "Active Contacts" to distinguish it from the main list.

This ensures full functional parity with the Vue implementation where the "Active" sidebar item loads a filtered list of contacts using the `active` API endpoint.