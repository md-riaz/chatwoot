<script lang="ts">
  /**
   * Onboarding Page
   * Super Admin installation onboarding
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { checkOnboardingStatus, completeOnboarding } from '$lib/api/onboarding';
  import { login } from '$lib/api/auth';
  import { goto } from '$app/navigation';
  import { toast } from 'svelte-sonner';
  import { onMount } from 'svelte';
  
  let loading = $state(true);
  let submitting = $state(false);
  let needsOnboarding = $state(false);
  
  let formData = $state({
    name: '',
    company: '',
    email: '',
    password: ''
  });
  
  let errors = $state<Record<string, string>>({});
  
  onMount(async () => {
    // Check if onboarding is needed
    try {
      const status = await checkOnboardingStatus();
      needsOnboarding = status?.onboardingPending || false;
      
      if (!needsOnboarding) {
        // Onboarding already complete, redirect to login
        await goto('/auth/login');
      }
    } catch (error) {
      // If API not available, assume onboarding is needed
      needsOnboarding = true;
    } finally {
      loading = false;
    }
  });
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    errors = {};
    
    // Validate
    if (!formData.name) errors.name = 'Name is required';
    if (!formData.company) errors.company = 'Company name is required';
    if (!formData.email) errors.email = 'Email is required';
    if (!formData.password || formData.password.length < 8) {
      errors.password = 'Password must be at least 8 characters';
    }
    
    if (Object.keys(errors).length > 0) return;
    
    submitting = true;
    
    try {
      await completeOnboarding(formData);
      
      toast.success('Super admin account created successfully!');
      
      // Try to log in automatically
      try {
        const response = await login({ 
          email: formData.email, 
          password: formData.password 
        });
        
        // Store token and user data
        if (response.token) {
          localStorage.setItem('auth_token', response.token);
        }
        
        if (response.user) {
          localStorage.setItem('current_user', JSON.stringify(response.user));
        }
        
        await goto('/app');
      } catch {
        // Login failed, redirect to login page
        await goto('/auth/login');
      }
    } catch (error: any) {
      if (error.response?.data?.errors) {
        // Laravel validation errors
        const apiErrors = error.response.data.errors;
        errors = Object.entries(apiErrors).reduce((acc, [key, value]) => {
          acc[key] = Array.isArray(value) ? value.join(', ') : value;
          return acc;
        }, {} as Record<string, string>);
      } else if (error.response?.data?.error) {
        toast.error(error.response.data.error);
      } else if (error.message) {
        toast.error(error.message);
      } else {
        toast.error('Failed to create admin account');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<svelte:head>
  <title>Super Admin Onboarding - Chatwoot</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-background p-4">
  {#if loading}
    <div class="text-center">
      <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
      <p class="mt-2 text-sm text-muted-foreground">Loading...</p>
    </div>
  {:else if needsOnboarding}
    <Card class="w-full max-w-md">
      <CardHeader>
        <CardTitle class="text-2xl font-bold">Welcome to Chatwoot</CardTitle>
        <CardDescription>
          Create your super admin account to get started
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onsubmit={handleSubmit} class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Full Name</Label>
            <Input
              id="name"
              type="text"
              bind:value={formData.name}
              placeholder="John Doe"
              disabled={submitting}
              class={errors.name ? 'border-destructive' : ''}
            />
            {#if errors.name}
              <p class="text-sm text-destructive">{errors.name}</p>
            {/if}
          </div>
          
          <div class="space-y-2">
            <Label for="company">Company Name</Label>
            <Input
              id="company"
              type="text"
              bind:value={formData.company}
              placeholder="Acme Inc."
              disabled={submitting}
              class={errors.company ? 'border-destructive' : ''}
            />
            {#if errors.company}
              <p class="text-sm text-destructive">{errors.company}</p>
            {/if}
          </div>
          
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              type="email"
              bind:value={formData.email}
              placeholder="admin@example.com"
              disabled={submitting}
              class={errors.email ? 'border-destructive' : ''}
            />
            {#if errors.email}
              <p class="text-sm text-destructive">{errors.email}</p>
            {/if}
          </div>
          
          <div class="space-y-2">
            <Label for="password">Password</Label>
            <Input
              id="password"
              type="password"
              bind:value={formData.password}
              placeholder="••••••••"
              disabled={submitting}
              class={errors.password ? 'border-destructive' : ''}
            />
            {#if errors.password}
              <p class="text-sm text-destructive">{errors.password}</p>
            {/if}
            <p class="text-xs text-muted-foreground">Must be at least 8 characters</p>
          </div>
          
          <Button type="submit" class="w-full" disabled={submitting}>
            {submitting ? 'Creating Account...' : 'Create Admin Account'}
          </Button>
        </form>
      </CardContent>
    </Card>
  {/if}
</div>
