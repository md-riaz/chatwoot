<script lang="ts">
  /**
   * Login Layout
   * Clean layout for login page without dashboard navigation
   */
  
  import type { Snippet } from 'svelte';
  import Logo from '$lib/components/layout/Logo.svelte';
  import { _ } from '$lib/i18n';
  
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
        <span class="text-3xl font-bold">Chatwoot</span>
      </div>
      <p class="mt-2 text-sm text-muted-foreground">{$_('auth.layout.tagline')}</p>
    </div>
    
    <!-- Content -->
    <div class="rounded-lg border bg-card p-8 shadow-xs">
      {@render children()}
    </div>
    
    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-muted-foreground">
      {$_('auth.layout.footer_copyright')}
    </div>
  </div>
</div>
