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

	interface InstanceMetrics {
		clearlineVersion?: string;
		laravelVersion?: string;
		phpVersion?: string;
		edition?: string;
		gitSha?: string;
		database?: {
			alive: boolean;
			driver?: string;
			version?: string;
			error?: string;
		};
		redis?: {
			alive: boolean;
			version?: string;
			connectedClients?: number;
			maxclients?: number;
			usedMemoryHuman?: string;
			usedMemoryPeakHuman?: string;
			totalSystemMemoryHuman?: string;
			maxmemory?: number;
			maxmemoryPolicy?: string;
			error?: string;
		};
		queue?: {
			driver?: string;
			connection?: string;
			error?: string;
		};
		migrations?: {
			status?: string;
			ranCount?: number;
			pendingCount?: number;
			error?: string;
		};
		system?: {
			os?: string;
			memoryLimit?: string;
			maxExecutionTime?: string;
			uploadMaxFilesize?: string;
		};
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

		// ClearLine version
		if (metrics.clearlineVersion) {
			rows.push({ metric: 'ClearLine version', value: metrics.clearlineVersion });
		}

		// Git SHA
		if (metrics.gitSha) {
			rows.push({ metric: 'Git SHA', value: metrics.gitSha });
		}

		// Postgres alive
		if (metrics.database) {
			rows.push({ 
				metric: 'Postgres alive', 
				value: metrics.database.alive ? 'true' : 'false' 
			});
		}

		// Redis alive
		if (metrics.redis) {
			rows.push({ 
				metric: 'Redis alive', 
				value: metrics.redis.alive ? 'true' : 'false' 
			});

			// Redis details if alive
			if (metrics.redis.alive) {
				if (metrics.redis.version) {
					rows.push({ metric: 'Redis version', value: metrics.redis.version });
				}
				if (metrics.redis.connectedClients !== undefined) {
					rows.push({ 
						metric: 'Redis number of connected clients', 
						value: String(metrics.redis.connectedClients) 
					});
				}
				if (metrics.redis.maxclients !== undefined) {
					rows.push({ 
						metric: "Redis 'maxclients' setting", 
						value: String(metrics.redis.maxclients) 
					});
				}
				if (metrics.redis.usedMemoryHuman) {
					rows.push({ 
						metric: 'Redis memory used', 
						value: metrics.redis.usedMemoryHuman 
					});
				}
				if (metrics.redis.usedMemoryPeakHuman) {
					rows.push({ 
						metric: 'Redis memory peak', 
						value: metrics.redis.usedMemoryPeakHuman 
					});
				}
				if (metrics.redis.totalSystemMemoryHuman) {
					rows.push({ 
						metric: 'Redis total memory available', 
						value: metrics.redis.totalSystemMemoryHuman 
					});
				}
				if (metrics.redis.maxmemory !== undefined) {
					rows.push({ 
						metric: "Redis 'maxmemory' setting", 
						value: String(metrics.redis.maxmemory) 
					});
				}
				if (metrics.redis.maxmemoryPolicy) {
					rows.push({ 
						metric: "Redis 'maxmemory_policy' setting", 
						value: metrics.redis.maxmemoryPolicy 
					});
				}
			}
		}

		// ClearLine edition
		if (metrics.edition) {
			rows.push({ metric: 'ClearLine edition', value: metrics.edition });
		}

		// Database Migrations
		if (metrics.migrations?.status) {
			rows.push({ 
				metric: 'Database Migrations', 
				value: metrics.migrations.status 
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
