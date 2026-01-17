<script lang="ts">
  /**
   * Login Layout
   * Clean layout for login page without dashboard navigation
   */
  
  import type { Snippet } from 'svelte';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
</script>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/40 px-4 py-12">
  <div class="w-full max-w-md">
    <!-- Logo -->
    <div class="mb-8 text-center">
      <h1 class="text-3xl font-bold">Chatwoot</h1>
      <p class="mt-2 text-sm text-muted-foreground">
        Customer engagement platform
      </p>
    </div>
    
    <!-- Content -->
    <div class="rounded-lg border bg-card p-8 shadow-xs">
      {@render children()}
    </div>
    
    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-muted-foreground">
      © 2026 Chatwoot. All rights reserved.
    </div>
  </div>
</div>
