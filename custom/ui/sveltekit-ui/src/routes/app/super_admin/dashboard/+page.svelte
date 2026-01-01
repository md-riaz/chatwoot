<script lang="ts">
	import { onMount } from 'svelte';
	import { superAdminApi } from '$lib/api/client';
	import { Card } from '$lib/components/ui/card';
	import { Skeleton } from '$lib/components/ui/skeleton';
	import BarChart from '$lib/components/BarChart.svelte';
	
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
	<header class="px-8 py-6 border-b bg-white dark:bg-slate-1" role="banner">
		<h1 id="page-title" class="text-2xl font-semibold text-slate-12">
			Admin Dashboard
		</h1>
	</header>

	<!-- Body matching Vue frontend -->
	<section class="p-0">
		{#if loading}
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0">
				{#each Array(4) as _}
					<div class="border-r border-b p-8 bg-white dark:bg-slate-1">
						<Skeleton class="h-16 w-full" />
					</div>
				{/each}
			</div>
		{:else if dashboardData}
			<!-- Report List matching Vue frontend exactly -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-0">
				<!-- Accounts Card -->
				<div class="border-r border-b border-slate-6 p-8 bg-white dark:bg-slate-1 hover:bg-slate-1 dark:hover:bg-slate-2 transition-colors">
					<div class="text-4xl font-bold text-slate-12 mb-2">
						{dashboardData.accountsCount || 0}
					</div>
					<div class="text-sm text-slate-11 uppercase tracking-wide">
						Accounts
					</div>
				</div>
				
				<!-- Users Card -->
				<div class="border-r border-b border-slate-6 p-8 bg-white dark:bg-slate-1 hover:bg-slate-1 dark:hover:bg-slate-2 transition-colors">
					<div class="text-4xl font-bold text-slate-12 mb-2">
						{dashboardData.usersCount || 0}
					</div>
					<div class="text-sm text-slate-11 uppercase tracking-wide">
						Users
					</div>
				</div>
				
				<!-- Inboxes Card -->
				<div class="border-r border-b border-slate-6 p-8 bg-white dark:bg-slate-1 hover:bg-slate-1 dark:hover:bg-slate-2 transition-colors">
					<div class="text-4xl font-bold text-slate-12 mb-2">
						{dashboardData.inboxesCount || 0}
					</div>
					<div class="text-sm text-slate-11 uppercase tracking-wide">
						Inboxes
					</div>
				</div>
				
				<!-- Conversations Card -->
				<div class="border-b border-slate-6 p-8 bg-white dark:bg-slate-1 hover:bg-slate-1 dark:hover:bg-slate-2 transition-colors">
					<div class="text-4xl font-bold text-slate-12 mb-2">
						{dashboardData.conversationsCount || 0}
					</div>
					<div class="text-sm text-slate-11 uppercase tracking-wide">
						Conversations
					</div>
				</div>
			</div>
			
			<!-- Chart Section matching Vue frontend -->
			<div class="p-8 w-full bg-white dark:bg-slate-1">
				<div class="max-h-[500px]">
					{#if dashboardData?.chartData && dashboardData.chartData.length > 0}
						<BarChart data={dashboardData.chartData} />
					{:else}
						<div class="h-64 flex items-center justify-center border-2 border-dashed rounded-lg" style="border-color: rgb(var(--slate-6));">
							<p class="text-sm" style="color: rgb(var(--slate-10));">
								No chart data available
							</p>
						</div>
					{/if}
				</div>
			</div>
		{:else}
			<div class="text-center py-12 bg-white dark:bg-slate-1">
				<p class="text-slate-11">Failed to load dashboard data</p>
			</div>
		{/if}
	</section>
</div>

<style>
	/* Custom styles to match Vue frontend exactly */
	:global(.text-slate-12) {
		color: rgb(var(--slate-12));
	}
	
	:global(.text-slate-11) {
		color: rgb(var(--slate-11));
	}
	
	:global(.text-slate-10) {
		color: rgb(var(--slate-10));
	}
	
	:global(.bg-slate-1) {
		background-color: rgb(var(--slate-1));
	}
	
	:global(.bg-slate-2) {
		background-color: rgb(var(--slate-2));
	}
	
	:global(.border-slate-6) {
		border-color: rgb(var(--slate-6));
	}
	
	:global(.hover\:bg-slate-1:hover) {
		background-color: rgb(var(--slate-1));
	}
	
	:global(.dark .hover\:bg-slate-2:hover) {
		background-color: rgb(var(--slate-2));
	}
</style>
