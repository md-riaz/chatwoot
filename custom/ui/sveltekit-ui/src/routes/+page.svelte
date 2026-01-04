
<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount, onDestroy } from 'svelte';
	import { get } from 'svelte/store';
	import { authStore } from '$lib/stores/auth';
	import { onboardingApi } from '$lib/api/client';
	
	let unsubscribe: (() => void) | null = null;
	let hasRedirected = false;
	
	onMount(async () => {
		try {
			// Check if onboarding is needed first
			const status = await onboardingApi.checkOnboardingStatus();
			// If status indicates onboarding is needed, redirect to onboarding page
			if (status?.needs_onboarding) {
				hasRedirected = true;
				goto('/onboarding');
				return;
			}
			// Otherwise, continue to authentication check below
		} catch (error) {
			// If onboarding check fails, continue with auth check
			console.debug('Onboarding check failed, proceeding with auth check');
		}
		
		// Prevent multiple redirects
		if (hasRedirected) return;
		
		// Wait for auth store to initialize if needed
		// The layout component calls authStore.init() in its onMount
		// We check the loading state and wait if initialization is still in progress
		let authState = get(authStore);
		if (authState.loading) {
			// Wait for initialization by subscribing until loading is complete
			unsubscribe = authStore.subscribe(state => {
				if (!state.loading && !hasRedirected) {
					if (unsubscribe) {
						unsubscribe();
						unsubscribe = null;
					}
					performRedirect(state.isAuthenticated);
				}
			});
		} else {
			// Auth store already initialized, perform redirect immediately
			performRedirect(authState.isAuthenticated);
		}
	});
	
	onDestroy(() => {
		// Clean up subscription if component unmounts before auth loads
		if (unsubscribe) {
			unsubscribe();
			unsubscribe = null;
		}
	});
	
	function performRedirect(isAuthenticated: boolean) {
		if (hasRedirected) return;
		hasRedirected = true;
		
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
