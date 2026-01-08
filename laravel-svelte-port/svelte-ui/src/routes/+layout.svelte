<script lang="ts">
  /**
   * Root Layout
   * Initializes global services: i18n, WebSocket, theme watcher
   */
  
  import type { Snippet } from 'svelte';
  import '../app.css';
  import { ModeWatcher } from 'mode-watcher';
  import { Toaster } from 'svelte-sonner';
  import { onMount } from 'svelte';
  import { initI18n } from '$lib/i18n';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Initialize i18n on mount
  onMount(() => {
    initI18n();
  });
</script>

<ModeWatcher />
<Toaster />

{@render children()}
