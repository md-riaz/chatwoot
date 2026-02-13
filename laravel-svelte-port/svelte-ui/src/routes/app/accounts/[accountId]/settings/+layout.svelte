<script lang="ts">
  import type { Snippet } from 'svelte';

  interface Props {
    children: Snippet;
    data: {
      accountId: number;
    };
  }

  let { children, data }: Props = $props();
</script>

<section class="h-full w-full px-6 py-6" data-account-id={data.accountId}>
  {@render children()}
</section>
