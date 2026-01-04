<script lang="ts">
  /**
   * Register Page
   * User registration page
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { register } from '$lib/api/auth';
  import { goto } from '$app/navigation';
  import { toast } from 'svelte-sonner';
  
  let name = $state('');
  let email = $state('');
  let password = $state('');
  let passwordConfirmation = $state('');
  let error = $state('');
  let loading = $state(false);
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    error = '';
    
    // Client-side validation
    if (!name || !email || !password || !passwordConfirmation) {
      error = 'All fields are required';
      return;
    }
    
    if (password !== passwordConfirmation) {
      error = 'Passwords do not match';
      return;
    }
    
    if (password.length < 8) {
      error = 'Password must be at least 8 characters';
      return;
    }
    
    loading = true;
    
    try {
      const response = await register({
        name,
        email,
        password,
        passwordConfirmation
      });
      
      // Store token and user data
      if (response.token) {
        localStorage.setItem('auth_token', response.token);
      }
      
      if (response.user) {
        localStorage.setItem('current_user', JSON.stringify(response.user));
      }
      
      // Show success message
      toast.success(response.message || 'Registration successful! Please check your email to confirm your account.');
      
      // Redirect to app
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
        error = 'Registration failed. Please try again.';
      }
    } finally {
      loading = false;
    }
  }
</script>

<div class="space-y-6">
  <div class="space-y-2 text-center">
    <h2 class="text-2xl font-semibold tracking-tight">Create an account</h2>
    <p class="text-sm text-muted-foreground">
      Enter your information to get started
    </p>
  </div>
  
  <form onsubmit={handleSubmit} class="space-y-4">
    <div class="space-y-2">
      <Label for="name">Full Name</Label>
      <Input
        id="name"
        type="text"
        placeholder="John Doe"
        bind:value={name}
        required
        disabled={loading}
      />
    </div>
    
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
      <p class="text-xs text-muted-foreground">Must be at least 8 characters</p>
    </div>
    
    <div class="space-y-2">
      <Label for="password-confirmation">Confirm Password</Label>
      <Input
        id="password-confirmation"
        type="password"
        placeholder="Confirm your password"
        bind:value={passwordConfirmation}
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
      {loading ? 'Creating account...' : 'Create account'}
    </Button>
  </form>
  
  <div class="text-center text-sm text-muted-foreground">
    Already have an account?{' '}
    <a href="/app/login" class="text-primary hover:underline">
      Sign in
    </a>
  </div>
</div>
