<script lang="ts">
  /**
   * Settings Layout
   * Wrapper for settings pages to provide consistent spacing and layout
   */
  
  import type { Snippet } from 'svelte';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
</script>

<!-- Settings Content Area -->
<div class="container max-w-[60rem] py-8 h-full">
  {@render children()}
</div>
