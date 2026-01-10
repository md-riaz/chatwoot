<script lang="ts">
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/superAdmin';
	import DataTable from '$lib/components/DataTable.svelte';
	import { Button } from '$lib/components/ui/button';
	import * as Select from '$lib/components/ui/select';
	import { Plus, RefreshCw, Search } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';
	
	// Svelte 5 runes - reactive state
	let loading = $state(true);
	let accounts = $state<any[]>([]);
	let searchQuery = $state('');
	let statusFilter = $state({ value: '' });
	let recentFilter = $state(false);
	let markedForDeletionFilter = $state(false);
	let pagination = $state({
		page: 1,
		perPage: 20,
		total: 0,
		lastPage: 1
	});
	
	// Status filter display for Select component
	const statusFilterDisplay = $derived(
		statusFilter.value === 'active' ? 'Active' : 
		statusFilter.value === 'suspended' ? 'Suspended' : 
		'All Statuses'
	);
	
	const columns = [
		{ key: 'id', label: 'ID', sortable: true, width: '80px' },
		{ key: 'name', label: 'Name', sortable: true },
		{ 
			key: 'locale', 
			label: 'Locale', 
			sortable: false,
			width: '100px',
			render: (value: string) => value?.toUpperCase() || 'EN'
		},
		{ 
			key: 'usersCount', 
			label: 'Users', 
			sortable: false,
			width: '80px',
			render: (value: number) => {
				return `<span class="font-medium text-blue-600">${value ?? 0}</span>`;
			}
		},
		{ 
			key: 'inboxesCount', 
			label: 'Inboxes', 
			sortable: false,
			width: '80px',
			render: (value: number) => {
				const count = value ?? 0;
				return `<span class="font-medium text-green-600">${count}</span>`;
			}
		},
		{ 
			key: 'conversationsCount', 
			label: 'Conversations', 
			sortable: false,
			width: '120px',
			render: (value: number) => {
				const count = value ?? 0;
				return `<span class="font-medium text-purple-600">${count}</span>`;
			}
		},
		{ 
			key: 'contactsCount', 
			label: 'Contacts', 
			sortable: false,
			width: '100px',
			render: (value: number) => {
				const count = value ?? 0;
				return `<span class="font-medium text-orange-600">${count}</span>`;
			}
		},
		{
			key: 'status',
			label: 'Status',
			width: '100px',
			render: (value: string) => {
				const statusColors: Record<string, string> = {
					active: 'rgb(18, 165, 148)',
					suspended: 'rgb(229, 70, 102)'
				};
				const color = statusColors[value] || 'rgb(var(--slate-11))';
				const displayValue = value === 'active' ? 'Active' : 'Suspended';
				return `<span style="color: ${color}; font-weight: 500;">${displayValue}</span>`;
			}
		}
	];
	
	async function loadAccounts() {
		loading = true;
		try {
			const params: any = {
				page: pagination.page,
				per_page: pagination.perPage
			};
			
			if (searchQuery) {
				params.search = searchQuery;
			}
			
			if (statusFilter.value) {
				params.status = statusFilter.value;
			}
			
			if (recentFilter) {
				params.recent = true;
			}
			
			if (markedForDeletionFilter) {
				params.marked_for_deletion = true;
			}
			
			const response = await superAdminApi.getAccounts(params);
			
			accounts = response.data || [];
			pagination.total = response.total || 0;
			
		} catch (error: any) {
			toast.error(error.message || 'Failed to load accounts');
		} finally {
			loading = false;
		}
	}
	
	function handleRowClick(account: any) {
		goto(`/app/super_admin/accounts/${account.id}`);
	}
	
	function handlePageChange(newPage: number) {
		pagination.page = newPage;
		loadAccounts();
	}
	
	function handleSearch() {
		pagination.page = 1;
		loadAccounts();
	}
	
	function handleStatusFilterChange() {
		pagination.page = 1;
		loadAccounts();
	}
	
	function handleRecentFilterChange() {
		pagination.page = 1;
		loadAccounts();
	}
	
	function handleMarkedForDeletionFilterChange() {
		pagination.page = 1;
		loadAccounts();
	}
	
	// Svelte 5 runes - effects for reactive behavior
	let initialized = $state(false);
	
	// Effect to watch status filter changes
	$effect(() => {
		if (initialized && statusFilter.value !== undefined) {
			handleStatusFilterChange();
		}
	});
	
	// Initialize on mount
	onMount(() => {
		initialized = true;
		loadAccounts();
	});
</script>

<svelte:head>
	<title>Accounts - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-card flex items-center justify-between">
		<div>
			<h1 class="text-2xl font-semibold text-foreground">
				Accounts
			</h1>
			<p class="text-sm mt-1 text-muted-foreground">
				Manage all accounts in your Chatwoot instance
			</p>
		</div>
		<Button onclick={() => goto('/app/super_admin/accounts/new')}>
			<Plus class="h-4 w-4 mr-2" />
			New Account
		</Button>
	</header>

	<!-- Body -->
	<section class="p-8">
		<!-- Search and Filters -->
		<div class="mb-6 flex items-center space-x-4">
			<div class="flex-1 max-w-md">
				<div class="relative">
					<Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
					<input
						type="text"
						placeholder="Search accounts by name, domain, or ID..."
						bind:value={searchQuery}
						class="pl-10 px-3 py-2 border rounded-md bg-background text-foreground w-full"
						onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && handleSearch()}
					/>
				</div>
			</div>
			<Select.Root type="single" bind:value={statusFilter.value}>
				<Select.Trigger class="w-[180px]">
					{statusFilterDisplay}
				</Select.Trigger>
				<Select.Content>
					<Select.Item value="">All Statuses</Select.Item>
					<Select.Item value="active">Active</Select.Item>
					<Select.Item value="suspended">Suspended</Select.Item>
				</Select.Content>
			</Select.Root>
			<label class="flex items-center space-x-2">
				<input 
					type="checkbox" 
					bind:checked={recentFilter}
					onchange={handleRecentFilterChange}
					class="rounded"
				/>
				<span class="text-sm">Recent (30 days)</span>
			</label>
			<label class="flex items-center space-x-2">
				<input 
					type="checkbox" 
					bind:checked={markedForDeletionFilter}
					onchange={handleMarkedForDeletionFilterChange}
					class="rounded"
				/>
				<span class="text-sm">Marked for deletion</span>
			</label>
			<Button variant="outline" onclick={handleSearch}>
				Search
			</Button>
			<Button variant="outline" onclick={loadAccounts}>
				<RefreshCw class="h-4 w-4 mr-2" />
				Refresh
			</Button>
		</div>

		<!-- Data Table -->
		<DataTable
			{columns}
			data={accounts}
			{loading}
			{pagination}
			onRowClick={handleRowClick}
			onPageChange={handlePageChange}
		/>
		
	</section>
</div>
