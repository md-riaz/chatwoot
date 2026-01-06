<script lang="ts">
  /**
   * Login Page
   * User authentication page
   */
  
  import { goto } from '$app/navigation';
  import { login } from '$lib/api/auth';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { toast } from 'svelte-sonner';
  
  let email = $state('mdriaz@alpha.net.bd');
  let password = $state('12345678');
  let error = $state('');
  let loading = $state(false);
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    error = '';
    loading = true;
    
    try {
      const response = await login({ email, password });
      
      // Store token and user data
      if (response.token) {
        localStorage.setItem('auth_token', response.token);
      }
      
      if (response.user) {
        localStorage.setItem('current_user', JSON.stringify(response.user));
        
        // Redirect based on user roles
        if (response.user.roles?.includes('super_admin')) {
          // Super admin - redirect to super admin dashboard
          toast.success('Logged in successfully!');
          await goto('/app/super_admin/dashboard');
          return;
        }
        
        // Regular user - redirect to their account's conversations page
        if (response.user.accounts && response.user.accounts.length > 0) {
          const firstAccount = response.user.accounts[0];
          toast.success('Logged in successfully!');
          await goto(`/app/accounts/${firstAccount.id}/conversations`);
          return;
        }
      }
      
      // Show success message
      toast.success('Logged in successfully!');
      
      // Fallback: redirect to app root (should not happen if user has accounts)
      await goto('/app');
    } catch (err: any) {
      if (err.response?.data?.errors) {
        // Laravel validation errors
        const errors = err.response.data.errors;
        error = Object.values(errors).flat().join(', ');
      } else if (err.response?.data?.message) {
        error = err.response.data.message;
      } else if (err.message) {
        error = err.message;
      } else {
        error = 'Login failed. Please check your credentials and try again.';
      }
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
    <a href="/app/auth/signup" class="text-primary hover:underline">
      Sign up
    </a>
  </div>
</div>
