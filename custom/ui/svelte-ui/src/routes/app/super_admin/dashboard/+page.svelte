<script lang="ts">
	import { superAdminApi } from '$lib/api/superAdmin';
	import BarChart from '$lib/components/BarChart.svelte';
	import { Skeleton } from '$lib/components/ui/skeleton';
	import { onMount } from 'svelte';
	
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
	<title>Admin Dashboard - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header matching Vue frontend -->
	<header class="px-8 py-6 border-b bg-card" role="banner">
		<h1 id="page-title" class="text-2xl font-semibold text-foreground">
			Admin Dashboard
		</h1>
	</header>

	<!-- Body matching Vue frontend -->
	<section class="p-0">
		{#if loading}
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0">
				{#each Array(4) as _}
					<div class="border-r border-b p-8 bg-card">
						<Skeleton class="h-16 w-full" />
					</div>
				{/each}
			</div>
		{:else if dashboardData}
			<!-- Report List matching Vue frontend exactly -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0">
				<!-- Accounts Card -->
				<div class="border-r border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.accounts_count || 0}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Accounts
					</div>
				</div>
				
				<!-- Users Card -->
				<div class="border-r border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.users_count || 0}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Users
					</div>
				</div>
				
				<!-- Inboxes Card -->
				<div class="border-r border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.inboxes_count || 0}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Inboxes
					</div>
				</div>
				
				<!-- Conversations Card -->
				<div class="border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.conversations_count || 0}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Conversations
					</div>
				</div>
			</div>
			
			<!-- Chart Section matching Vue frontend -->
			<div class="p-8 w-full bg-card">
				<div class="max-h-[500px]">
					{#if dashboardData?.growth}
						{@const chartData = [
							{ label: 'Accounts', value: dashboardData.growth.accounts.current },
							{ label: 'Users', value: dashboardData.growth.users.current },
							{ label: 'Conversations', value: dashboardData.growth.conversations.current }
						]}
						<BarChart data={chartData} />
					{:else}
						<div class="h-64 flex items-center justify-center border-2 border-dashed rounded-lg border-border">
							<p class="text-sm text-muted-foreground">
								No chart data available
							</p>
						</div>
					{/if}
				</div>
			</div>
		{:else}
			<div class="text-center py-12 bg-card">
				<p class="text-muted-foreground">Failed to load dashboard data</p>
			</div>
		{/if}
	</section>
</div>


