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
  import { _ } from '$lib/i18n';
  
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
      error = $_('auth.signup_page.errors.all_required');
      return;
    }
    
    if (password !== passwordConfirmation) {
      error = $_('auth.signup_page.errors.password_mismatch');
      return;
    }
    
    if (password.length < 8) {
      error = $_('auth.signup_page.errors.password_min_length');
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
      toast.success(response.message || $_('auth.signup_page.messages.registration_success'));
      
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
        error = $_('auth.signup_page.messages.registration_failed');
      }
    } finally {
      loading = false;
    }
  }
</script>

<div class="space-y-6">
  <div class="space-y-2 text-center">
    <h2 class="text-2xl font-semibold tracking-tight">{$_('auth.signup_page.title')}</h2>
    <p class="text-sm text-muted-foreground">
      {$_('auth.signup_page.description')}
    </p>
  </div>
  
  <form onsubmit={handleSubmit} class="space-y-4">
    <div class="space-y-2">
      <Label for="name">{$_('auth.signup_page.full_name')}</Label>
      <Input
        id="name"
        type="text"
        placeholder={$_('auth.signup_page.placeholders.full_name')}
        bind:value={name}
        required
        disabled={loading}
      />
    </div>
    
    <div class="space-y-2">
      <Label for="email">{$_('auth.email')}</Label>
      <Input
        id="email"
        type="email"
        placeholder={$_('auth.placeholders.email')}
        bind:value={email}
        required
        disabled={loading}
      />
    </div>
    
    <div class="space-y-2">
      <Label for="password">{$_('auth.password')}</Label>
      <Input
        id="password"
        type="password"
        placeholder={$_('auth.placeholders.password')}
        bind:value={password}
        required
        disabled={loading}
      />
      <p class="text-xs text-muted-foreground">{$_('auth.signup_page.password_hint')}</p>
    </div>
    
    <div class="space-y-2">
      <Label for="password-confirmation">{$_('auth.signup_page.confirm_password')}</Label>
      <Input
        id="password-confirmation"
        type="password"
        placeholder={$_('auth.signup_page.placeholders.confirm_password')}
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
      {loading ? $_('auth.signup_page.creating_account') : $_('auth.signup_page.submit')}
    </Button>
  </form>
  
  <div class="text-center text-sm text-muted-foreground">
    {$_('auth.signup_page.has_account')}{' '}
    <a href="/app/login" class="text-primary hover:underline">
      {$_('auth.login_page.title')}
    </a>
  </div>
</div>
