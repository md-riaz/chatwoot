<script lang="ts">
import { goto } from '$app/navigation';
import { page } from '$app/stores';
import { api } from '$lib/api/superAdmin';
import { Button } from '$lib/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
import * as Dialog from '$lib/components/ui/dialog';
import { ArrowLeft, Edit, Trash2, Copy, Eye, EyeOff } from 'lucide-svelte';
import { onMount } from 'svelte';
import { toast } from 'svelte-sonner';
import type { PlatformApp } from '$lib/api/superAdmin';

const platformAppId = $page.params.id;

let loading = $state(true);
let platformApp: PlatformApp | null = $state(null);  /* Make platformApp reactive with $state() */
let showAccessToken = $state(false);
let showDeleteDialog = $state(false);
let deleting = $state(false);

onMount(async () => {
	if (!platformAppId) {
		toast.error('Invalid platform app ID');
		goto('/app/super_admin/platform-apps');
		return;
	}
	await loadPlatformApp();
});

async function loadPlatformApp() {
	if (!platformAppId) return;
	
	loading = true;
	try {
		platformApp = await api.platformApps.get(platformAppId);
	} catch (error: any) {
		toast.error('Failed to load platform app: ' + (error.message || 'Unknown error'));
		goto('/app/super_admin/platform-apps');
	} finally {
		loading = false;
	}
}

function openDeleteDialog() {
	showDeleteDialog = true;
}

async function confirmDelete() {
	if (!platformAppId || deleting) return;
	
	deleting = true;
	try {
		await api.platformApps.delete(platformAppId);
		toast.success('Platform app deleted successfully');
		showDeleteDialog = false;
		goto('/app/super_admin/platform-apps');
	} catch (error: any) {
		toast.error('Failed to delete platform app: ' + (error.message || 'Unknown error'));
	} finally {
		deleting = false;
	}
}

function cancelDelete() {
	showDeleteDialog = false;
}

function copyAccessToken() {
	if (platformApp?.accessToken) {
		navigator.clipboard.writeText(platformApp.accessToken);
		toast.success('Access token copied to clipboard');
	}
}
</script>

<svelte:head>
	<title>Platform App Details - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center">
			<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/platform-apps')}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div class="ml-4">
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : platformApp?.name || 'Platform App Details'}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					<a href="/app/super_admin/platform-apps" class="hover:text-iris-9">Platform Apps</a>
					/ {platformApp?.name || 'Loading...'}
				</p>
			</div>
		</div>
		<div class="flex items-center gap-2">
			<Button variant="outline" onclick={() => goto(`/app/super_admin/platform-apps/${platformAppId}/edit`)} disabled={loading}>
				<Edit class="h-4 w-4 mr-2" />
				Edit
			</Button>
			<Button variant="destructive" onclick={openDeleteDialog} disabled={loading}>
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

		{#if !loading && platformApp}
			<div class="max-w-4xl space-y-6">
				<!-- Basic Information -->
				<Card>
					<CardHeader>
						<CardTitle>Platform App Information</CardTitle>
						<CardDescription>Basic details about this platform app</CardDescription>
					</CardHeader>
					<CardContent class="space-y-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- Left Column -->
							<div class="space-y-4">
								<div>
									<p class="text-sm font-medium text-muted-foreground">ID</p>
									<p class="text-lg font-mono">{platformApp.id}</p>
								</div>

								<div>
									<p class="text-sm font-medium text-muted-foreground">NAME</p>
									<p class="text-lg">{platformApp.name}</p>
								</div>
							</div>

							<!-- Right Column -->
							<div class="space-y-4">
								<div>
									<p class="text-sm font-medium text-muted-foreground">CREATED AT</p>
									<p class="text-lg">{new Date(platformApp.createdAt).toLocaleString()}</p>
								</div>

								<div>
									<p class="text-sm font-medium text-muted-foreground">UPDATED AT</p>
									<p class="text-lg">{new Date(platformApp.updatedAt).toLocaleString()}</p>
								</div>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Access Token -->
				{#if platformApp.accessToken}
					<Card>
						<CardHeader>
							<CardTitle>Access Token</CardTitle>
							<CardDescription>API authentication token for this platform app</CardDescription>
						</CardHeader>
						<CardContent>
							<div class="space-y-4">
								<div>
									<p class="text-sm font-medium text-muted-foreground">TOKEN</p>
									<div class="flex items-center gap-2 mt-2">
										<div class="flex-1 relative">
											<input
												type={showAccessToken ? 'text' : 'password'}
												value={platformApp.accessToken}
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
										Use this token to authenticate API requests for this platform app. Keep this token secure and do not share it publicly.
									</p>
								</div>
							</div>
						</CardContent>
					</Card>
				{/if}
			</div>
		{/if}

		{#if !loading && !platformApp}
			<Card class="max-w-4xl">
				<CardContent class="p-6">
					<p>No platform app data available</p>
				</CardContent>
			</Card>
		{/if}
	</section>
</div>

<!-- Delete Confirmation Dialog -->
<Dialog.Root open={showDeleteDialog} onOpenChange={(open) => showDeleteDialog = open}>
	<Dialog.Content class="sm:max-w-[400px]">
		<Dialog.Header>
			<Dialog.Title>Delete Platform App</Dialog.Title>
			<Dialog.Description>
				Are you sure you want to delete "{platformApp?.name}"? This action cannot be undone and will permanently remove this platform app and revoke its access token.
			</Dialog.Description>
		</Dialog.Header>
		<Dialog.Footer class="pt-4">
			<Button variant="outline" onclick={cancelDelete} disabled={deleting}>
				Cancel
			</Button>
			<Button variant="destructive" onclick={confirmDelete} disabled={deleting}>
				{deleting ? 'Deleting...' : 'Delete Platform App'}
			</Button>
		</Dialog.Footer>
	</Dialog.Content>
</Dialog.Root>
