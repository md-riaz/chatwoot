<script lang="ts">
	import { goto } from '$app/navigation';
	import { superAdminApi } from '$lib/api/superAdmin';
	import DataTable from '$lib/components/DataTable.svelte';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import { Plus, RefreshCw } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	let searchInput: HTMLInputElement | null = null;

	let users: any[] = [];
	let loading = true;
	let searchQuery = '';
	let currentPage = 1;
	let totalPages = 1;
	let totalCount = 0;

	async function loadUsers() {
		loading = true;
		try {
			const response = await superAdminApi.getUsers({
				page: currentPage,
				per_page: 20,
				search: searchQuery || undefined
			});
			console.log('API Response:', response);
			console.log('Users data:', response.data);
			if (response.data && response.data.length > 0) {
				console.log('First user:', response.data[0]);
				console.log('First user created_at:', response.data[0].created_at);
				console.log('First user createdAt:', response.data[0].createdAt);
			}
			users = response.data || [];
			totalPages = response.last_page || 1;
			totalCount = response.total || 0;
		} catch (error) {
			toast.error('Failed to load users');
			console.error(error);
		} finally {
			loading = false;
		}
	}

	function handleSearch(e: KeyboardEvent) {
		if (e.key === 'Enter') {
			currentPage = 1;
			loadUsers();
		}
	}

	function handleSearchInput() {
		// Debounce search or trigger on Enter
		// For now, we'll just trigger on Enter key
	}

	function handleRowClick(user: any) {
		goto(`/app/super_admin/users/${user.id}`);
	}

	onMount(() => {
		loadUsers();
		
		// Add event listener for search input
		if (searchInput) {
			searchInput.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') {
					currentPage = 1;
					loadUsers();
				}
			});
		}
	});

	const columns = [
		{ key: 'id', label: 'ID', sortable: true },
		{ key: 'name', label: 'Name', sortable: true },
		{ key: 'email', label: 'Email', sortable: true },
		{
			key: 'role',
			label: 'Role',
			render: (value: any, user: any) => {
				const roleColors: Record<string, string> = {
					administrator: 'bg-iris-2 text-iris-11 border-iris-6',
					agent: 'bg-slate-2 text-slate-11 border-slate-6'
				};
				return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border ${roleColors[value] || 'bg-slate-2 text-slate-11 border-slate-6'}">${value}</span>`;
			}
		},
		{
			key: 'confirmed',
			label: 'Confirmed',
			render: (value: any, user: any) => {
				if (value) {
					return '<span class="text-teal-9 flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>';
				}
				return '<span class="text-amber-9 flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>';
			}
		},
		{
			key: 'locked',
			label: 'Status',
			render: (value: any, user: any) => {
				if (value) {
					return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-ruby-2 text-ruby-11 border-ruby-6">Locked</span>';
				}
				return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-teal-2 text-teal-11 border-teal-6">Active</span>';
			}
		},
		{ 
			key: 'createdAt', 
			label: 'Created At',
			render: (value: any, user: any) => {
				console.log('Render createdAt - value:', value, 'user.createdAt:', user.createdAt, 'user.created_at:', user.created_at);
				if (value) {
					return new Date(value).toLocaleDateString();
				}
				return '';
			}
		}
	];
</script>

<div class="h-full flex flex-col bg-background">
	<div class="flex items-center justify-between px-8 py-6 border-b bg-card">
		<h1 class="text-2xl font-semibold text-foreground">Users</h1>
		<div class="flex items-center gap-3">
			<div class="relative">
				<Input
					type="text"
					placeholder="Search users..."
					bind:value={searchQuery}
					bind:ref={searchInput}
					class="w-64"
				/>
			</div>
			<Button variant="outline" size="icon" onclick={loadUsers}>
				<RefreshCw class="h-4 w-4" />
			</Button>
			<Button onclick={() => goto('/app/super_admin/users/new')}>
				<Plus class="h-4 w-4 mr-2" />
				New User
			</Button>
		</div>
	</div>

	<div class="flex-1 overflow-auto p-8">
		<DataTable
			{columns}
			data={users}
			{loading}
			pagination={{
				page: currentPage,
				perPage: 20,
				total: totalCount
			}}
			onPageChange={(page) => {
				currentPage = page;
				loadUsers();
			}}
			onRowClick={handleRowClick}
		/>
	</div>
</div>
