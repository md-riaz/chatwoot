<script lang="ts">
import { goto } from '$app/navigation';
import { api } from '$lib/api/superAdmin';
import DataTable from '$lib/components/DataTable.svelte';
import Button from '$lib/components/ui/button/button.svelte';
import Input from '$lib/components/ui/input/input.svelte';
import { Plus, RefreshCw } from 'lucide-svelte';
import { onMount } from 'svelte';
import { toast } from 'svelte-sonner';

let platformApps: any[] = [];
let loading = true;
let searchQuery = '';
let currentPage = 1;
let totalPages = 1;
const perPage = 20;

const columns = [
{ key: 'id', label: 'ID', sortable: true },
{ key: 'name', label: 'Name', sortable: true },
{
key: 'webhook_url',
label: 'Webhook URL',
sortable: false,
render: (value: any, row: any) => value || '—'
},
{
key: 'created_at',
label: 'Created At',
sortable: true,
render: (value: any, row: any) => value ? new Date(value).toLocaleDateString() : ''
}
];

async function loadPlatformApps() {
loading = true;
try {
const response = await api.platformApps.list({
page: currentPage,
per_page: perPage,
search: searchQuery
});
platformApps = response.data || [];
totalPages = response.meta?.total_pages || 1;
} catch (error: any) {
toast.error(error.message || 'Failed to load platform apps');
platformApps = [];
} finally {
loading = false;
}
}

function handleSearch(event: KeyboardEvent) {
if (event.key === 'Enter') {
currentPage = 1;
loadPlatformApps();
}
}

function handleRowClick(row: any) {
goto(`/app/super_admin/platform-apps/${row.id}`);
}

function handlePageChange(page: number) {
currentPage = page;
loadPlatformApps();
}

onMount(() => {
loadPlatformApps();
});
</script>

<div class="h-full flex flex-col">
<div class="border-b border-slate-6 px-8 py-6">
<h1 class="text-2xl font-semibold text-slate-12">Platform Apps</h1>
</div>

<div class="flex-1 p-8">
<div class="flex items-center gap-4 mb-6">
<div class="flex-1">
<Input
type="text"
placeholder="Search platform apps..."
bind:value={searchQuery}
on:keydown={handleSearch}
class="max-w-md"
/>
</div>
<Button variant="outline" size="icon" on:click={() => loadPlatformApps()}>
<RefreshCw class="h-4 w-4" />
</Button>
<Button on:click={() => goto('/app/super_admin/platform-apps/new')}>
<Plus class="h-4 w-4 mr-2" />
New Platform App
</Button>
</div>

<DataTable
{columns}
data={platformApps}
{loading}
onRowClick={handleRowClick}
pagination={{
currentPage,
totalPages,
onPageChange: handlePageChange
}}
/>
</div>
</div>
