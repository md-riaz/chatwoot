<script lang="ts">
  import { resolve } from '$app/paths';
</script>

<div class="mx-auto max-w-md space-y-3 text-center">
  <h1 class="text-2xl font-semibold tracking-tight">Need help signing in?</h1>
  <p class="text-sm text-muted-foreground">
    Password reset is currently managed by your workspace administrator. Please contact your
    administrator to regain access.
  </p>

  <a class="text-sm text-primary hover:underline" href={resolve('/app/login')}>Back to sign in</a>
</div>
