<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { toast } from 'svelte-sonner';
	import { RefreshCw, Plus, CheckCircle, Clock, Lock } from 'lucide-svelte';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import Badge from '$lib/components/ui/badge/badge.svelte';
	import DataTable from '$lib/components/DataTable.svelte';
	import { superAdminApi } from '$lib/api/superAdmin';

	let users: any[] = [];
	let loading = true;
	let searchQuery = '';
	let currentPage = 1;
	let totalPages = 1;

	async function loadUsers() {
		loading = true;
		try {
			const data = await superAdminApi.users.list({
				page: currentPage,
				per_page: 20,
				search: searchQuery || undefined
			});
			users = data.users || [];
			totalPages = data.meta?.pages || 1;
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

	function handleRowClick(user: any) {
		goto(`/app/super_admin/users/${user.id}`);
	}

	onMount(() => {
		loadUsers();
	});

	const columns = [
		{ key: 'id', label: 'ID', sortable: true },
		{ key: 'name', label: 'Name', sortable: true },
		{ key: 'email', label: 'Email', sortable: true },
		{
			key: 'role',
			label: 'Role',
			render: (user: any) => {
				const roleColors: Record<string, string> = {
					administrator: 'bg-iris-2 text-iris-11 border-iris-6',
					agent: 'bg-slate-2 text-slate-11 border-slate-6'
				};
				return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border ${roleColors[user.role] || 'bg-slate-2 text-slate-11 border-slate-6'}">${user.role}</span>`;
			}
		},
		{
			key: 'confirmed',
			label: 'Confirmed',
			render: (user: any) => {
				if (user.confirmed) {
					return '<span class="text-teal-9 flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>';
				}
				return '<span class="text-amber-9 flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>';
			}
		},
		{
			key: 'locked',
			label: 'Status',
			render: (user: any) => {
				if (user.locked) {
					return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-ruby-2 text-ruby-11 border-ruby-6">Locked</span>';
				}
				return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium border bg-teal-2 text-teal-11 border-teal-6">Active</span>';
			}
		},
		{ key: 'created_at', label: 'Created At' }
	];
</script>

<div class="h-full flex flex-col bg-white dark:bg-slate-1">
	<div class="flex items-center justify-between px-8 py-6 border-b border-slate-6">
		<h1 class="text-2xl font-semibold text-slate-12">Users</h1>
		<div class="flex items-center gap-3">
			<div class="relative">
				<Input
					type="text"
					placeholder="Search users..."
					bind:value={searchQuery}
					onkeydown={handleSearch}
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
			{currentPage}
			{totalPages}
			onPageChange={(page) => {
				currentPage = page;
				loadUsers();
			}}
			onRowClick={handleRowClick}
			emptyMessage="No users found"
		/>
	</div>
</div>
