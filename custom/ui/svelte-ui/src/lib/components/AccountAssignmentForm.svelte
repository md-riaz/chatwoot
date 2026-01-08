<script lang="ts">
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '$lib/components/ui/select';
	import { Building, Search, X } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';

	interface Account {
		id: number;
		name: string;
		domain?: string;
		status?: string;
	}

	interface Props {
		userId: number;
		onAccountAssigned?: (accountUser: any) => void;
		onCancel?: () => void;
		existingAccountIds?: number[];
	}

	let { userId, onAccountAssigned, onCancel, existingAccountIds = [] }: Props = $props();

	let isOpen = $state(false);
	let loading = $state(false);
	let searching = $state(false);
	let submitting = $state(false);

	let searchQuery = $state('');
	let selectedAccount = $state<Account | null>(null);
	let selectedRole = $state('agent');
	let accounts = $state<Account[]>([]);
	let searchTimeout: NodeJS.Timeout | null = null;

	let errors = $state<Record<string, string>>({});

	// Search for accounts
	async function searchAccounts(query: string) {
		if (!query.trim()) {
			accounts = [];
			return;
		}

		searching = true;
		try {
			const response = await superAdminApi.getAccounts({
				search: query,
				per_page: 10
			});
			
			// Filter out accounts already assigned to this user
			accounts = response.data.filter(account => !existingAccountIds.includes(account.id));
		} catch (error: any) {
			console.error('Failed to search accounts:', error);
			accounts = [];
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
			searchAccounts(searchQuery);
		}, 300);
	}

	// Select an account from search results
	function selectAccount(account: Account) {
		selectedAccount = account;
		searchQuery = account.name;
		accounts = [];
	}

	// Clear selected account
	function clearSelection() {
		selectedAccount = null;
		searchQuery = '';
		accounts = [];
	}

	// Submit the form
	async function handleSubmit(event: Event) {
		event.preventDefault();
		errors = {};

		if (!selectedAccount) {
			errors.account = 'Please select an account';
			return;
		}

		if (!selectedRole) {
			errors.role = 'Please select a role';
			return;
		}

		submitting = true;
		try {
			const accountUser = await superAdminApi.createAccountUser({
				userId: userId,
				accountId: selectedAccount.id,
				role: selectedRole
			});

			toast.success(`User has been assigned to ${selectedAccount.name} as ${selectedRole}`);
			
			// Reset form
			selectedAccount = null;
			searchQuery = '';
			selectedRole = 'agent';
			accounts = [];
			isOpen = false;

			// Notify parent component
			onAccountAssigned?.(accountUser);

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
		selectedAccount = null;
		searchQuery = '';
		selectedRole = 'agent';
		accounts = [];
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
			<Building class="h-4 w-4 mr-2" />
			Assign to Account
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
				<!-- Account Search -->
				<div class="space-y-2">
					<Label for="account-search">Account *</Label>
					<div class="relative">
						<div class="relative">
							<Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
							<Input
								id="account-search"
								type="text"
								placeholder="Search accounts by name or domain..."
								bind:value={searchQuery}
								oninput={handleSearchInput}
								class="pl-10 {selectedAccount ? 'pr-10' : ''}"
								disabled={submitting}
							/>
							{#if selectedAccount}
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
						{#if accounts.length > 0 && !selectedAccount}
							<div class="absolute z-10 w-full mt-1 bg-popover border border-border rounded-md shadow-lg max-h-60 overflow-auto">
								{#each accounts as account}
									<button
										type="button"
										onclick={() => selectAccount(account)}
										class="w-full px-4 py-2 text-left hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none"
									>
										<div class="font-medium">{account.name}</div>
										{#if account.domain}
											<div class="text-sm text-muted-foreground">{account.domain}</div>
										{/if}
										<div class="text-xs {account.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
											{account.status === 'active' ? 'Active' : 'Suspended'}
										</div>
									</button>
								{/each}
							</div>
						{/if}

						<!-- Loading indicator -->
						{#if searching}
							<div class="absolute right-3 top-1/2 transform -translate-y-1/2">
								<div class="animate-spin h-4 w-4 border-2 border-muted-foreground border-t-transparent rounded-full"></div>
							</div>
						{/if}
					</div>

					{#if errors.account}
						<p class="text-sm text-red-600 dark:text-red-400">{errors.account}</p>
					{/if}

					<!-- Selected Account Display -->
					{#if selectedAccount}
						<div class="mt-2 p-3 bg-accent rounded-md">
							<div class="flex items-center justify-between">
								<div>
									<div class="font-medium text-accent-foreground">{selectedAccount.name}</div>
									{#if selectedAccount.domain}
										<div class="text-sm text-muted-foreground">{selectedAccount.domain}</div>
									{/if}
									<div class="text-xs {selectedAccount.status === 'active' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}">
										{selectedAccount.status === 'active' ? 'Active' : 'Suspended'}
									</div>
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
					<Select bind:value={selectedRole} disabled={submitting}>
						<SelectTrigger>
							<SelectValue placeholder="Select a role" />
						</SelectTrigger>
						<SelectContent>
							<SelectItem value="agent">Agent</SelectItem>
							<SelectItem value="administrator">Administrator</SelectItem>
						</SelectContent>
					</Select>
					{#if errors.role}
						<p class="text-sm text-red-600 dark:text-red-400">{errors.role}</p>
					{/if}
				</div>

				<!-- Form Actions -->
				<div class="flex items-center space-x-3 pt-4">
					<Button type="submit" disabled={submitting || !selectedAccount}>
						{#if submitting}
							<div class="animate-spin h-4 w-4 border-2 border-current border-t-transparent rounded-full mr-2"></div>
						{:else}
							<Building class="h-4 w-4 mr-2" />
						{/if}
						{submitting ? 'Assigning...' : 'Assign to Account'}
					</Button>
					<Button type="button" variant="outline" onclick={handleCancel} disabled={submitting}>
						Cancel
					</Button>
				</div>
			</form>
		</div>
	{/if}
</div>
</script>