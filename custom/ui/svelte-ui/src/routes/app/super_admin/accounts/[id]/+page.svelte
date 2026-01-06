<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import ConfirmDialog from '$lib/components/ConfirmDialog.svelte';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { ArrowLeft, Database, RefreshCw, Save, Trash2 } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	
	let loading = $state(true);
	let submitting = $state(false);
	let accountId: number = Number($page.params.id);

	let formData = $state({
		name: '',
		status: 'active',
		locale: 'en',
		domain: '',
		auto_resolve_duration: '',
		selected_feature_flags: [] as string[],
		all_features: {} as Record<string, boolean>
	});

	let errors = $state<Record<string, string>>({});
	
	// Confirm dialog state
	let showDeleteConfirm = $state(false);
	let showSeedConfirm = $state(false);
	let showResetCacheConfirm = $state(false);
	let actionLoading = $state(false);
	
	async function loadAccount() {
		loading = true;
		try {
			   const account = await superAdminApi.getAccount(accountId);
			   formData = {
				   name: account.name || '',
				   status: account.status || 'active',
				   locale: account.locale || 'en',
				   domain: account.domain || '',
				   auto_resolve_duration: String(account.auto_resolve_duration ?? ''),
				   selected_feature_flags: account.selected_feature_flags || [],
				   all_features: account.all_features || {}
			   };
		} catch (error: any) {
			toast.error(error.message || 'Failed to load account');
			goto('/app/super_admin/accounts');
		} finally {
			loading = false;
		}
	}
	
	async function handleSubmit(event: Event) {
	   event.preventDefault();
	   errors = {};
   
	   if (!formData.name) {
		   errors.name = 'Name is required';
		   return;
	   }
   
	   submitting = true;
		try {
			   await superAdminApi.updateAccount(accountId, {
				   ...formData,
				   status: formData.status as 'active' | 'suspended' | undefined,
				   auto_resolve_duration: formData.auto_resolve_duration ? Number(formData.auto_resolve_duration) : undefined,
				   selected_feature_flags: formData.selected_feature_flags
			   });
			toast.success('Account updated successfully');
			goto('/app/super_admin/accounts');
		} catch (error: any) {
			if (error.response?.errors) {
				errors = error.response.errors;
			} else {
				toast.error(error.message || 'Failed to update account');
			}
		} finally {
			submitting = false;
		}
	}
	
	function openDeleteConfirm() {
		showDeleteConfirm = true;
	}
	
	function openSeedConfirm() {
		showSeedConfirm = true;
	}
	
	function openResetCacheConfirm() {
		showResetCacheConfirm = true;
	}
	
	   async function handleDelete() {
		   try {
			   await superAdminApi.deleteAccount(accountId);
			   toast.success('Account deleted successfully');
			   goto('/app/super_admin/accounts');
		   } catch (err: any) {
			   toast.error(err.message || 'Failed to delete account');
			   throw err; // Re-throw to keep dialog open on error
		   }
	   }
	   
	   async function handleSeedAccount() {
		   actionLoading = true;
		   try {
			   const response = await superAdminApi.seedAccount(accountId);
			   toast.success(response.message || 'Account seeding triggered');
		   } catch (err: any) {
			   toast.error(err.message || 'Failed to seed account');
			   throw err; // Re-throw to keep dialog open on error
		   } finally {
			   actionLoading = false;
		   }
	   }
	   
	   async function handleResetCache() {
		   actionLoading = true;
		   try {
			   const response = await superAdminApi.resetAccountCache(accountId);
			   toast.success(response.message || 'Cache keys cleared');
		   } catch (err: any) {
			   toast.error(err.message || 'Failed to reset cache');
			   throw err; // Re-throw to keep dialog open on error
		   } finally {
			   actionLoading = false;
		   }
	   }
	
	   function handleFeaturesChange(features: string[]) {
		   formData.selected_feature_flags = features;
	   }
	   
	   onMount(() => {
		   loadAccount();
	   });
</script>

<svelte:head>
	<title>Edit Account - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between" role="banner">
		<div class="flex items-center space-x-4">
			<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/accounts')}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div>
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : formData.name || 'Edit Account'}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					Account ID: {accountId}
				</p>
			</div>
		</div>
		<div class="flex items-center space-x-2">
			<Button variant="outline" onclick={openSeedConfirm} disabled={loading || actionLoading}>
				<Database class="h-4 w-4 mr-2" />
				Seed Account
			</Button>
			<Button variant="outline" onclick={openResetCacheConfirm} disabled={loading || actionLoading}>
				<RefreshCw class="h-4 w-4 mr-2" />
				Reset Cache
			</Button>
			<Button variant="destructive" onclick={openDeleteConfirm}>
				<Trash2 class="h-4 w-4 mr-2" />
				Delete
			</Button>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		   <Card class="max-w-2xl">
			   <CardHeader>
				   <CardTitle>Account Details</CardTitle>
				   <CardDescription>Update account information and settings</CardDescription>
			   </CardHeader>
			   <CardContent>
				{#if loading}
					<div class="text-center py-8">
						<p style="color: rgb(var(--slate-10));">Loading account...</p>
					</div>
				{:else}
					   <form onsubmit={handleSubmit} class="space-y-4">
						<div class="space-y-2">
							<Label for="name">Account Name *</Label>
							<Input
								id="name"
								type="text"
								bind:value={formData.name}
								disabled={submitting}
								class={errors.name ? 'border-destructive' : ''}
							/>
							{#if errors.name}
								<p class="text-sm text-destructive">{errors.name}</p>
							{/if}
						</div>
						
						<div class="space-y-2">
							<Label for="status">Status</Label>
							<select
								id="status"
								bind:value={formData.status}
								disabled={submitting}
								class="flex h-10 w-full rounded-md border px-3 py-2 text-sm"
								style="border-color: rgb(var(--slate-6));"
							>
								<option value="active">Active</option>
								<option value="suspended">Suspended</option>
							</select>
						</div>
						
						<div class="space-y-2">
							<Label for="locale">Locale</Label>
							<Input
								id="locale"
								type="text"
								bind:value={formData.locale}
								placeholder="en"
								disabled={submitting}
							/>
						</div>
						
						<div class="space-y-2">
							<Label for="domain">Domain</Label>
							<Input
								id="domain"
								type="text"
								bind:value={formData.domain}
								placeholder="example.com"
								disabled={submitting}
							/>
						</div>
						
						<div class="space-y-2">
							<Label for="auto_resolve_duration">Auto Resolve Duration (days)</Label>
							<Input
								id="auto_resolve_duration"
								type="number"
								bind:value={formData.auto_resolve_duration}
								placeholder="30"
								disabled={submitting}
							/>
						</div>
						
						<div class="flex items-center space-x-2 pt-4">
							<Button type="submit" disabled={submitting}>
								<Save class="h-4 w-4 mr-2" />
								{submitting ? 'Saving...' : 'Save Changes'}
							</Button>
							<Button type="button" variant="outline" onclick={() => goto('/app/super_admin/accounts')}>
								Cancel
							</Button>
						</div>
					</form>
				{/if}
			   </CardContent>
		   </Card>
		   
		   {#if !loading}
			   <div class="mt-6">
				   <FeatureFlagManager
					   selectedFeatures={formData.selected_feature_flags}
					   allFeatures={formData.all_features}
					   onFeaturesChange={handleFeaturesChange}
					   disabled={submitting}
				   />
			   </div>
		   {/if}
	</section>
</div>

<!-- Confirm Dialogs -->
<ConfirmDialog
	bind:open={showDeleteConfirm}
	title="Delete Account"
	description="Are you sure you want to delete this account? This action cannot be undone and will permanently remove all associated data."
	confirmText="Delete Account"
	variant="destructive"
	onConfirm={handleDelete}
/>

<ConfirmDialog
	bind:open={showSeedConfirm}
	title="Seed Account"
	description="This will populate the account with demo data including sample conversations, contacts, and inboxes. This action may take a few minutes to complete."
	confirmText="Seed Account"
	onConfirm={handleSeedAccount}
/>

<ConfirmDialog
	bind:open={showResetCacheConfirm}
	title="Reset Cache"
	description="This will clear all cached data for this account including settings and feature flags. The cache will be rebuilt automatically as needed."
	confirmText="Reset Cache"
	onConfirm={handleResetCache}
/>
