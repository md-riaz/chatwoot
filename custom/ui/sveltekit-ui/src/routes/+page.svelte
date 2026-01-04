
<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { get } from 'svelte/store';
	import { authStore } from '$lib/stores/auth';
	import { onboardingApi } from '$lib/api/client';
	
	let loading = true;
	
	onMount(async () => {
		try {
			// Check if onboarding is needed first
			const status = await onboardingApi.checkOnboardingStatus();
			if (status?.needs_onboarding) {
				goto('/onboarding');
				return;
			}
		} catch (error) {
			// If onboarding check fails, continue with auth check
			console.debug('Onboarding check failed, proceeding with auth check');
		}
		
		// Check authentication status using get() to properly access store value
		const authState = get(authStore);
		
		if (authState.isAuthenticated) {
			// User is authenticated, redirect to app
			goto('/app/super_admin/dashboard');
		} else {
			// User is not authenticated, redirect to login
			goto('/login');
		}
		
		// Note: loading = false is not needed as goto() will unmount the component
	});
</script>

<svelte:head>
	<title>Chatwoot</title>
</svelte:head>

{#if loading}
	<div class="flex min-h-screen items-center justify-center bg-background">
		<div class="text-center">
			<div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
			<p class="mt-2 text-sm text-muted-foreground">Loading...</p>
		</div>
	</div>
{/if}
