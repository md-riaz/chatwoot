<script lang="ts">
	import { goto } from '$app/navigation';
	import { authApi, onboardingApi } from '$lib/api/client';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { authStore } from '$lib/stores/auth';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	
	let loading = true;
	let submitting = false;
	let needsOnboarding = false;
	
	let formData = {
		name: '',
		company: '',
		email: '',
		password: ''
	};
	
	let errors: Record<string, string> = {};
	
	onMount(async () => {
		// Check if onboarding is needed
		try {
			const status = await onboardingApi.checkOnboardingStatus();
			needsOnboarding = status?.needs_onboarding || false;
			
			if (!needsOnboarding) {
				// Onboarding already complete, redirect to login
				goto('/login');
			}
		} catch (error) {
			// If API not available, assume onboarding is needed
			needsOnboarding = true;
		} finally {
			loading = false;
		}
	});
	
	async function handleSubmit() {
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
			const { token, client, uid, user } = await onboardingApi.completeOnboarding(formData);
			
			toast.success('Super admin account created successfully!');
			
			// Log in with the returned credentials
			authStore.login(token, user, client, uid);
			goto('/app/super_admin/dashboard');
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to create admin account');
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
				<form on:submit|preventDefault={handleSubmit} class="space-y-4">
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
