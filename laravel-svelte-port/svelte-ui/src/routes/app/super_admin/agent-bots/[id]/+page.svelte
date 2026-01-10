<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { api } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Badge } from '$lib/components/ui/badge';
	import { ArrowLeft, Edit, Trash2, Copy, Eye, EyeOff, ExternalLink } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	const botId = $page.params.id;

	let loading = $state(true);
	let bot: any = null;
	let showAccessToken = $state(false);

	onMount(async () => {
		if (!botId) {
			toast.error('Invalid bot ID');
			goto('/app/super_admin/agent-bots');
			return;
		}
		await loadBot();
	});

	async function loadBot() {
		if (!botId) return;
		
		loading = true;
		try {
			bot = await api.agentBots.get(botId); // This already returns the bot data directly
		} catch (error: any) {
			toast.error('Failed to load agent bot: ' + (error.message || 'Unknown error'));
			goto('/app/super_admin/agent-bots');
		} finally {
			loading = false;
		}
	}

	async function handleDelete() {
		if (!botId) return;
		
		if (!confirm('Are you sure you want to delete this agent bot? This action cannot be undone.')) {
			return;
		}

		try {
			await api.agentBots.delete(botId);
			toast.success('Agent bot deleted successfully');
			goto('/app/super_admin/agent-bots');
		} catch (error: any) {
			toast.error('Failed to delete agent bot: ' + (error.message || 'Unknown error'));
		}
	}

	function copyAccessToken() {
		if (bot?.accessToken) {
			navigator.clipboard.writeText(bot.accessToken);
			toast.success('Access token copied to clipboard');
		}
	}
</script>

<svelte:head>
	<title>Agent Bot Details - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center">
			<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/agent-bots')}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div class="ml-4">
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : `Show AgentBot #${bot?.id || botId}`}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					Agent Bots
				</p>
			</div>
		</div>
		<div class="flex items-center gap-2">
			<Button variant="outline" onclick={() => goto(`/app/super_admin/agent-bots/${botId}/edit`)} disabled={loading}>
				<Edit class="h-4 w-4 mr-2" />
				Edit
			</Button>
			<Button variant="destructive" onclick={handleDelete} disabled={loading}>
				<Trash2 class="h-4 w-4 mr-2" />
				Delete
			</Button>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		{#if loading}
			<Card class="max-w-4xl">
				<CardContent class="p-6">
					<div class="space-y-4">
						<div class="h-10 w-full animate-pulse rounded bg-muted"></div>
						<div class="h-10 w-full animate-pulse rounded bg-muted"></div>
						<div class="h-32 w-full animate-pulse rounded bg-muted"></div>
					</div>
				</CardContent>
			</Card>
		{/if}

		{#if !loading && bot}
			<div class="max-w-4xl space-y-6">
				<!-- Basic Information -->
				<Card>
					<CardHeader>
						<CardTitle>Bot Information</CardTitle>
						<CardDescription>Basic details about this agent bot</CardDescription>
					</CardHeader>
					<CardContent class="space-y-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- Left Column -->
							<div class="space-y-4">
								<div>
									<label class="text-sm font-medium text-muted-foreground">ID</label>
									<p class="text-lg font-mono">{bot.id}</p>
								</div>

								<div>
									<label class="text-sm font-medium text-muted-foreground">ACCOUNT</label>
									{#if bot.account}
										<p class="text-lg">
											<a 
												href="/app/super_admin/accounts/{bot.account.id}" 
												class="text-blue-600 hover:text-blue-800 hover:underline"
											>
												#{bot.account.id} {bot.account.name}
											</a>
										</p>
									{:else}
										<Badge variant="secondary">Global Bot (All Accounts)</Badge>
									{/if}
								</div>

								<div>
									<label class="text-sm font-medium text-muted-foreground">NAME</label>
									<p class="text-lg">{bot.name}</p>
								</div>

								<div>
									<label class="text-sm font-medium text-muted-foreground">DESCRIPTION</label>
									<p class="text-lg">{bot.description || '-'}</p>
								</div>
							</div>

							<!-- Right Column -->
							<div class="space-y-4">
								<div>
									<label class="text-sm font-medium text-muted-foreground">AVATAR URL</label>
									{#if bot.avatarUrl}
										<div class="flex items-center gap-3">
											<img 
												src={bot.avatarUrl} 
												alt="{bot.name} avatar" 
												class="h-16 w-16 rounded-lg object-cover border border-border"
											/>
											<div>
												<p class="text-sm font-mono break-all">{bot.avatarUrl}</p>
												<a 
													href={bot.avatarUrl} 
													target="_blank" 
													rel="noopener noreferrer"
													class="text-blue-600 hover:text-blue-800 text-sm inline-flex items-center gap-1"
												>
													View Full Size <ExternalLink class="h-3 w-3" />
												</a>
											</div>
										</div>
									{:else}
										<div class="flex items-center gap-3">
											<div class="h-16 w-16 rounded-lg bg-muted border border-border flex items-center justify-center">
												<span class="text-lg font-medium text-muted-foreground">
													{bot.name.charAt(0).toUpperCase()}
												</span>
											</div>
											<p class="text-muted-foreground">No avatar uploaded</p>
										</div>
									{/if}
								</div>

								<div>
									<label class="text-sm font-medium text-muted-foreground">OUTGOING URL</label>
									{#if bot.outgoingUrl}
										<p class="text-lg font-mono break-all">{bot.outgoingUrl}</p>
									{:else}
										<p class="text-muted-foreground">-</p>
									{/if}
								</div>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Access Token -->
				{#if bot.accessToken}
					<Card>
						<CardHeader>
							<CardTitle>Access Token</CardTitle>
							<CardDescription>API authentication token for this bot</CardDescription>
						</CardHeader>
						<CardContent>
							<div class="space-y-4">
								<div>
									<label class="text-sm font-medium text-muted-foreground">TOKEN</label>
									<div class="flex items-center gap-2 mt-2">
										<div class="flex-1 relative">
											<input
												type={showAccessToken ? 'text' : 'password'}
												value={bot.accessToken}
												readonly
												class="w-full px-3 py-2 border border-border rounded-md bg-muted font-mono text-sm pr-16"
											/>
											<div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
												<button
													type="button"
													onclick={() => showAccessToken = !showAccessToken}
													class="p-1 hover:bg-background rounded"
												>
													{#if showAccessToken}
														<EyeOff class="h-4 w-4" />
													{:else}
														<Eye class="h-4 w-4" />
													{/if}
												</button>
												<button
													type="button"
													onclick={copyAccessToken}
													class="p-1 hover:bg-background rounded"
												>
													<Copy class="h-4 w-4" />
												</button>
											</div>
										</div>
									</div>
									<p class="text-xs text-muted-foreground mt-2">
										Use this token to authenticate API requests for this bot. To get a new token, delete and recreate the bot.
									</p>
								</div>
							</div>
						</CardContent>
					</Card>
				{/if}

				<!-- Metadata -->
				<Card>
					<CardHeader>
						<CardTitle>Metadata</CardTitle>
						<CardDescription>Creation and modification timestamps</CardDescription>
					</CardHeader>
					<CardContent>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="text-sm font-medium text-muted-foreground">CREATED AT</label>
								<p class="text-lg">{new Date(bot.createdAt).toLocaleString()}</p>
							</div>
							<div>
								<label class="text-sm font-medium text-muted-foreground">UPDATED AT</label>
								<p class="text-lg">{new Date(bot.updatedAt).toLocaleString()}</p>
							</div>
						</div>
					</CardContent>
				</Card>
			</div>
		{/if}

		{#if !loading && !bot}
			<Card class="max-w-4xl">
				<CardContent class="p-6">
					<p>No bot data available</p>
				</CardContent>
			</Card>
		{/if}
	</section>
</div>