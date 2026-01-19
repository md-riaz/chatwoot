<script lang="ts">
  /**
   * Auth Layout
   * Layout for authentication pages (login, register, forgot password)
   * Centered content without navigation
   */
  
  import type { Snippet } from 'svelte';
  import Logo from '$lib/components/layout/Logo.svelte';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
</script>

<div class="flex min-h-screen flex-col items-center justify-center bg-muted/40 px-4 py-12">
  <div class="w-full max-w-md">
    <div class="mb-8 text-center">
      <div class="inline-flex items-center gap-2">
        <Logo class="h-8 w-8" aria-label="Chatwoot logo" />
        <span class="text-3xl font-bold">ClearLine</span>
      </div>
      <p class="mt-2 text-sm text-muted-foreground">Customer engagement platform</p>
    </div>
    
    <!-- Content -->
    <div class="rounded-lg border bg-card p-8 shadow-xs">
      {@render children()}
    </div>
    
    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-muted-foreground">
      © 2026 ClearLine. All rights reserved.
    </div>
  </div>
</div>
