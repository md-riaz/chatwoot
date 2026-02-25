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

<section
  class="h-full w-full overflow-auto bg-background"
  data-account-id={data.accountId}
>
  <div class="mx-auto w-full max-w-6xl px-5 py-6">
    {@render children()}
  </div>
</section>
