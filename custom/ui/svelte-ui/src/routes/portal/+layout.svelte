<script lang="ts">
  import type { Snippet } from 'svelte';
  import { onMount } from 'svelte';
  import { initI18n } from '$lib/i18n';

  interface Props {
    children: Snippet;
  }

  let { children }: Props = $props();

  onMount(async () => {
    // Initialize i18n for portal
    await initI18n('en');
  });
</script>

<div class="portal-container">
  {@render children()}
</div>

<style>
  .portal-container {
    min-height: 100vh;
    background: #f9fafb;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
      sans-serif;
  }

  :global(*) {
    box-sizing: border-box;
  }
</style>
