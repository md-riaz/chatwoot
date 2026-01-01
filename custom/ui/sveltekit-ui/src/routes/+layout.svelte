<script lang="ts">
	import '../app.css';
	import { onMount } from 'svelte';
	import { ModeWatcher } from 'mode-watcher';
	import { Toaster } from 'svelte-sonner';
	import { authStore } from '$lib/stores/auth';

	let { children } = $props();
	
	onMount(() => {
		// Initialize auth store on app load
		authStore.init();
	});
</script>

<ModeWatcher />
<Toaster richColors />

{@render children()}
