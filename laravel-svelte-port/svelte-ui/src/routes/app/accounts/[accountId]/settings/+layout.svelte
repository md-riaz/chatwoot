<script lang="ts">
  /**
   * Settings Layout
   * Wrapper for settings pages to provide consistent spacing and layout
   */

  import type { Snippet } from 'svelte';

  interface Props {
    children: Snippet;
    data: {
      accountId: number;
    };
  }

  let { children, data }: Props = $props();
</script>

<div
  class="container h-full max-w-[60rem] py-8"
  data-account-id={data.accountId}
>
  {@render children()}
</div>
