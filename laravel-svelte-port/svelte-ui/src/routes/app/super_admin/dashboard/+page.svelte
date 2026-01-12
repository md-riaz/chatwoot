<script lang="ts">
	import type { DashboardData } from '$lib/api/superAdmin';
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Skeleton } from '$lib/components/ui/skeleton';
	import { onMount } from 'svelte';
	
	let loading = true;
	let dashboardData: DashboardData | null = null;
	
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
	<title>Admin Dashboard - Super Admin - ClearLine</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header matching Vue frontend -->
	<header class="px-8 py-6 border-b bg-card">
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
						{dashboardData.accountsCount || '0'}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Accounts
					</div>
				</div>
				
				<!-- Users Card -->
				<div class="border-r border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.usersCount || '0'}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Users
					</div>
				</div>
				
				<!-- Inboxes Card -->
				<div class="border-r border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.inboxesCount || '0'}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Inboxes
					</div>
				</div>
				
				<!-- Conversations Card -->
				<div class="border-b border-border p-8 bg-card hover:bg-accent/50 transition-colors">
					<div class="text-4xl font-bold text-foreground mb-2">
						{dashboardData.conversationsCount || '0'}
					</div>
					<div class="text-sm text-muted-foreground uppercase tracking-wide">
						Conversations
					</div>
				</div>
			</div>
			
			<!-- Chart Section matching Vue frontend -->
			<div class="p-8 w-full bg-card">
				<div class="max-h-[500px]">
					{#if dashboardData?.chartData && dashboardData.chartData.length > 0}
						<div class="space-y-4">
							<h3 class="text-lg font-semibold text-foreground">Conversations Over Time</h3>
							<p class="text-sm text-muted-foreground">Daily conversation count for the last 30 days</p>
							
							<!-- Summary stats -->
							<div class="flex gap-4 text-sm">
								<div class="flex items-center gap-2">
									<div class="w-3 h-3 bg-primary rounded-full"></div>
									<span>Total: {dashboardData.chartData.reduce((sum, [, count]) => sum + count, 0)} conversations</span>
								</div>
								<div class="flex items-center gap-2">
									<div class="w-3 h-3 bg-muted rounded-full"></div>
									<span>Avg: {Math.round(dashboardData.chartData.reduce((sum, [, count]) => sum + count, 0) / dashboardData.chartData.length)} per day</span>
								</div>
							</div>
							
							<!-- Simple bar chart visualization -->
							<div class="h-[200px] w-full">
								<div class="flex items-end justify-between h-full gap-1 px-2">
									{#each dashboardData.chartData.slice(-14) as [date, count]}
										{@const maxCount = Math.max(...dashboardData.chartData.map(([, c]) => c))}
										{@const height = maxCount > 0 ? (count / maxCount) * 100 : 0}
										<div class="flex flex-col items-center gap-1 flex-1">
											<div 
												class="w-full bg-primary rounded-t transition-all hover:bg-primary/80 min-h-[2px]"
												style="height: {height}%"
												title="{new Date(date).toLocaleDateString()}: {count} conversations"
											></div>
											<span class="text-xs text-muted-foreground transform -rotate-45 origin-center whitespace-nowrap">
												{new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
											</span>
										</div>
									{/each}
								</div>
							</div>
						</div>
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


