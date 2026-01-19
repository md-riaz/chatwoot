<script lang="ts">
  import { goto } from '$app/navigation';
  import type { PageLoad } from './$types';

  export const load: PageLoad = async ({ params }) => {
    const { accountId } = params;
    throw goto(302, `/app/accounts/${accountId}/inbox-view`);
  };
</script>

<svelte:head>
  <title>Account Dashboard</title>
</svelte:head>
