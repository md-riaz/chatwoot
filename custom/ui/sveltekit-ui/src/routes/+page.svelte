
<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	onMount(() => {
		goto('/login');
	});
</script>

<!-- Redirecting to /login for full UI/UX parity with Vue frontend -->
