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
		{ key: 'id', label: 'ID', sortable: true },
		{ key: 'name', label: 'Name', sortable: true },
		{ key: 'description', label: 'Description' },
		{ key: 'outgoing_url', label: 'Outgoing URL' },
		{
			key: 'created_at',
			label: 'Created At',
			sortable: true,
			render: (value: any, row: any) => value ? new Date(value).toLocaleString() : ''
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
		goto(`/app/super_admin/agent-bots/${bot.id}`);
	}

	function handleSearch(event: KeyboardEvent) {
		if (event.key === 'Enter') {
			currentPage = 1;
			loadAgentBots();
		}
	}

	function handlePreviousPage() {
		if (currentPage > 1) {
			currentPage--;
			loadAgentBots();
		}
	}

	function handleNextPage() {
		if (currentPage < totalPages) {
			currentPage++;
			loadAgentBots();
		}
	}

	onMount(() => {
		loadAgentBots();
	});
</script>

<div class="flex h-full flex-col">
	<!-- Header -->
	<div class="border-b bg-card px-8 py-6">
		<div class="flex items-center justify-between">
			<h1 class="text-2xl font-semibold text-foreground">Agent Bots</h1>
			<Button
				on:click={() => goto('/app/super_admin/agent-bots/new')}
				class="bg-iris-9 text-white hover:bg-iris-10"
			>
				<Plus class="mr-2 h-4 w-4" />
				New Agent Bot
			</Button>
		</div>
	</div>

	<!-- Content -->
	<div class="flex-1 overflow-auto bg-background p-8">
		<!-- Search and Actions -->
		<div class="mb-6 flex items-center gap-4">
			<div class="relative flex-1">
				<Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-11" />
				<Input
					type="text"
					placeholder="Search agent bots..."
					bind:value={searchQuery}
					on:keydown={handleSearch}
					class="pl-10"
				/>
			</div>
			<Button variant="outline" on:click={loadAgentBots} disabled={loading}>
				<RefreshCw class={`mr-2 h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
				Refresh
			</Button>
		</div>

		<!-- Table -->
		<DataTable
			{columns}
			data={agentBots}
			{loading}
			{currentPage}
			{totalPages}
			onRowClick={handleRowClick}
			onPreviousPage={handlePreviousPage}
			onNextPage={handleNextPage}
			emptyMessage="No agent bots found"
		/>
	</div>
</div>
