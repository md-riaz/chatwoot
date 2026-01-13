<script lang="ts">
import { goto } from '$app/navigation';
import { page } from '$app/stores';
import { api } from '$lib/api/superAdmin';
import { Button } from '$lib/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
import { Input } from '$lib/components/ui/input';
import { Label } from '$lib/components/ui/label';
import { ArrowLeft } from 'lucide-svelte';
import { onMount } from 'svelte';
import { toast } from 'svelte-sonner';
import type { PlatformApp } from '$lib/api/superAdmin';

const platformAppId = $page.params.id;

let loading = $state(true);
let saving = $state(false);
let platformApp: PlatformApp | null = $state(null);
let errors = $state<Record<string, string>>({});

let formData = $state({
  name: ''
});

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
    formData = {
      name: platformApp.name || ''
    };
  } catch (error: any) {
    toast.error('Failed to load platform app: ' + (error.message || 'Unknown error'));
    goto('/app/super_admin/platform-apps');
  } finally {
    loading = false;
  }
}

async function handleSubmit(e: SubmitEvent) {
  e.preventDefault();
  if (!platformAppId) return;
  
  try {
    saving = true;
    errors = {};
    
    await api.platformApps.update(platformAppId, formData);
    toast.success('Platform app updated successfully');
    goto(`/app/super_admin/platform-apps/${platformAppId}`);
  } catch (error: any) {
    if (error.status === 422) {
      errors = error.errors || {};
    }
    toast.error('Failed to update platform app: ' + (error.message || 'Unknown error'));
  } finally {
    saving = false;
  }
}
</script>

<svelte:head>
	<title>Edit Platform App - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center">
			<Button variant="ghost" size="sm" onclick={() => goto(`/app/super_admin/platform-apps/${platformAppId}`)}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div class="ml-4">
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : 'Edit Platform App'}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					<a href="/app/super_admin/platform-apps" class="hover:text-iris-9">Platform Apps</a>
					/ <a href="/app/super_admin/platform-apps/{platformAppId}" class="hover:text-iris-9">{platformApp?.name || 'Loading...'}</a>
					/ Edit
				</p>
			</div>
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
					</div>
				</CardContent>
			</Card>
		{/if}

		{#if !loading && platformApp}
			<div class="max-w-4xl">
				<Card>
					<CardHeader>
						<CardTitle>Edit Platform App</CardTitle>
						<CardDescription>Update the platform app information</CardDescription>
					</CardHeader>
					<CardContent>
						<form onsubmit={(e: SubmitEvent) => handleSubmit(e)}>
							<div class="space-y-6">
								<div class="space-y-2">
									<Label for="name">Name *</Label>
									<Input
										id="name"
										bind:value={formData.name}
										placeholder="Enter platform app name"
										required
										class={errors.name ? 'border-red-500' : ''}
									/>
									{#if errors.name}
										<p class="text-sm text-red-600">{errors.name}</p>
									{/if}
								</div>
								
								<div class="flex gap-3 pt-4 border-t">
									<Button type="submit" disabled={saving}>
										{saving ? 'Saving...' : 'Save Changes'}
									</Button>
									<Button variant="outline" onclick={() => goto(`/app/super_admin/platform-apps/${platformAppId}`)}>
										Cancel
									</Button>
								</div>
							</div>
						</form>
					</CardContent>
				</Card>
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