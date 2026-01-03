<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/superAdmin';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import DataTable from '$lib/components/DataTable.svelte';
	import { Plus, Search, RefreshCw, Trash2, Settings as SettingsIcon } from 'lucide-svelte';
	import { toast } from 'svelte-sonner';
	
	let loading = true;
	let accounts: any[] = [];
	let searchQuery = '';
	let pagination = {
		page: 1,
		perPage: 20,
		total: 0
	};
	
	const columns = [
		{ key: 'id', label: 'ID', sortable: true },
		{ key: 'name', label: 'Name', sortable: true },
		{ 
			key: 'created_at', 
			label: 'Created At', 
			sortable: true,
			render: (value: string) => {
				if (!value) return '-';
				return new Date(value).toLocaleDateString();
			}
		},
		{
			key: 'status',
			label: 'Status',
			render: (value: string) => {
				const statusColors: Record<string, string> = {
					active: 'rgb(18, 165, 148)',
					suspended: 'rgb(229, 70, 102)'
				};
				const color = statusColors[value] || 'rgb(var(--slate-11))';
				return `<span style="color: ${color}; font-weight: 500;">${value || 'active'}</span>`;
			}
		}
	];
	
	async function loadAccounts() {
		loading = true;
		try {
			const response = await superAdminApi.getAccounts({
				page: pagination.page,
				per_page: pagination.perPage,
				search: searchQuery
			});
			
			accounts = response.accounts || [];
			pagination.total = response.meta?.total || accounts.length;
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
	
	onMount(() => {
		loadAccounts();
	});
</script>

<svelte:head>
	<title>Accounts - Super Admin - Chatwoot</title>
</svelte:head>

<div class="w-full h-full">
	<!-- Header -->
	<header class="px-8 py-6 border-b bg-white dark:bg-slate-1 flex items-center justify-between" style="border-color: rgb(var(--slate-6));">
		<div>
			<h1 class="text-2xl font-semibold" style="color: rgb(var(--slate-12));">
				Accounts
			</h1>
			<p class="text-sm mt-1" style="color: rgb(var(--slate-11));">
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
					<Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4" style="color: rgb(var(--slate-10));" />
					<Input
						type="text"
						placeholder="Search accounts..."
						bind:value={searchQuery}
						class="pl-10"
						onkeydown={(e) => e.key === 'Enter' && handleSearch()}
					/>
				</div>
			</div>
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
			{onRowClick}
			onPageChange={handlePageChange}
		/>
	</section>
</div>
