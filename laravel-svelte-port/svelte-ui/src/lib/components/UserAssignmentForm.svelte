<script lang="ts">
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import * as Select from '$lib/components/ui/select';
	import { Search, UserPlus, X } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';

	interface User {
		id: number;
		name: string;
		email: string;
		displayName?: string;
		type?: string;
	}

	interface Props {
		accountId: number;
		onUserAssigned?: (accountUser: any) => void;
		onCancel?: () => void;
		existingUserIds?: number[];
	}

	let { accountId, onUserAssigned, onCancel, existingUserIds = [] }: Props = $props();

	let isOpen = $state(false);
	let loading = $state(false);
	let searching = $state(false);
	let submitting = $state(false);

	let searchQuery = $state('');
	let selectedUser = $state<User | null>(null);
	let selectedRole = $state('agent');
	let users = $state<User[]>([]);
	let searchTimeout: NodeJS.Timeout | null = null;

	let errors = $state<Record<string, string>>({});

	// Role options (following official docs pattern)
	const roleOptions = [
		{ value: "agent", label: "Agent" },
		{ value: "administrator", label: "Administrator" }
	];

	// Trigger content (following official docs pattern)
	const roleTriggerContent = $derived(
		roleOptions.find((r) => r.value === selectedRole)?.label ?? "Select a role"
	);

	// Search for users
	async function searchUsers(query: string) {
		if (!query.trim()) {
			users = [];
			return;
		}

		searching = true;
		try {
			const response = await superAdminApi.getUsers({
				search: query,
				per_page: 10
			});
			
			console.log('API Response:', response); // Debug log
			console.log('Response data:', response.data); // Debug log
			
			// Filter out users already assigned to this account
			users = response.data.filter(user => !existingUserIds.includes(user.id));
			console.log('Filtered users:', users); // Debug log
		} catch (error: any) {
			console.error('Failed to search users:', error);
			users = [];
		} finally {
			searching = false;
		}
	}

	// Handle search input with debouncing
	function handleSearchInput(event: Event) {
		const target = event.target as HTMLInputElement;
		searchQuery = target.value;

		if (searchTimeout) {
			clearTimeout(searchTimeout);
		}

		searchTimeout = setTimeout(() => {
			searchUsers(searchQuery);
		}, 300);
	}

	// Select a user from search results
	function selectUser(user: User) {
		selectedUser = user;
		searchQuery = user.displayName || user.name;
		users = [];
	}

	// Clear selected user
	function clearSelection() {
		selectedUser = null;
		searchQuery = '';
		users = [];
	}

	// Submit the form
	async function handleSubmit(event: Event) {
		event.preventDefault();
		errors = {};

		if (!selectedUser) {
			errors.user = 'Please select a user';
			return;
		}

		if (!selectedRole) {
			errors.role = 'Please select a role';
			return;
		}

		submitting = true;
		try {
			const accountUser = await superAdminApi.createAccountUser({
				userId: selectedUser.id,
				accountId: accountId,
				role: selectedRole
			});

			toast.success(`${selectedUser.name} has been assigned to the account as ${selectedRole}`);
			
			// Reset form
			selectedUser = null;
			searchQuery = '';
			selectedRole = 'agent';
			users = [];
			isOpen = false;

			// Notify parent component
			onUserAssigned?.(accountUser);

		} catch (error: any) {
			if (error.response?.error) {
				toast.error(error.response.error);
			} else {
				toast.error(error.message || 'Failed to assign user to account');
			}
		} finally {
			submitting = false;
		}
	}

	// Handle cancel
	function handleCancel() {
		selectedUser = null;
		searchQuery = '';
		selectedRole = 'agent';
		users = [];
		errors = {};
		isOpen = false;
		onCancel?.();
	}

	// Open/close form
	function toggleForm() {
		isOpen = !isOpen;
		if (!isOpen) {
			handleCancel();
		}
	}
</script>

<div class="space-y-4">
	<!-- Toggle Button -->
	{#if !isOpen}
		<Button onclick={toggleForm} class="w-full sm:w-auto">
			<UserPlus class="h-4 w-4 mr-2" />
			Assign User to Account
		</Button>
	{/if}

	<!-- Assignment Form -->
	{#if isOpen}
		<div class="border border-border rounded-lg p-6 bg-card">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-lg font-medium text-foreground">Assign User to Account</h3>
				<Button variant="ghost" size="sm" onclick={handleCancel}>
					<X class="h-4 w-4" />
				</Button>
			</div>

			<form onsubmit={handleSubmit} class="space-y-4">
				<!-- User Search -->
				<div class="space-y-2">
					<Label for="user-search">User *</Label>
					<div class="relative">
						<div class="relative">
							<Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
							<Input
								id="user-search"
								type="text"
								placeholder="Search users by name or email..."
								bind:value={searchQuery}
								oninput={handleSearchInput}
								class="pl-10 {selectedUser ? 'pr-10' : ''}"
								disabled={submitting}
							/>
							{#if selectedUser}
								<button
									type="button"
									onclick={clearSelection}
									class="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground hover:text-foreground"
								>
									<X class="h-4 w-4" />
								</button>
							{/if}
						</div>

						<!-- Search Results Dropdown -->
						{#if users.length > 0 && !selectedUser}
							<div class="absolute z-10 w-full mt-1 bg-popover border border-border rounded-md shadow-lg max-h-60 overflow-auto">
								<!-- Debug info -->
								<div class="px-4 py-2 text-xs text-muted-foreground border-b">
									Found {users.length} users
								</div>
								{#each users as user}
									<button
										type="button"
										onclick={() => selectUser(user)}
										class="w-full px-4 py-2 text-left hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-hidden"
									>
										<div class="font-medium">{user.displayName || user.name}</div>
										<div class="text-sm text-muted-foreground">{user.email}</div>
										{#if user.type === 'SuperAdmin'}
											<div class="text-xs text-purple-600 dark:text-purple-400">Super Admin</div>
										{/if}
									</button>
								{/each}
							</div>
						{:else if searchQuery && !searching && users.length === 0}
							<div class="absolute z-10 w-full mt-1 bg-popover border border-border rounded-md shadow-lg p-4">
								<div class="text-sm text-muted-foreground">No users found</div>
							</div>
						{/if}

						<!-- Loading indicator -->
						{#if searching}
							<div class="absolute right-3 top-1/2 transform -translate-y-1/2">
								<div class="animate-spin h-4 w-4 border-2 border-muted-foreground border-t-transparent rounded-full"></div>
							</div>
						{/if}
					</div>

					{#if errors.user}
						<p class="text-sm text-red-600 dark:text-red-400">{errors.user}</p>
					{/if}

					<!-- Selected User Display -->
					{#if selectedUser}
						<div class="mt-2 p-3 bg-accent rounded-md">
							<div class="flex items-center justify-between">
								<div>
									<div class="font-medium text-accent-foreground">{selectedUser.displayName || selectedUser.name}</div>
									<div class="text-sm text-muted-foreground">{selectedUser.email}</div>
									{#if selectedUser.type === 'SuperAdmin'}
										<div class="text-xs text-purple-600 dark:text-purple-400">Super Admin</div>
									{/if}
								</div>
								<Button variant="ghost" size="sm" onclick={clearSelection}>
									<X class="h-4 w-4" />
								</Button>
							</div>
						</div>
					{/if}
				</div>

				<!-- Role Selection -->
				<div class="space-y-2">
					<Label for="role">Role *</Label>
					<Select.Root type="single" name="role" bind:value={selectedRole} disabled={submitting}>
						<Select.Trigger class="w-full">
							{roleTriggerContent}
						</Select.Trigger>
						<Select.Content>
							{#each roleOptions as role (role.value)}
								<Select.Item value={role.value} label={role.label}>
									{role.label}
								</Select.Item>
							{/each}
						</Select.Content>
					</Select.Root>
					{#if errors.role}
						<p class="text-sm text-red-600 dark:text-red-400">{errors.role}</p>
					{/if}
				</div>

				<!-- Form Actions -->
				<div class="flex items-center space-x-3 pt-4">
					<Button type="submit" disabled={submitting || !selectedUser}>
						{#if submitting}
							<div class="animate-spin h-4 w-4 border-2 border-current border-t-transparent rounded-full mr-2"></div>
						{:else}
							<UserPlus class="h-4 w-4 mr-2" />
						{/if}
						{submitting ? 'Assigning...' : 'Assign User'}
					</Button>
					<Button type="button" variant="outline" onclick={handleCancel} disabled={submitting}>
						Cancel
					</Button>
				</div>
			</form>
		</div>
	{/if}
</div>