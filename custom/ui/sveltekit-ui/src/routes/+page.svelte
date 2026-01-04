
<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { get } from 'svelte/store';
	import { authStore } from '$lib/stores/auth';
	import { onboardingApi } from '$lib/api/client';
	
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
		
		// Wait for auth store to initialize
		// The store's loading state will be false once initialized
		let authState = get(authStore);
		if (authState.loading) {
			// Wait for initialization by subscribing briefly
			const unsubscribe = authStore.subscribe(state => {
				if (!state.loading) {
					unsubscribe();
					authState = state;
					performRedirect(authState.isAuthenticated);
				}
			});
		} else {
			performRedirect(authState.isAuthenticated);
		}
	});
	
	function performRedirect(isAuthenticated: boolean) {
		if (isAuthenticated) {
			// User is authenticated, redirect to app
			goto('/app/super_admin/dashboard');
		} else {
			// User is not authenticated, redirect to login
			goto('/login');
		}
	}
</script>

<svelte:head>
	<title>Chatwoot</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-background">
	<div class="text-center">
		<div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
		<p class="mt-2 text-sm text-muted-foreground">Loading...</p>
	</div>
</div>
