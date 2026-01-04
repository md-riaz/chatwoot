<script lang="ts">
  /**
   * Login Page
   * User authentication page
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';
  import { goto } from '$app/navigation';
  
  let email = $state('');
  let password = $state('');
  let error = $state('');
  let loading = $state(false);
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    error = '';
    loading = true;
    
    try {
      // TODO: Implement actual login logic when API is ready
      // await authStore.login(email, password);
      
      // For now, just show a message
      error = 'Login API not yet implemented. This is a UI placeholder.';
      
      // Simulate successful login for testing
      if (email && password) {
        // Set a temporary token for testing
        localStorage.setItem('auth_token', 'test-token');
        localStorage.setItem('current_user', JSON.stringify({
          id: 1,
          name: 'Test User',
          email: email,
          avatar_url: '',
          accounts: [
            {
              id: 1,
              name: 'Test Account',
              role: 'administrator',
            }
          ],
        }));
        
        // Redirect to app
        await goto('/app');
      }
    } catch (err) {
      error = err instanceof Error ? err.message : 'Login failed';
    } finally {
      loading = false;
    }
  }
</script>

<div class="space-y-6">
  <div class="space-y-2 text-center">
    <h2 class="text-2xl font-semibold tracking-tight">Sign in</h2>
    <p class="text-sm text-muted-foreground">
      Enter your credentials to access your account
    </p>
  </div>
  
  <form onsubmit={handleSubmit} class="space-y-4">
    <div class="space-y-2">
      <Label for="email">Email</Label>
      <Input
        id="email"
        type="email"
        placeholder="name@example.com"
        bind:value={email}
        required
        disabled={loading}
      />
    </div>
    
    <div class="space-y-2">
      <Label for="password">Password</Label>
      <Input
        id="password"
        type="password"
        placeholder="Enter your password"
        bind:value={password}
        required
        disabled={loading}
      />
    </div>
    
    {#if error}
      <div class="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
        {error}
      </div>
    {/if}
    
    <Button type="submit" class="w-full" disabled={loading}>
      {loading ? 'Signing in...' : 'Sign in'}
    </Button>
  </form>
  
  <div class="text-center text-sm">
    <a href="/auth/forgot-password" class="text-primary hover:underline">
      Forgot your password?
    </a>
  </div>
  
  <div class="text-center text-sm text-muted-foreground">
    Don't have an account?{' '}
    <a href="/auth/register" class="text-primary hover:underline">
      Sign up
    </a>
  </div>
</div>
