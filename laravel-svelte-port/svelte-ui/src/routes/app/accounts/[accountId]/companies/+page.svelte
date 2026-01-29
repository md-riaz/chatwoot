<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { companiesStore } from '$lib/stores/companies.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Search, Plus, Filter, ArrowUpDown, MoreVertical } from 'lucide-svelte';
  import type { Company } from '$lib/api/companies';
  import CompanyDialog from '$lib/components/companies/CompanyDialog.svelte';
  import CompanyCard from './_components/CompanyCard.svelte';

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
</script>

<div class="h-full flex flex-col bg-background">
  <!-- Header -->
  <div class="flex items-center justify-between px-6 py-4 border-b">
    <h1 class="text-xl font-medium">Companies</h1>
    
    <div class="flex items-center gap-2">
      <!-- Search -->
      <div class="relative w-64">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          value={searchQuery}
          oninput={handleSearchInput}
          placeholder="Search companies..."
          class="pl-9 h-9"
        />
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1 border-l pl-2 ml-2">
        <Button variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground">
          <Filter class="h-4 w-4" />
        </Button>
        <Button variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground">
          <ArrowUpDown class="h-4 w-4" />
        </Button>
        <Button variant="ghost" size="icon" class="h-9 w-9 text-muted-foreground">
          <MoreVertical class="h-4 w-4" />
        </Button>
      </div>

      <Button class="gap-2 ml-2 bg-blue-600 hover:bg-blue-700 text-white" onclick={handleCreateCompany}>
        <Plus class="h-4 w-4" />
        New Company
      </Button>
    </div>
  </div>

  <!-- Content -->
  <div class="flex-1 overflow-y-auto relative">
    {#if isLoading}
      <!-- Loading skeleton -->
      <div class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#each Array(6) as _}
          <div class="border rounded-lg p-6 bg-card">
            <div class="flex items-start gap-4">
              <div class="h-12 w-12 rounded-full bg-muted animate-pulse"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 w-32 bg-muted animate-pulse rounded"></div>
                <div class="h-3 w-40 bg-muted animate-pulse rounded"></div>
                <div class="h-3 w-36 bg-muted animate-pulse rounded"></div>
              </div>
            </div>
          </div>
        {/each}
      </div>
    {:else if companies.length === 0}
      <!-- Empty state -->
      <div class="relative w-full max-w-[60rem] mx-auto overflow-hidden h-full max-h-[28rem] mt-12">
        {#if !searchQuery}
          <!-- Ghost Background for Empty State -->
          <div class="w-full h-full space-y-4 overflow-y-hidden opacity-50 pointer-events-none">
            {#each Array(5) as _}
              <div class="flex items-center gap-4 p-4 border rounded-lg bg-card/50">
                <div class="h-10 w-10 rounded-full bg-muted"></div>
                <div class="flex-1 space-y-2">
                  <div class="h-4 bg-muted w-1/4 rounded"></div>
                  <div class="h-3 bg-muted w-1/3 rounded"></div>
                </div>
              </div>
            {/each}
          </div>
        {/if}

        <div class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-end w-full h-full pb-20 bg-gradient-to-t from-background from-25% to-transparent">
          <div class="flex flex-col items-center justify-center gap-6">
            {#if searchQuery}
              <div class="flex flex-col items-center text-center">
                <div class="mb-4 h-12 w-12 rounded-full bg-muted flex items-center justify-center">
                  <Search class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-semibold mb-2">No companies found</h3>
                <p class="text-sm text-muted-foreground max-w-md">
                  We couldn't find any companies matching "{searchQuery}". Try adjusting your search query.
                </p>
              </div>
            {:else}
              <div class="flex flex-col items-center justify-center gap-3">
                <h2 class="text-3xl font-medium text-center text-foreground">
                  No companies found in this account
                </h2>
                <p class="max-w-xl text-base text-center text-muted-foreground">
                  Start adding new companies by clicking on the button below
                </p>
              </div>
              <Button class="gap-2 bg-blue-600 hover:bg-blue-700 text-white min-w-[140px]" onclick={handleCreateCompany}>
                <Plus class="h-4 w-4" />
                Add Company
              </Button>
            {/if}
          </div>
        </div>
      </div>
    {:else}
      <!-- Companies Grid -->
      <div class="p-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#each companies as company (company.id)}
          <CompanyCard 
            {company}
            onView={handleViewCompany}
            onEdit={handleEditCompany}
            onDelete={handleDeleteCompany}
          />
        {/each}
      </div>
    {/if}
  </div>
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
