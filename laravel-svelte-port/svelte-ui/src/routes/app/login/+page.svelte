<script lang="ts">
  /**
   * Login Page
   * User authentication page
   */

  import { goto } from '$app/navigation';
  import { resolve } from '$app/paths';
  import { login } from '$lib/api/auth';
  import { authStore } from '$lib/stores/auth.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { toast } from 'svelte-sonner';

  let email = $state('mdriaz@alpha.net.bd');
  let password = $state('12345678');
  let error = $state('');
  let loading = $state(false);

  function getErrorMessage(err: unknown): string {
    if (typeof err === 'object' && err !== null) {
      const knownError = err as {
        response?: { data?: { errors?: Record<string, string[]>; message?: string } };
        message?: string;
      };

      if (knownError.response?.data?.errors) {
        return Object.values(knownError.response.data.errors).flat().join(', ');
      }

      if (knownError.response?.data?.message) {
        return knownError.response.data.message;
      }

      if (knownError.message) {
        return knownError.message;
      }
    }

    return 'Login failed. Please check your credentials and try again.';
  }

  async function handleSubmit(e: Event) {
    e.preventDefault();
    error = '';
    loading = true;

    try {
      const response = await login({ email, password });

      // Store token and user data in auth store/local persistence
      if (response.user) {
        authStore.setAuthenticatedUser(response.user, response.token);

        // Redirect based on user type
        if (response.user.type === 'SuperAdmin') {
          toast.success('Logged in successfully!');
          await goto(resolve('/app/super_admin/dashboard'));
          return;
        }

        // Regular user - redirect to their account's conversations page
        if (response.user.accounts && response.user.accounts.length > 0) {
          const firstAccount = response.user.accounts[0];
          toast.success('Logged in successfully!');
          await goto(resolve(`/app/accounts/${firstAccount.id}/conversations`));
          return;
        }
      }

      toast.success('Logged in successfully!');
      await goto(resolve('/app'));
    } catch (err: unknown) {
      error = getErrorMessage(err);
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
    <a href={resolve('/login')} class="text-primary hover:underline">
      Need help signing in?
    </a>
  </div>

  <div class="text-center text-sm text-muted-foreground">
    Don't have an account?
    <a href={resolve('/app/signup')} class="text-primary hover:underline">Sign up</a>
  </div>
</div>
