<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { companiesStore } from '$lib/stores/companies.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import type { Company } from '$lib/api/companies';
  import CompanyDialog from '$lib/components/companies/CompanyDialog.svelte';

  let accountId = $derived($page.params.accountId);
  let isLoading = $derived(companiesStore.isLoading);
  let companies = $derived(companiesStore.filteredCompanies);
  let searchQuery = $state('');
  let searchTimeout: ReturnType<typeof setTimeout> | null = null;

  let showCreateDialog = $state(false);
  let showEditDialog = $state(false);
  let editingCompany = $state<Company | null>(null);

  onMount(() => {
    companiesStore.fetchCompanies({ page: 1, sort: 'name' });
  });

  function handleCreateCompany() {
    showCreateDialog = true;
  }

  async function handleSubmitCreate(event: CustomEvent) {
    const data = event.detail;
    await companiesStore.createCompany(data);
    companiesStore.fetchCompanies({ page: 1, sort: 'name' });
  }

  function handleEditCompany(company: Company) {
    editingCompany = company;
    showEditDialog = true;
  }

  async function handleSubmitEdit(event: CustomEvent) {
    if (!editingCompany) return;
    const data = event.detail;
    await companiesStore.updateCompany(editingCompany.id, data);
    companiesStore.fetchCompanies({ page: 1, sort: 'name' });
    editingCompany = null;
  }

  function handleViewCompany(companyId: number) {
    goto(`/app/accounts/${accountId}/companies/${companyId}`);
  }

  async function handleDeleteCompany(companyId: number, companyName: string) {
    if (
      confirm(`Are you sure you want to delete "${companyName}"? This action cannot be undone.`)
    ) {
      await companiesStore.deleteCompany(companyId);
    }
  }

  function handleSearchInput(event: Event) {
    const target = event.target as HTMLInputElement;
    searchQuery = target.value;

    // Clear existing timeout
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    // Debounce search
    searchTimeout = setTimeout(() => {
      if (searchQuery.trim()) {
        companiesStore.searchCompanies({ q: searchQuery, page: 1 });
      } else {
        companiesStore.fetchCompanies({ page: 1, sort: 'name' });
      }
    }, 300);
  }

  function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString();
  }

  function isValidUrl(url: string): boolean {
    try {
      const urlObj = new URL(url.startsWith('http') ? url : `https://${url}`);
      return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
    } catch {
      return false;
    }
  }
</script>

<div class="companies-page p-6">
  <div class="companies-header mb-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-2xl font-bold">Companies</h1>
        <p class="text-gray-600 mt-1">
          Manage your organizations and track their interactions
        </p>
      </div>
      <Button onclick={handleCreateCompany}>Create Company</Button>
    </div>

    <!-- Search bar -->
    <div class="search-bar">
      <Input
        type="search"
        placeholder="Search companies by name, website, or industry..."
        value={searchQuery}
        on:input={handleSearchInput}
        class="max-w-md"
      />
    </div>
  </div>

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if companies.length === 0}
    <div class="empty-state text-center py-20">
      <div class="mb-4">
        <svg
          class="mx-auto h-16 w-16 text-gray-400"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
          />
        </svg>
      </div>
      <h2 class="text-xl font-semibold mb-2">
        {searchQuery ? 'No companies found' : 'No companies yet'}
      </h2>
      <p class="text-gray-600 mb-4">
        {searchQuery
          ? 'Try adjusting your search criteria'
          : 'Create your first company to start organizing your contacts'}
      </p>
      {#if !searchQuery}
        <Button onclick={handleCreateCompany}>Create Your First Company</Button>
      {/if}
    </div>
  {:else}
    <div class="companies-stats mb-4 text-sm text-gray-600">
      Showing {companies.length} {companies.length === 1 ? 'company' : 'companies'}
      {#if searchQuery}
        matching "{searchQuery}"
      {/if}
    </div>

    <div class="companies-grid grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      {#each companies as company}
        <div
          class="company-card border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
          role="button"
          tabindex="0"
          on:click={() => handleViewCompany(company.id)}
          on:keydown={(event) => {
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              handleViewCompany(company.id);
            }
          }}
        >
          <div class="flex justify-between items-start mb-2">
            <h3 class="font-semibold text-lg">{company.name}</h3>
          </div>

          {#if company.website}
            <div class="text-sm text-blue-600 mb-2 truncate">
              <a
                href={isValidUrl(company.website) ? company.website : `https://${company.website}`}
                target="_blank"
                rel="noopener noreferrer"
                on:click|stopPropagation={() => {}}
              >
                {company.website}
              </a>
            </div>
          {/if}

          {#if company.description}
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
              {company.description}
            </p>
          {/if}

          <div class="company-meta text-xs text-gray-500 space-y-1 mb-3">
            {#if company.industry}
              <div class="flex items-center gap-1">
                <span class="font-medium">Industry:</span>
                <span>{company.industry}</span>
              </div>
            {/if}
            {#if company.size}
              <div class="flex items-center gap-1">
                <span class="font-medium">Size:</span>
                <span>{company.size}</span>
              </div>
            {/if}
            <div class="flex items-center gap-1">
              <span class="font-medium">Contacts:</span>
              <span>{company.contactsCount || 0}</span>
            </div>
            <div class="flex items-center gap-1">
              <span class="font-medium">Created:</span>
              <span>{formatDate(company.createdAt)}</span>
            </div>
          </div>

          <div class="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onclick={(event: MouseEvent) => {
                event.stopPropagation();
                handleViewCompany(company.id);
              }}
            >
              View
            </Button>
            <Button
              variant="outline"
              size="sm"
              onclick={(event: MouseEvent) => {
                event.stopPropagation();
                handleEditCompany(company);
              }}
            >
              Edit
            </Button>
            <Button
              variant="destructive"
              size="sm"
              onclick={(event: MouseEvent) => {
                event.stopPropagation();
                handleDeleteCompany(company.id, company.name);
              }}
            >
              Delete
            </Button>
          </div>
        </div>
      {/each}
    </div>

    <!-- Pagination would go here if needed -->
  {/if}
</div>

<!-- Company Dialogs -->
<CompanyDialog
  bind:open={showCreateDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<CompanyDialog
  bind:open={showEditDialog}
  mode="edit"
  company={editingCompany}
  on:submit={handleSubmitEdit}
/>

<style>
  .line-clamp-2 {
    display: -webkit-box;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>
