<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import ConfirmDialog from '$lib/components/ConfirmDialog.svelte';
	import FeatureFlagManager from '$lib/components/FeatureFlagManager.svelte';
	import UserAssignmentForm from '$lib/components/UserAssignmentForm.svelte';
	import { Button } from '$lib/components/ui/button';
	import { ArrowLeft, Database, Edit, RefreshCw, Trash2 } from 'lucide-svelte';
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
		supportEmail: '',
		autoResolveDuration: '',
		selectedFeatureFlags: [] as string[],
		allFeatures: {} as Record<string, {
			available: boolean;
			display_name?: string;
			displayName?: string;
			enabled: boolean;
			premium: boolean;
			help_url?: string;
			helpUrl?: string;
		}>,
		settings: {} as Record<string, any>,
		limits: {} as Record<string, any>,
		customAttributes: {} as Record<string, any>,
		usersCount: 0,
		inboxesCount: 0,
		conversationsCount: 0,
		contactsCount: 0,
		createdAt: '',
		updatedAt: ''
	});

	let errors = $state<Record<string, string>>({});
	
	// Account users data
	let accountUsers = $state<any[]>([]);
	
	// Confirm dialog state
	let showDeleteConfirm = $state(false);
	let showSeedConfirm = $state(false);
	let showResetCacheConfirm = $state(false);
	let showRemoveUserConfirm = $state(false);
	let actionLoading = $state(false);
	let selectedAccountUser = $state<any>(null);
	
	async function loadAccount() {
		loading = true;
		try {
			   const account = await superAdminApi.getAccount(accountId);
			   
			   formData = {
				   name: account.name || '',
				   status: account.status || 'active',
				   locale: account.locale || 'en',
				   domain: account.domain || '',
				   supportEmail: account.supportEmail || '',
				   autoResolveDuration: String(account.autoResolveDuration ?? ''),
				   selectedFeatureFlags: account.selectedFeatureFlags || [],
				   allFeatures: account.allFeatures || {},
				   settings: account.settings || {},
				   limits: account.limits || {},
				   customAttributes: account.customAttributes || {},
				   usersCount: account.usersCount || 0,
				   inboxesCount: account.inboxesCount || 0,
				   conversationsCount: account.conversationsCount || 0,
				   contactsCount: account.contactsCount || 0,
				   createdAt: account.createdAt || '',
				   updatedAt: account.updatedAt || ''
			   };
			   
			   // Account users come from the account data (like Rails HasMany relationship)
			   accountUsers = account.accountUsers || [];
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
				   name: formData.name,
				   status: formData.status as 'active' | 'suspended' | undefined,
				   locale: formData.locale,
				   domain: formData.domain,
				   supportEmail: formData.supportEmail,
				   autoResolveDuration: formData.autoResolveDuration ? Number(formData.autoResolveDuration) : undefined,
				   selectedFeatureFlags: formData.selectedFeatureFlags,
				   settings: formData.settings,
				   limits: formData.limits,
				   customAttributes: formData.customAttributes
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
	
	function openRemoveUserConfirm(accountUser: any) {
		selectedAccountUser = accountUser;
		showRemoveUserConfirm = true;
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
	   
	   async function handleRemoveUser() {
		   if (!selectedAccountUser) return;
		   
		   // Check if this is the last administrator
		   if (selectedAccountUser.role === 1) { // Administrator role
			   const adminCount = accountUsers.filter(au => au.role === 1).length;
			   if (adminCount <= 1) {
				   toast.error('Cannot remove the last administrator from the account');
				   return;
			   }
		   }
		   
		   actionLoading = true;
		   try {
			   const response = await superAdminApi.deleteAccountUser(selectedAccountUser.id);
			   toast.success(response.message || 'User removed from account successfully');
			   
			   // Remove the user from the local state
			   accountUsers = accountUsers.filter(au => au.id !== selectedAccountUser.id);
			   
			   // Update the users count
			   formData.usersCount = Math.max(0, formData.usersCount - 1);
		   } catch (err: any) {
			   toast.error(err.message || 'Failed to remove user from account');
			   throw err; // Re-throw to keep dialog open on error
		   } finally {
			   actionLoading = false;
			   selectedAccountUser = null;
		   }
	   }
	
	   function handleFeaturesChange(features: string[] | Record<string, boolean>) {
		   // Convert Record to string[] if needed
		   if (Array.isArray(features)) {
			   formData.selectedFeatureFlags = features;
		   } else {
			   // Convert Record<string, boolean> to string[] (keys where value is true)
			   formData.selectedFeatureFlags = Object.keys(features).filter(key => features[key]);
		   }
	   }

	   // Handle user assignment
	   function handleUserAssigned(accountUser: any) {
		   // Add the new account user to the list
		   accountUsers = [...accountUsers, accountUser];
		   
		   // Update the users count
		   formData.usersCount = formData.usersCount + 1;
		   
		   toast.success('User assigned to account successfully');
	   }

	   // Get existing user IDs to filter from search
	   function getExistingUserIds(): number[] {
		   return accountUsers.map(au => au.user_id || au.userId).filter(Boolean);
	   }
	   
	   onMount(() => {
		   loadAccount();
	   });
</script>

<svelte:head>
	<title>Account Details - Super Admin - Chatwoot</title>
</svelte:head>

<div class="h-full flex flex-col bg-background">
	<!-- Header matching other super admin pages -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div class="flex items-center space-x-4">
			<Button variant="ghost" size="sm" onclick={() => goto('/app/super_admin/accounts')}>
				<ArrowLeft class="h-4 w-4" />
			</Button>
			<div>
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : `#${accountId} ${formData.name}` || 'Account Details'}
				</h1>
				<p class="text-sm mt-1 text-muted-foreground">
					Account Details
				</p>
			</div>
		</div>
		<div class="flex items-center space-x-2">
			<Button variant="outline" onclick={() => goto(`/app/super_admin/accounts/${accountId}/edit`)} disabled={loading}>
				<Edit class="h-4 w-4 mr-2" />
				Edit
			</Button>
		</div>
	</header>

	<!-- Main Content -->
	<main class="flex-1 overflow-auto bg-background p-8">
		{#if loading}
			<div class="bg-card rounded-lg shadow-xs p-8">
				<div class="animate-pulse space-y-4">
					<div class="h-4 bg-muted rounded w-1/4"></div>
					<div class="h-4 bg-muted rounded w-1/2"></div>
					<div class="h-4 bg-muted rounded w-1/3"></div>
				</div>
			</div>
		{:else}
			<!-- Basic Information Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Basic Information</h2>
				</div>
				<div class="p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">ID</span>
							<div class="text-sm text-foreground">{accountId}</div>
						</div>
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">Name</span>
							<div class="text-sm text-foreground">{formData.name}</div>
						</div>
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">Status</span>
							<div class="text-sm">
								<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {formData.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'}">
									{formData.status === 'active' ? 'Active' : 'Suspended'}
								</span>
							</div>
						</div>
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">Locale</span>
							<div class="text-sm text-foreground">{formData.locale?.toUpperCase() || 'EN'}</div>
						</div>
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">Created At</span>
							<div class="text-sm text-foreground">{formData.createdAt ? new Date(formData.createdAt).toLocaleString() : 'Unknown'}</div>
						</div>
						<div>
							<span class="block text-sm font-medium text-muted-foreground mb-1">Updated At</span>
							<div class="text-sm text-foreground">{formData.updatedAt ? new Date(formData.updatedAt).toLocaleString() : 'Unknown'}</div>
						</div>
					</div>
				</div>
			</section>

			<!-- Account Limits Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Account Limits</h2>
					<p class="text-sm text-muted-foreground mt-1">Usage limits configured for this account</p>
				</div>
				<div class="p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
						<div class="text-center p-4 border border-border rounded-lg">
							<div class="text-2xl font-semibold text-foreground">
								{formData.limits?.agents || 'Unlimited'}
							</div>
							<div class="text-sm text-muted-foreground">Agent Limit</div>
						</div>
						<div class="text-center p-4 border border-border rounded-lg">
							<div class="text-2xl font-semibold text-foreground">
								{formData.limits?.inboxes || 'Unlimited'}
							</div>
							<div class="text-sm text-muted-foreground">Inbox Limit</div>
						</div>
						{#if formData.limits && Object.keys(formData.limits).some(key => !['agents', 'inboxes'].includes(key))}
							{#each Object.entries(formData.limits).filter(([key]) => !['agents', 'inboxes'].includes(key)) as [key, value]}
								<div class="text-center p-4 border border-border rounded-lg">
									<div class="text-2xl font-semibold text-foreground">{value || 'Unlimited'}</div>
									<div class="text-sm text-muted-foreground capitalize">{key.replace(/_/g, ' ')}</div>
								</div>
							{/each}
						{/if}
					</div>
				</div>
			</section>

			<!-- Statistics Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Statistics</h2>
				</div>
				<div class="p-6">
					<div class="grid grid-cols-2 md:grid-cols-4 gap-6">
						<div class="text-center p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-border">
							<div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{formData.usersCount}</div>
							<div class="text-sm text-blue-700 dark:text-blue-300 font-medium">Users</div>
						</div>
						<div class="text-center p-4 bg-green-50 dark:bg-green-950/20 rounded-lg border border-border">
							<div class="text-2xl font-bold text-green-600 dark:text-green-400">{formData.inboxesCount}</div>
							<div class="text-sm text-green-700 dark:text-green-300 font-medium">Inboxes</div>
						</div>
						<div class="text-center p-4 bg-purple-50 dark:bg-purple-950/20 rounded-lg border border-border">
							<div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{formData.conversationsCount}</div>
							<div class="text-sm text-purple-700 dark:text-purple-300 font-medium">Conversations</div>
						</div>
						<div class="text-center p-4 bg-orange-50 dark:bg-orange-950/20 rounded-lg border border-border">
							<div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{formData.contactsCount}</div>
							<div class="text-sm text-orange-700 dark:text-orange-300 font-medium">Contacts</div>
						</div>
					</div>
				</div>
			</section>

			<!-- Account Users Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Account Users</h2>
					<p class="text-sm text-muted-foreground mt-1">Users with access to this account</p>
				</div>
				<div class="p-6 space-y-6">
					<!-- User Assignment Form -->
					<UserAssignmentForm 
						{accountId} 
						onUserAssigned={handleUserAssigned}
						existingUserIds={getExistingUserIds()}
					/>

					<!-- Existing Users Table -->
					{#if accountUsers && accountUsers.length > 0}
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead>
									<tr class="border-b border-border">
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">User</th>
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">Email</th>
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">Role</th>
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">Inviter</th>
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">Joined</th>
										<th class="text-left py-2 text-sm font-medium text-muted-foreground">Actions</th>
									</tr>
								</thead>
								<tbody>
									{#each accountUsers as accountUser}
										{@const isLastAdmin = accountUser.role === 1 && accountUsers.filter(au => au.role === 1).length <= 1}
										<tr class="border-b border-border">
											<td class="py-3 text-sm text-foreground">{accountUser.user?.displayName || accountUser.user?.name || 'Unknown User'}</td>
											<td class="py-3 text-sm text-muted-foreground">{accountUser.user?.email || '-'}</td>
											<td class="py-3">
												<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {accountUser.role === 1 ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'}">
													{accountUser.roleName || (accountUser.role === 1 ? 'Administrator' : 'Agent')}
												</span>
											</td>
											<td class="py-3 text-sm text-muted-foreground">{accountUser.inviter?.name || '-'}</td>
											<td class="py-3 text-sm text-muted-foreground">{accountUser.createdAt ? new Date(accountUser.createdAt).toLocaleDateString() : '-'}</td>
											<td class="py-3">
												<div class="flex items-center space-x-2">
													<Button variant="outline" size="sm" onclick={() => goto(`/app/super_admin/users/${accountUser.userId}`)}>
														View User
													</Button>
													<Button 
														variant="outline" 
														size="sm" 
														onclick={() => openRemoveUserConfirm(accountUser)}
														disabled={isLastAdmin}
														class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950/20 disabled:opacity-50 disabled:cursor-not-allowed"
														title={isLastAdmin ? 'Cannot remove the last administrator' : 'Remove user from account'}
													>
														<Trash2 class="h-3 w-3 mr-1" />
														Remove
													</Button>
												</div>
											</td>
										</tr>
									{/each}
								</tbody>
							</table>
						</div>
					{:else}
						<div class="text-sm text-muted-foreground">No account users found</div>
					{/if}
				</div>
			</section>

			<!-- Configuration Section -->
			{#if formData.domain || formData.supportEmail || formData.autoResolveDuration}
				<section class="bg-card rounded-lg shadow-xs mb-6">
					<div class="px-6 py-4 border-b border-border">
						<h2 class="text-lg font-medium text-foreground">Configuration</h2>
					</div>
					<div class="p-6">
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
							{#if formData.domain}
								<div>
									<span class="block text-sm font-medium text-muted-foreground mb-1">Domain</span>
									<div class="text-sm text-foreground">{formData.domain}</div>
								</div>
							{/if}
							{#if formData.supportEmail}
								<div>
									<span class="block text-sm font-medium text-muted-foreground mb-1">Support Email</span>
									<div class="text-sm text-foreground">{formData.supportEmail}</div>
								</div>
							{/if}
							{#if formData.autoResolveDuration}
								<div>
									<span class="block text-sm font-medium text-muted-foreground mb-1">Auto Resolve Duration</span>
									<div class="text-sm text-foreground">{formData.autoResolveDuration} days</div>
								</div>
							{/if}
						</div>
					</div>
				</section>
			{/if}

			<!-- Custom Attributes Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Custom Attributes</h2>
				</div>
				<div class="p-6">
					{#if formData.customAttributes && Object.keys(formData.customAttributes).length > 0}
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							{#each Object.entries(formData.customAttributes) as [key, value]}
								<div>
									<span class="block text-sm font-medium text-muted-foreground mb-1">{key}</span>
									<div class="text-sm text-foreground">{typeof value === 'object' ? JSON.stringify(value) : value}</div>
								</div>
							{/each}
						</div>
					{:else}
						<div class="text-sm text-muted-foreground">{JSON.stringify({})}</div>
					{/if}
				</div>
			</section>

			<!-- Feature Flags Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Feature Flags</h2>
					<p class="text-sm text-muted-foreground mt-1">Enabled features for this account</p>
				</div>
				<div class="p-6">
					<FeatureFlagManager
						selectedFeatures={formData.selectedFeatureFlags}
						allFeatures={formData.allFeatures}
						onFeaturesChange={handleFeaturesChange}
						disabled={true}
					/>
				</div>
			</section>

			<!-- Account Actions Section -->
			<section class="bg-card rounded-lg shadow-xs mb-6">
				<div class="px-6 py-4 border-b border-border">
					<h2 class="text-lg font-medium text-foreground">Account Actions</h2>
				</div>
				<div class="p-6 space-y-4">
					<!-- Seed Data Action -->
					<div class="border border-border rounded-lg p-4">
						<div class="flex items-center justify-between">
							<div>
								<h3 class="text-sm font-medium text-foreground">Generate Seed Data</h3>
								<p class="text-sm text-muted-foreground mt-1">Click the button to generate seed data into this account for demos.</p>
								<p class="text-sm text-red-600 dark:text-red-400 mt-1">Note: This will clear all the existing data in this account.</p>
							</div>
							<Button variant="outline" onclick={openSeedConfirm} disabled={actionLoading}>
								<Database class="h-4 w-4 mr-2" />
								Generate Seed Data
							</Button>
						</div>
					</div>

					<!-- Reset Cache Action -->
					<div class="border border-border rounded-lg p-4">
						<div class="flex items-center justify-between">
							<div>
								<h3 class="text-sm font-medium text-foreground">Reset Frontend Cache</h3>
								<p class="text-sm text-muted-foreground mt-1">This will clear the IndexedDB cache keys from redis.</p>
								<p class="text-sm text-muted-foreground">The next load will fetch the data from backend.</p>
							</div>
							<Button variant="outline" onclick={openResetCacheConfirm} disabled={actionLoading}>
								<RefreshCw class="h-4 w-4 mr-2" />
								Reset Frontend Cache
							</Button>
						</div>
					</div>

					<!-- Delete Account Action -->
					<div class="border border-red-200 dark:border-red-800 rounded-lg p-4 bg-red-50 dark:bg-red-950/20">
						<div class="flex items-center justify-between">
							<div>
								<h3 class="text-sm font-medium text-red-900 dark:text-red-100">Delete Account</h3>
								<p class="text-sm text-red-700 dark:text-red-300 mt-1">Permanently delete this account and all associated data. This action cannot be undone.</p>
							</div>
							<Button variant="destructive" onclick={openDeleteConfirm}>
								<Trash2 class="h-4 w-4 mr-2" />
								Delete Account
							</Button>
						</div>
					</div>
				</div>
			</section>
		{/if}
	</main>
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

<ConfirmDialog
	bind:open={showRemoveUserConfirm}
	title="Remove User from Account"
	description={selectedAccountUser ? `Are you sure you want to remove "${selectedAccountUser.user?.name || selectedAccountUser.user?.email}" from this account? This action cannot be undone.` : 'Are you sure you want to remove this user from the account?'}
	confirmText="Remove User"
	variant="destructive"
	onConfirm={handleRemoveUser}
/>