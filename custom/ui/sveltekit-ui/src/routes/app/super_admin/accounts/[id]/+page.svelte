<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/client';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { Card } from '$lib/components/ui/card';
	import { Select } from '$lib/components/ui/select';
	import ConfirmDialog from '$lib/components/ConfirmDialog.svelte';
	import { toast } from 'svelte-sonner';
	import { ArrowLeft, Save, Trash2, Database, RefreshCw } from 'lucide-svelte';
	
	let loading = true;
	let submitting = false;
	let accountId = $page.params.id;
	
	let formData = {
		name: '',
		status: 'active',
		locale: 'en',
		domain: '',
		auto_resolve_duration: ''
	};
	
	let errors: Record<string, string> = {};
	
	// Confirm dialog state
	let showDeleteConfirm = $state(false);
	let showSeedConfirm = $state(false);
	
	async function loadAccount() {
		loading = true;
		try {
			const account = await superAdminApi.getAccount(accountId);
			formData = {
				name: account.name || '',
				status: account.status || 'active',
				locale: account.locale || 'en',
				domain: account.domain || '',
				auto_resolve_duration: account.auto_resolve_duration || ''
			};
		} catch (error: any) {
			toast.error(error.message || 'Failed to load account');
			goto('/app/super_admin/accounts');
		} finally {
			loading = false;
		}
	}
	
	async function handleSubmit() {
		errors = {};
		
		if (!formData.name) {
			errors.name = 'Name is required';
			return;
		}
		
		submitting = true;
		try {
			await superAdminApi.updateAccount(accountId, formData);
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
		} catch (error: any) {
			toast.error(error.message || 'Failed to delete account');
			throw error; // Re-throw to keep dialog open on error
		}
	}
	
	function openSeedConfirm() {
		showSeedConfirm = true;
	}
	
	async function handleSeedData() {
		try {
			await superAdminApi.seedAccountData(accountId);
			toast.success('Account data seeded successfully');
		} catch (error: any) {
			toast.error(error.message || 'Failed to seed account data');
			throw error; // Re-throw to keep dialog open on error
		}
	}
	
	async function handleResetCache() {
		try {
			await superAdminApi.resetAccountCache(accountId);
			toast.success('Account cache reset successfully');
		} catch (error: any) {
			toast.error(error.message || 'Failed to reset cache');
		}
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
	<header class="px-8 py-6 border-b bg-white dark:bg-slate-1 flex items-center justify-between" style="border-color: rgb(var(--slate-6));">
		<div class="flex items-center space-x-4">
			<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/accounts')}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div>
				<h1 class="text-2xl font-semibold" style="color: rgb(var(--slate-12));">
					{loading ? 'Loading...' : formData.name || 'Edit Account'}
				</h1>
				<p class="text-sm mt-1" style="color: rgb(var(--slate-11));">
					Account ID: {accountId}
				</p>
			</div>
		</div>
		<div class="flex items-center space-x-2">
			<Button variant="outline" onclick={handleResetCache}>
				<RefreshCw class="h-4 w-4 mr-2" />
				Reset Cache
			</Button>
			<Button variant="outline" onclick={openSeedConfirm}>
				<Database class="h-4 w-4 mr-2" />
				Seed Data
			</Button>
			<Button variant="destructive" onclick={openDeleteConfirm}>
				<Trash2 class="h-4 w-4 mr-2" />
				Delete
			</Button>
		</div>
	</header>

	<!-- Body -->
	<section class="p-8">
		<Card.Root class="max-w-2xl">
			<Card.Header>
				<Card.Title>Account Details</Card.Title>
				<Card.Description>Update account information and settings</Card.Description>
			</Card.Header>
			<Card.Content>
				{#if loading}
					<div class="text-center py-8">
						<p style="color: rgb(var(--slate-10));">Loading account...</p>
					</div>
				{:else}
					<form on:submit|preventDefault={handleSubmit} class="space-y-4">
						<div class="space-y-2">
							<Label for="name">Account Name *</Label>
							<Input
								id="name"
								type="text"
								bind:value={formData.name}
								placeholder="Acme Inc."
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
			</Card.Content>
		</Card.Root>
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
	title="Seed Account Data"
	description="Are you sure you want to seed data for this account? This will populate the account with sample data."
	confirmText="Seed Data"
	onConfirm={handleSeedData}
/>
