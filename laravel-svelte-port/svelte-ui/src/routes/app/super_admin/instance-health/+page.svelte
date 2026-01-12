<script lang="ts">
	/**
	 * Instance Health Page
	 * Displays system metrics and health status for the ClearLine instance
	 * Matches Rails SuperAdmin::InstanceStatusesController functionality
	 */
	import { onMount } from 'svelte';
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Loader2, RefreshCw } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';

	// Using Record<string, any> to match the flat structure returned by Rails-compatible API
	interface InstanceMetrics {
		[key: string]: any;
	}
	
	let metrics = $state<InstanceMetrics | null>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);
	
	async function fetchInstanceStatus() {
		loading = true;
		error = null;
			
		try {
			const data = await superAdminApi.getInstanceStatus();
			metrics = data.data as InstanceMetrics;
		} catch (err: any) {
			error = err.message || 'Failed to load instance status';
			toast.error(error);
			console.error('Instance status error:', err);
		} finally {
			loading = false;
		}
	}

	onMount(() => {
		fetchInstanceStatus();
	});

	function formatValue(value: any): string {
		if (value === null || value === undefined) return 'N/A';
		if (typeof value === 'boolean') return value ? 'true' : 'false';
		if (typeof value === 'number') return value.toLocaleString();
		return String(value);
	}

	function getMetricRows(): Array<{ metric: string; value: string }> {
		if (!metrics) return [];

		const rows: Array<{ metric: string; value: string }> = [];

		// Chatwoot version (Rails format)
		if (metrics['Chatwoot version']) {
			rows.push({ metric: 'Chatwoot version', value: metrics['Chatwoot version'] });
		}

		// Git SHA
		if (metrics['Git SHA']) {
			rows.push({ metric: 'Git SHA', value: metrics['Git SHA'] });
		}

		// Postgres alive
		if (metrics['Postgres alive']) {
			rows.push({ 
				metric: 'Postgres alive', 
				value: metrics['Postgres alive'] 
			});
		}

		// Redis metrics
		if (metrics['Redis alive']) {
			rows.push({ 
				metric: 'Redis alive', 
				value: metrics['Redis alive'] 
			});

			// Only show additional Redis details if Redis is alive
			if (metrics['Redis alive'] === 'true') {
				if (metrics['Redis version']) {
					rows.push({ metric: 'Redis version', value: metrics['Redis version'] });
				}
				if (metrics['Redis number of connected clients'] !== undefined) {
					rows.push({ 
						metric: 'Redis number of connected clients', 
						value: String(metrics['Redis number of connected clients']) 
					});
				}
				if (metrics["Redis 'maxclients' setting"] !== undefined) {
					rows.push({ 
						metric: "Redis 'maxclients' setting", 
						value: String(metrics["Redis 'maxclients' setting"]) 
					});
				}
				if (metrics['Redis memory used']) {
					rows.push({ 
						metric: 'Redis memory used', 
						value: metrics['Redis memory used'] 
					});
				}
				if (metrics['Redis memory peak']) {
					rows.push({ 
						metric: 'Redis memory peak', 
						value: metrics['Redis memory peak'] 
					});
				}
				if (metrics['Redis total memory available']) {
					rows.push({ 
						metric: 'Redis total memory available', 
						value: metrics['Redis total memory available'] 
					});
				}
				if (metrics["Redis 'maxmemory' setting"] !== undefined) {
					rows.push({ 
						metric: "Redis 'maxmemory' setting", 
						value: String(metrics["Redis 'maxmemory' setting"]) 
					});
				}
				if (metrics["Redis 'maxmemory_policy' setting"]) {
					rows.push({ 
						metric: "Redis 'maxmemory_policy' setting", 
						value: metrics["Redis 'maxmemory_policy' setting"] 
					});
				}
			}
		}

		// Chatwoot edition
		if (metrics['Chatwoot edition']) {
			rows.push({ metric: 'Chatwoot edition', value: metrics['Chatwoot edition'] });
		}

		// Database Migrations
		if (metrics['Database Migrations']) {
			rows.push({ 
				metric: 'Database Migrations', 
				value: metrics['Database Migrations'] 
			});
		}

		return rows;
	}
</script>

<div class="p-6 space-y-6">
	<!-- Page Header -->
	<div class="flex items-center justify-between">
		<div>
			<h1 class="text-3xl font-bold tracking-tight">Instance Status</h1>
			<p class="text-muted-foreground mt-1">
				System health and configuration metrics
			</p>
		</div>
		<Button onclick={() => fetchInstanceStatus()} disabled={loading} size="sm">
			{#if loading}
				<Loader2 class="mr-2 h-4 w-4 animate-spin" />
				Loading...
			{:else}
				<RefreshCw class="mr-2 h-4 w-4" />
				Refresh
			{/if}
		</Button>
	</div>

	{#if error}
		<Card class="border-destructive">
			<CardHeader>
				<CardTitle class="text-destructive">Error Loading Status</CardTitle>
			</CardHeader>
			<CardContent>
				<p class="text-sm">{error}</p>
			</CardContent>
		</Card>
	{:else if loading && !metrics}
		<div class="flex items-center justify-center py-12">
			<Loader2 class="h-8 w-8 animate-spin text-muted-foreground" />
		</div>
	{:else if metrics}
		<Card>
			<CardContent class="p-0">
				<div class="overflow-x-auto">
					<table class="w-full">
						<thead class="bg-muted/50">
							<tr>
								<th class="px-6 py-4 text-left text-sm font-semibold text-foreground">
									Metric
								</th>
								<th class="px-6 py-4 text-left text-sm font-semibold text-foreground">
									Value
								</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-border">
							{#each getMetricRows() as row, index}
								<tr class="hover:bg-muted/30 transition-colors">
									<td class="px-6 py-4 text-sm font-medium text-foreground">
										{row.metric}
									</td>
									<td class="px-6 py-4 text-sm text-muted-foreground">
										{row.value}
									</td>
								</tr>
							{/each}
						</tbody>
					</table>
				</div>
			</CardContent>
		</Card>
	{/if}
</div>
