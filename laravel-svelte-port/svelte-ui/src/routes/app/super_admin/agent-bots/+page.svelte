<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/superAdmin';
	import DataTable from '$lib/components/DataTable.svelte';
	import Button from '$lib/components/ui/button/button.svelte';
	import Input from '$lib/components/ui/input/input.svelte';
	import { Plus, RefreshCw, Search } from 'lucide-svelte';
	import { onMount } from 'svelte';
	import { toast } from 'svelte-sonner';

	let agentBots: any[] = [];
	let loading = true;
	let searchQuery = '';
	let currentPage = 1;
	let totalPages = 1;
	const perPage = 20;

	const columns = [
		{ key: 'id', label: 'ID', sortable: true, width: '80px' },
		{ 
			key: 'avatar', 
			label: 'Avatar', 
			sortable: false,
			width: '80px',
			render: (value: any, row: any) => {
				if (row.avatarUrl) {
					return `<img src="${row.avatarUrl}" alt="${row.name}" class="h-10 w-10 rounded-full object-cover border border-border" />`;
				}
				return `<div class="h-10 w-10 rounded-full bg-muted border border-border flex items-center justify-center text-xs font-medium text-muted-foreground">${row.name.charAt(0).toUpperCase()}</div>`;
			}
		},
		{ 
			key: 'account', 
			label: 'Account', 
			sortable: false,
			width: '200px',
			render: (value: any, row: any) => {
				if (row.account) {
					return `<a href="/app/super_admin/accounts/${row.account.id}" class="text-blue-600 hover:text-blue-800 hover:underline">${row.account.name}</a>`;
				}
				return '<span class="text-muted-foreground">Global</span>';
			}
		},
		{ key: 'name', label: 'Name', sortable: true },
		{ 
			key: 'outgoingUrl', 
			label: 'Outgoing URL',
			sortable: false,
			render: (value: string) => {
				if (value) {
					return `<span class="font-mono text-sm">${value}</span>`;
				}
				return '<span class="text-muted-foreground">-</span>';
			}
		},
		{
			key: 'actions',
			label: 'Actions',
			sortable: false,
			width: '120px',
			render: (value: any, row: any) => {
				return `
					<div class="flex items-center gap-2">
						<button onclick="editBot(${row.id})" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
						<button onclick="deleteBot(${row.id})" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
					</div>
				`;
			}
		}
	];

	async function loadAgentBots() {
		loading = true;
		try {
			const response = await api.agentBots.list({
				page: currentPage,
				per_page: perPage,
				search: searchQuery
			});
			agentBots = response.data || [];
			totalPages = response.last_page || 1;
		} catch (error: any) {
			toast.error('Failed to load agent bots: ' + (error.message || 'Unknown error'));
			agentBots = [];
		} finally {
			loading = false;
		}
	}

	function handleRowClick(bot: any) {
		goto(`/app/super_admin/agent-bots/${bot.id}`); // Go to details page
	}

	function handlePageChange(page: number) {
		currentPage = page;
		loadAgentBots();
	}

	// Make functions globally available for the action buttons
	(globalThis as any).editBot = (id: number) => {
		goto(`/app/super_admin/agent-bots/${id}/edit`); // Go to edit page
	};

	(globalThis as any).deleteBot = async (id: number) => {
		if (!confirm('Are you sure you want to delete this agent bot? This action cannot be undone.')) {
			return;
		}

		try {
			await api.agentBots.delete(id.toString());
			toast.success('Agent bot deleted successfully');
			loadAgentBots(); // Reload the list
		} catch (error: any) {
			toast.error('Failed to delete agent bot: ' + (error.message || 'Unknown error'));
		}
	};

	function handleSearch(event: KeyboardEvent) {
		if (event.key === 'Enter') {
			currentPage = 1;
			loadAgentBots();
		}
	}

	onMount(() => {
		loadAgentBots();
	});
</script>

<div class="h-full flex flex-col bg-background">
	<div class="flex items-center justify-between px-8 py-6 border-b bg-card">
		<h1 class="text-2xl font-semibold text-foreground">Agent Bots</h1>
		<div class="flex items-center gap-3">
			<div class="relative">
				<Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
				<Input
					type="text"
					placeholder="Search agent bots..."
					bind:value={searchQuery}
					onkeydown={handleSearch}
					class="pl-10 w-64"
				/>
			</div>
			<Button variant="outline" size="icon" onclick={loadAgentBots} disabled={loading}>
				<RefreshCw class={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
			</Button>
			<Button onclick={() => goto('/app/super_admin/agent-bots/new')}>
				<Plus class="h-4 w-4 mr-2" />
				New Agent Bot
			</Button>
		</div>
	</div>

	<div class="flex-1 overflow-auto p-8">
		<DataTable
			{columns}
			data={agentBots}
			{loading}
			pagination={{
				page: currentPage,
				perPage: perPage,
				total: totalPages * perPage
			}}
			onPageChange={handlePageChange}
			onRowClick={handleRowClick}
			emptyMessage="No agent bots found"
		/>
	</div>
</div>
