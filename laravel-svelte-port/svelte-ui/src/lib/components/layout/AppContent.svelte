<script lang="ts">
  /**
   * AppContent - Main content wrapper
   * Provides consistent padding and max-width for content area
   */
  
  import type { Snippet } from 'svelte';
  
  interface Props {
    class?: string;
    children?: Snippet;
  }
  
  let { class: className = '', children }: Props = $props();
</script>

<main class="flex-1 overflow-auto bg-background {className}">
  <div class="container mx-auto px-4 py-6 lg:px-8">
    {#if children}
      {@render children()}
    {/if}
  </div>
</main>
