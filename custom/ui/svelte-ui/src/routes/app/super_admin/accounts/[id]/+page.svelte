<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import ConfirmDialog from '$lib/components/ConfirmDialog.svelte';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { ArrowLeft, Save, Trash2 } from 'lucide-svelte';
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
		auto_resolve_duration: ''
	});

	let errors = $state<Record<string, string>>({});
	
	// Confirm dialog state
	let showDeleteConfirm = $state(false);
	
	async function loadAccount() {
		loading = true;
		try {
			   const account = await superAdminApi.getAccount(accountId);
			   formData = {
				   name: account.name || '',
				   status: account.status || 'active',
				   locale: account.locale || 'en',
				   domain: account.domain || '',
				   auto_resolve_duration: String(account.auto_resolve_duration ?? '')
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
				   auto_resolve_duration: formData.auto_resolve_duration ? Number(formData.auto_resolve_duration) : undefined
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
	
	   // Remove handleSeedData and handleResetCache as these methods do not exist
	
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
			   <!-- Removed Reset Cache and Seed Data buttons -->
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

<!-- Removed Seed Data ConfirmDialog -->
