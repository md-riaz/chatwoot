<script lang="ts">
  /**
   * Super Admin Root - Redirect to Dashboard
   * Redirects /app/super_admin to /app/super_admin/dashboard
   */
  
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  
  onMount(() => {
    goto('/app/super_admin/dashboard', { replaceState: true });
  });
</script>

<div class="flex min-h-screen items-center justify-center">
  <p class="text-muted-foreground">Redirecting to dashboard...</p>
</div>
