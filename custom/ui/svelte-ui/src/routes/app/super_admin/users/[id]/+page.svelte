<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { superAdminApi } from '$lib/api/superAdmin';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Label from '$lib/components/ui/label/label.svelte';
	import Select from '$lib/components/ui/select/select-native.svelte';
	import Skeleton from '$lib/components/ui/skeleton/skeleton.svelte';
	import { ChevronLeft, Lock, Save, Trash2, Unlock, Upload, X } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	let userId: string = $page.params.id || '';
	let user: any = null;
	let loading = true;
	let saving = false;
	let avatarFile: File | null = null;
	let avatarPreview: string | null = null;

	// Account users data
	let accountUsers = $state<any[]>([]);
	let showRemoveAccountConfirm = $state(false);
	let selectedAccountUser = $state<any>(null);
	let actionLoading = $state(false);

	let formData = {
		name: '',
		display_name: '',
		email: '',
		role: 'agent',
		password: '',
		confirmed_at: ''
	};

	async function loadUser() {
		if (!userId) return;
		
		loading = true;
		try {
			user = await superAdminApi.getUser(parseInt(userId));
			formData = {
				name: user.name || '',
				display_name: user.displayName || '', // Use camelCase from API
				email: user.email || '',
				role: user.role || 'agent',
				password: '',
				confirmed_at: user.confirmedAt ? new Date(user.confirmedAt).toISOString().slice(0, 16) : '' // Convert to datetime-local format
			};
			if (user.avatarUrl) {
				avatarPreview = user.avatarUrl;
			}

			// Load account users (like Rails HasMany relationship)
			accountUsers = user.accountUsers || [];
		} catch (error) {
			toast.error('Failed to load user');
			console.error(error);
		} finally {
			loading = false;
		}
	}

	async function handleSave() {
		if (!userId) return;
		
		saving = true;
		try {
			// Prepare update data with Rails parity format
			const updateData = {
				name: formData.name,
				display_name: formData.display_name,
				email: formData.email,
				password: formData.password || undefined,
				// Rails parity: Use confirmed_at field as datetime (Field::DateTime)
				confirmed_at: formData.confirmed_at || null
			};

			// Remove undefined values
			Object.keys(updateData).forEach(key => {
				if (updateData[key] === undefined) {
					delete updateData[key];
				}
			});

			// Update user info
			await superAdminApi.updateUser(parseInt(userId), updateData);

			// Upload avatar if selected
			if (avatarFile) {
				await superAdminApi.uploadUserAvatar(parseInt(userId), avatarFile);
			}

			toast.success('User updated successfully');
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to update user');
			console.error(error);
		} finally {
			saving = false;
		}
	}

	async function handleDelete() {
		if (!userId || !confirm('Are you sure you want to delete this user?')) return;

		try {
			await superAdminApi.deleteUser(parseInt(userId));
			toast.success('User deleted successfully');
			goto('/app/super_admin/users');
		} catch (error: any) {
			toast.error(error.message || 'Failed to delete user');
			console.error(error);
		}
	}

	async function handleToggleLock() {
		if (!userId) return;
		
		try {
			if (isUserLocked()) {
				await superAdminApi.unlockUser(parseInt(userId));
				toast.success('User unlocked successfully');
			} else {
				await superAdminApi.lockUser(parseInt(userId));
				toast.success('User locked successfully');
			}
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to update user status');
			console.error(error);
		}
	}

	async function handleDeleteAvatar() {
		if (!userId || !confirm('Are you sure you want to delete this avatar?')) return;

		try {
			await superAdminApi.deleteUserAvatar(parseInt(userId));
			avatarPreview = null;
			avatarFile = null;
			toast.success('Avatar deleted successfully');
			await loadUser();
		} catch (error: any) {
			toast.error(error.message || 'Failed to delete avatar');
			console.error(error);
		}
	}
	
	function isUserConfirmed(): boolean {
		return !!user?.confirmed || !!user?.emailVerifiedAt; // Check both possible field names
	}
	
	function isUserLocked(): boolean {
		return user?.locked === true || user?.customAttributes?.locked === true; // Check both possible field names
	}

	function handleFileSelect(e: Event) {
		const target = e.target as HTMLInputElement;
		const file = target.files?.[0];
		if (file) {
			avatarFile = file;
			const reader = new FileReader();
			reader.onload = (e) => {
				avatarPreview = e.target?.result as string;
			};
			reader.readAsDataURL(file);
		}
	}

	// Handle account assignment
	function handleAccountAssigned(accountUser: any) {
		// Add the new account user to the list
		accountUsers = [...accountUsers, accountUser];
		toast.success('User assigned to account successfully');
	}

	// Get existing account IDs to filter from search
	function getExistingAccountIds(): number[] {
		return accountUsers.map(au => au.account_id || au.accountId).filter(Boolean);
	}

	// Handle remove user from account
	function openRemoveAccountConfirm(accountUser: any) {
		selectedAccountUser = accountUser;
		showRemoveAccountConfirm = true;
	}

	async function handleRemoveFromAccount() {
		if (!selectedAccountUser) return;
		
		actionLoading = true;
		try {
			const response = await superAdminApi.deleteAccountUser(selectedAccountUser.id);
			toast.success(response.message || 'User removed from account successfully');
			
			// Remove the account user from the local state
			accountUsers = accountUsers.filter(au => au.id !== selectedAccountUser.id);
		} catch (err: any) {
			toast.error(err.message || 'Failed to remove user from account');
			throw err; // Re-throw to keep dialog open on error
		} finally {
			actionLoading = false;
			selectedAccountUser = null;
		}
	}

	onMount(() => {
		loadUser();
	});
</script>

<div class="h-full flex flex-col bg-background">
	<div class="flex items-center justify-between px-8 py-6 border-b bg-card">
		<div class="flex items-center gap-4">
			<Button variant="ghost" size="icon" onclick={() => goto('/app/super_admin/users')}>
				<ChevronLeft class="h-5 w-5" />
			</Button>
			<div>
				<div class="text-xs text-muted-foreground mb-1">Users / Edit</div>
				<h1 class="text-2xl font-semibold text-foreground">
					{loading ? 'Loading...' : user?.name || 'User Details'}
				</h1>
			</div>
		</div>
		<div class="flex items-center gap-3">
			{#if !loading && user}
				<Button
					variant="outline"
					onclick={handleToggleLock}
				>
					{#if isUserLocked()}
						<Unlock class="h-4 w-4 mr-2" />
						Unlock User
					{:else}
						<Lock class="h-4 w-4 mr-2" />
						Lock User
					{/if}
				</Button>
				<Button variant="destructive" onclick={handleDelete}>
					<Trash2 class="h-4 w-4 mr-2" />
					Delete
				</Button>
			{/if}
			<Button onclick={handleSave} disabled={saving || loading}>
				<Save class="h-4 w-4 mr-2" />
				{saving ? 'Saving...' : 'Save'}
			</Button>
		</div>
	</div>

	<div class="flex-1 overflow-auto p-8">
		{#if loading}
			<div class="max-w-2xl space-y-6">
				<Skeleton class="h-32 w-32 rounded-lg" />
				<Skeleton class="h-10 w-full" />
				<Skeleton class="h-10 w-full" />
				<Skeleton class="h-10 w-full" />
			</div>
		{:else}
			<div class="max-w-2xl space-y-6">
				<!-- Avatar Section -->
				<div class="space-y-2">
					<Label>Avatar</Label>
					<div class="flex items-center gap-4">
						{#if avatarPreview}
							<div class="relative">
								<img
									src={avatarPreview}
									alt="User avatar"
									class="h-32 w-32 rounded-lg object-cover border border-border"
								/>
								<button
									type="button"
									onclick={handleDeleteAvatar}
									class="absolute -top-2 -right-2 p-1 bg-destructive text-destructive-foreground rounded-full hover:bg-destructive/90 transition-colors"
								>
									<X class="h-4 w-4" />
								</button>
							</div>
						{:else}
							<div class="h-32 w-32 rounded-lg border-2 border-dashed border-border flex items-center justify-center bg-muted">
								<Upload class="h-8 w-8 text-muted-foreground" />
							</div>
						{/if}
						<div>
							<input
								type="file"
								accept="image/*"
								onchange={handleFileSelect}
								class="hidden"
								id="avatar-upload"
							/>
							<Label for="avatar-upload" class="cursor-pointer">
								<div class="px-4 py-2 bg-muted hover:bg-muted/80 rounded-lg border border-border text-sm font-medium text-foreground transition-colors">
									{avatarPreview ? 'Change Avatar' : 'Upload Avatar'}
								</div>
							</Label>
							<p class="text-xs text-muted-foreground mt-1">PNG, JPG up to 2MB</p>
						</div>
					</div>
				</div>

				<!-- Name -->
				<div class="space-y-2">
					<Label for="name">Name *</Label>
					<Input id="name" bind:value={formData.name} required />
				</div>

				<!-- Display Name -->
				<div class="space-y-2">
					<Label for="display_name">Display Name</Label>
					<Input id="display_name" bind:value={formData.display_name} />
				</div>

				<!-- Email -->
				<div class="space-y-2">
					<Label for="email">Email *</Label>
					<Input id="email" type="email" bind:value={formData.email} required />
				</div>

				<!-- User Type (Read-only) -->
				{#if user?.type === 'SuperAdmin'}
					<div class="space-y-2">
						<Label>User Type</Label>
						<div class="px-3 py-2 bg-muted rounded-lg border border-border">
							<span class="text-sm font-medium text-foreground">Super Administrator</span>
							<p class="text-xs text-muted-foreground mt-1">Platform-level administrator with full system access</p>
						</div>
					</div>
				{/if}

				<!-- Role (Account-level) -->
				<div class="space-y-2">
					<Label for="role">Account Role *</Label>
					<Select id="role" bind:value={formData.role}>
						<option value="administrator">Administrator</option>
						<option value="agent">Agent</option>
					</Select>
					<p class="text-xs text-muted-foreground">Role within the current account</p>
				</div>

				<!-- Password -->
				<div class="space-y-2">
					<Label for="password">Password (leave blank to keep unchanged)</Label>
					<Input id="password" type="password" bind:value={formData.password} />
				</div>

				<!-- Email Confirmation -->
				<div class="space-y-2">
					<Label for="confirmed_at">Email Confirmed At</Label>
					<div class="flex gap-2">
						<Input 
							id="confirmed_at" 
							type="datetime-local" 
							bind:value={formData.confirmed_at}
							placeholder="Leave empty if not confirmed"
							class="flex-1"
						/>
						<Button 
							type="button" 
							variant="outline" 
							size="sm"
							onclick={() => formData.confirmed_at = new Date().toISOString().slice(0, 16)}
						>
							Now
						</Button>
						<Button 
							type="button" 
							variant="outline" 
							size="sm"
							onclick={() => formData.confirmed_at = ''}
						>
							Clear
						</Button>
					</div>
					<p class="text-xs text-muted-foreground">
						Set the date and time when the user's email was confirmed (Rails Field::DateTime parity)
					</p>
				</div>

				<!-- Account Assignments Section -->
				<div class="space-y-4 pt-6 border-t border-border">
					<div>
						<h3 class="text-lg font-medium text-foreground mb-2">Account Assignments</h3>
						<p class="text-sm text-muted-foreground mb-4">Accounts this user has access to</p>
					</div>

					<!-- Account Assignment Form -->
					<AccountAssignmentForm 
						userId={parseInt(userId)} 
						onAccountAssigned={handleAccountAssigned}
						existingAccountIds={getExistingAccountIds()}
					/>

					<!-- Existing Account Assignments -->
					{#if accountUsers && accountUsers.length > 0}
						<div class="space-y-3">
							{#each accountUsers as accountUser}
								<div class="flex items-center justify-between p-4 border border-border rounded-lg bg-card">
									<div class="flex items-center space-x-4">
										<Building class="h-5 w-5 text-muted-foreground" />
										<div>
											<div class="font-medium text-foreground">{accountUser.account?.name || 'Unknown Account'}</div>
											{#if accountUser.account?.domain}
												<div class="text-sm text-muted-foreground">{accountUser.account.domain}</div>
											{/if}
											<div class="flex items-center space-x-2 mt-1">
												<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {accountUser.role === 1 ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'}">
													{accountUser.roleName || (accountUser.role === 1 ? 'Administrator' : 'Agent')}
												</span>
												{#if accountUser.createdAt}
													<span class="text-xs text-muted-foreground">
														Joined {new Date(accountUser.createdAt).toLocaleDateString()}
													</span>
												{/if}
											</div>
										</div>
									</div>
									<div class="flex items-center space-x-2">
										<Button variant="outline" size="sm" onclick={() => goto(`/app/super_admin/accounts/${accountUser.accountId || accountUser.account_id}`)}>
											View Account
										</Button>
										<Button 
											variant="outline" 
											size="sm" 
											onclick={() => openRemoveAccountConfirm(accountUser)}
											class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:text-red-300 dark:hover:bg-red-950/20"
										>
											<Trash2 class="h-3 w-3 mr-1" />
											Remove
										</Button>
									</div>
								</div>
							{/each}
						</div>
					{:else}
						<div class="text-sm text-muted-foreground p-4 border border-dashed border-border rounded-lg text-center">
							No account assignments found
						</div>
					{/if}
				</div>
			</div>
		{/if}
	</div>
</div>

<!-- Confirm Dialog -->
<ConfirmDialog
	bind:open={showRemoveAccountConfirm}
	title="Remove User from Account"
	description={selectedAccountUser ? `Are you sure you want to remove this user from "${selectedAccountUser.account?.name}"? This action cannot be undone.` : 'Are you sure you want to remove this user from the account?'}
	confirmText="Remove User"
	variant="destructive"
	onConfirm={handleRemoveFromAccount}
/>
