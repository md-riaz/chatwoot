<script lang="ts">
import { goto } from '$app/navigation';
import { api } from '$lib/api/superAdmin';
import DataTable from '$lib/components/DataTable.svelte';
import TokenDisplay from '$lib/components/TokenDisplay.svelte';
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
let totalCount = 0;
const perPage = 20;

const columns = [
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: 'Name', sortable: true },
  {
    key: 'accessToken',
    label: 'Access Token',
    sortable: false,
    render: (value: any, row: any) => {
      if (!value) return '—';
      // Create a simple masked display for the table
      const masked = value.length > 8 ? '••••••••' + value.slice(-8) : '••••••••';
      return `<span class="font-mono text-sm">${masked}</span>`;
    }
  },
  {
    key: 'createdAt',
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
      perPage: perPage,
      search: searchQuery
    });
    
    // Handle Laravel standard pagination format
    platformApps = response.data || [];
    totalPages = response.last_page || 1;
    totalCount = response.total || 0;
    currentPage = response.current_page || 1;
  } catch (error: any) {
    console.error('Failed to load platform apps:', error);
    toast.error(error.message || 'Failed to load platform apps');
    platformApps = [];
    totalPages = 1;
    totalCount = 0;
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

function handleRefresh() {
  loadPlatformApps();
}

onMount(() => {
  loadPlatformApps();
});
</script>

<div class="h-full flex flex-col">
  <div class="border-b border-slate-6 px-8 py-6">
    <h1 class="text-2xl font-semibold text-slate-12">Platform Apps</h1>
    <p class="mt-1 text-sm text-slate-11">Manage external applications and their API access</p>
  </div>

  <div class="flex-1 p-8">
    <div class="flex items-center gap-4 mb-6">
      <div class="flex-1">
        <Input
          type="text"
          placeholder="Search platform apps..."
          bind:value={searchQuery}
          onkeydown={handleSearch}
          class="max-w-md"
          disabled={loading}
        />
      </div>
      <Button variant="outline" size="icon" onclick={handleRefresh} disabled={loading}>
        <RefreshCw class="h-4 w-4 {loading ? 'animate-spin' : ''}" />
      </Button>
      <Button onclick={() => {
        console.log('Navigating to new platform app page...');
        goto('/app/super_admin/platform-apps/new');
      }} disabled={loading}>
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
        page: currentPage,
        perPage: perPage,
        total: totalCount
      }}
      onPageChange={handlePageChange}
    />

    {#if !loading && platformApps.length === 0 && !searchQuery}
      <div class="text-center py-12">
        <div class="text-slate-400 mb-4">
          <Plus class="h-12 w-12 mx-auto mb-4 opacity-50" />
        </div>
        <h3 class="text-lg font-medium text-slate-900 mb-2">No Platform Apps</h3>
        <p class="text-slate-500">Get started by creating your first platform app using the "New Platform App" button above</p>
      </div>
    {/if}

    {#if !loading && platformApps.length === 0 && searchQuery}
      <div class="text-center py-12">
        <h3 class="text-lg font-medium text-slate-900 mb-2">No results found</h3>
        <p class="text-slate-500 mb-6">Try adjusting your search terms</p>
        <Button variant="outline" onclick={() => { searchQuery = ''; loadPlatformApps(); }}>
          Clear search
        </Button>
      </div>
    {/if}
  </div>
</div>
