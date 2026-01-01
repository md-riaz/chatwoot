<script lang="ts">
	import { onMount } from 'svelte';
	import { superAdminApi } from '$lib/api/client';
	import { Card } from '$lib/components/ui/card';
	import { Skeleton } from '$lib/components/ui/skeleton';
	
	let loading = true;
	let dashboardData: any = null;
	
	onMount(async () => {
		try {
			dashboardData = await superAdminApi.getDashboard();
		} catch (error) {
			console.error('Failed to load dashboard:', error);
		} finally {
			loading = false;
		}
	});
</script>

<svelte:head>
	<title>Dashboard - Super Admin - Chatwoot</title>
</svelte:head>

<div class="p-8">
	<header class="mb-8">
		<h1 class="text-3xl font-bold">Super Admin Dashboard</h1>
		<p class="text-muted-foreground">Welcome to the Chatwoot super admin panel</p>
	</header>
	
	{#if loading}
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			{#each Array(4) as _}
				<Card.Root>
					<Card.Content class="p-6">
						<Skeleton class="h-16 w-full" />
					</Card.Content>
				</Card.Root>
			{/each}
		</div>
	{:else if dashboardData}
		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			<Card.Root>
				<Card.Content class="p-6">
					<div class="text-2xl font-bold">{dashboardData.accountsCount || 0}</div>
					<div class="text-sm text-muted-foreground">Accounts</div>
				</Card.Content>
			</Card.Root>
			
			<Card.Root>
				<Card.Content class="p-6">
					<div class="text-2xl font-bold">{dashboardData.usersCount || 0}</div>
					<div class="text-sm text-muted-foreground">Users</div>
				</Card.Content>
			</Card.Root>
			
			<Card.Root>
				<Card.Content class="p-6">
					<div class="text-2xl font-bold">{dashboardData.inboxesCount || 0}</div>
					<div class="text-sm text-muted-foreground">Inboxes</div>
				</Card.Content>
			</Card.Root>
			
			<Card.Root>
				<Card.Content class="p-6">
					<div class="text-2xl font-bold">{dashboardData.conversationsCount || 0}</div>
					<div class="text-sm text-muted-foreground">Conversations</div>
				</Card.Content>
			</Card.Root>
		</div>
		
		<!-- Chart placeholder - to be implemented -->
		<Card.Root>
			<Card.Header>
				<Card.Title>Conversations Trend</Card.Title>
			</Card.Header>
			<Card.Content class="p-6">
				<div class="h-64 flex items-center justify-center text-muted-foreground">
					Chart visualization will be implemented next
				</div>
			</Card.Content>
		</Card.Root>
	{:else}
		<div class="text-center py-12">
			<p class="text-muted-foreground">Failed to load dashboard data</p>
		</div>
	{/if}
</div>
