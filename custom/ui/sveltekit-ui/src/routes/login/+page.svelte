<script lang="ts">
	import { goto } from '$app/navigation';
	import { authApi } from '$lib/api/client';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Checkbox } from '$lib/components/ui/checkbox';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { authStore } from '$lib/stores/auth';
	import { toast } from 'svelte-sonner';
	
	let submitting = false;
	let formData = {
		email: '',
		password: '',
		rememberMe: false
	};
	
	let errors: Record<string, string> = {};
	
	async function handleSubmit() {
		errors = {};
		
		// Validate
		if (!formData.email) {
			errors.email = 'Email is required';
		}
		if (!formData.password) {
			errors.password = 'Password is required';
		}
		
		if (Object.keys(errors).length > 0) return;
		
		submitting = true;
		
		try {
			const { token, user } = await authApi.login(formData.email, formData.password);
			authStore.login(token, user);
			
			toast.success('Logged in successfully!');
			
			// Redirect based on user roles
			if (user.roles?.includes('super_admin')) {
				goto('/app/super_admin/dashboard');
			} else {
				// Regular user - no account routes exist yet, show message
				toast.error('Regular account interface not yet available. Please use the mobile app or contact support.');
				authStore.logout(); // Clear auth since we can't provide proper interface
			}
		} catch (error: any) {
			if (error.response?.status === 401) {
				toast.error('Invalid email or password');
			} else if (error.response?.status === 403) {
				toast.error('You do not have super admin access');
			} else if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Login failed');
			}
		} finally {
			submitting = false;
		}
	}
</script>

<svelte:head>
	<title>Super Admin Login - Chatwoot</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-background p-4">
	   <Card class="w-full max-w-md">
		   <CardHeader class="space-y-1">
			<div class="flex justify-center mb-4">
				<div class="h-12 w-12 rounded-lg bg-primary flex items-center justify-center">
					<span class="text-2xl font-bold text-primary-foreground">C</span>
				</div>
			</div>
			   <CardTitle class="text-2xl font-bold text-center">Super Admin Login</CardTitle>
			   <CardDescription class="text-center">
				Enter your credentials to access the super admin panel
			   </CardDescription>
		   </CardHeader>
		   <CardContent>
			<form on:submit|preventDefault={handleSubmit} class="space-y-4">
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
				</div>
				
				   <div class="flex items-center space-x-2">
					   <Checkbox
						   bind:checked={formData.rememberMe}
						   disabled={submitting}
					   />
					   <Label for="rememberMe" class="text-sm font-normal cursor-pointer">
						   Remember me
					   </Label>
				   </div>
				
				<Button type="submit" class="w-full" disabled={submitting}>
					{submitting ? 'Logging in...' : 'Login'}
				</Button>
			</form>
		</CardContent>
		<CardFooter class="flex flex-col space-y-2">
			<div class="text-sm text-muted-foreground text-center">
				First time? <a href="/onboarding" class="text-primary hover:underline">Set up your account</a>
			</div>
		   </CardFooter>
	   </Card>
</div>
