<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { companiesStore } from '$lib/stores/companies.svelte';
  import { getContacts, type Contact } from '$lib/api/contacts';
  import type { Company } from '$lib/api/companies';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import * as Avatar from '$lib/components/ui/avatar';
  import {
    ArrowLeft,
    Globe,
    Building,
    Calendar,
    Pencil,
    Trash,
  } from 'lucide-svelte';
  import CompanyDialog from '$lib/components/companies/CompanyDialog.svelte';

  let accountId = $derived(Number($page.params.accountId));
  let companyId = $derived(Number($page.params.id));

  // Use local state for the view to avoid conflict with list view state if we go back
  let company = $state<Company | null>(null);
  let contacts = $state<Contact[]>([]);
  let isLoading = $state(true);
  let showEditDialog = $state(false);

  async function loadData() {
    isLoading = true;
    try {
      // Fetch company via store to keep it in sync, or direct API
      const companyData = await companiesStore.fetchCompany(companyId);
      company = companyData;

      // Fetch contacts
      // Note: Assuming API supports filtering by company_id, which is standard in Chatwoot
      const contactsData = await getContacts(accountId, {
        company_id: companyId,
      });
      contacts = contactsData.data;
    } catch (error) {
      console.error('Failed to load company details', error);
    } finally {
      isLoading = false;
    }
  }

  onMount(() => {
    loadData();
  });

  function handleEdit() {
    showEditDialog = true;
  }

  async function handleUpdate(event: CustomEvent) {
    if (!company) return;
    const data = event.detail;
    await companiesStore.updateCompany(company.id, data);
    await loadData(); // Reload to get fresh data
    showEditDialog = false;
  }

  async function handleDelete() {
    if (!company) return;
    if (
      confirm(
        `Are you sure you want to delete "${company.name}"? This action cannot be undone.`
      )
    ) {
      await companiesStore.deleteCompany(company.id);
      goto(`/app/accounts/${accountId}/companies`);
    }
  }

  function goBack() {
    goto(`/app/accounts/${accountId}/companies`);
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

<div class="h-full flex flex-col bg-background">
  <!-- Header -->
  <div class="flex items-center justify-between px-6 py-4 border-b">
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" onclick={goBack}>
        <ArrowLeft class="h-4 w-4" />
      </Button>
      <div>
        <h1 class="text-xl font-medium">
          {company?.name || 'Company Details'}
        </h1>
        {#if company?.website}
          <a
            href={isValidUrl(company.website)
              ? company.website
              : `https://${company.website}`}
            target="_blank"
            class="text-sm text-blue-600 hover:underline flex items-center gap-1"
          >
            <Globe class="h-3 w-3" />
            {company.website}
          </a>
        {/if}
      </div>
    </div>
    <div class="flex items-center gap-2">
      <Button variant="outline" size="sm" onclick={handleEdit}>
        <Pencil class="h-4 w-4 mr-2" />
        Edit
      </Button>
      <Button variant="destructive" size="sm" onclick={handleDelete}>
        <Trash class="h-4 w-4 mr-2" />
        Delete
      </Button>
    </div>
  </div>

  <div class="flex-1 overflow-y-auto p-6">
    {#if isLoading}
      <div class="flex justify-center items-center h-full">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
        ></div>
      </div>
    {:else if company}
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Company Info -->
        <div class="lg:col-span-1 space-y-6">
          <Card.Root>
            <Card.Header>
              <Card.Title>About</Card.Title>
            </Card.Header>
            <Card.Content class="space-y-4">
              {#if company.description}
                <div>
                  <div class="text-sm font-medium text-muted-foreground mb-1">
                    Description
                  </div>
                  <p class="text-sm">{company.description}</p>
                </div>
              {/if}

              <div class="grid grid-cols-2 gap-4">
                {#if company.industry}
                  <div>
                    <div class="text-sm font-medium text-muted-foreground mb-1">
                      Industry
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                      <Building class="h-4 w-4" />
                      {company.industry}
                    </div>
                  </div>
                {/if}
                {#if company.size}
                  <div>
                    <div class="text-sm font-medium text-muted-foreground mb-1">
                      Size
                    </div>
                    <div class="text-sm">{company.size}</div>
                  </div>
                {/if}
              </div>

              <div class="pt-4 border-t">
                <div class="text-sm font-medium text-muted-foreground mb-1">
                  Created
                </div>
                <div class="flex items-center gap-2 text-sm">
                  <Calendar class="h-4 w-4" />
                  {formatDate(company.created_at)}
                </div>
              </div>
            </Card.Content>
          </Card.Root>
        </div>

        <!-- Right: Contacts -->
        <div class="lg:col-span-2">
          <Card.Root>
            <Card.Header>
              <Card.Title>Contacts ({contacts.length})</Card.Title>
            </Card.Header>
            <Card.Content>
              {#if contacts.length === 0}
                <div class="text-center py-8 text-muted-foreground">
                  No contacts associated with this company.
                </div>
              {:else}
                <div class="space-y-4">
                  {#each contacts as contact}
                    <div
                      class="flex items-center justify-between p-3 border rounded-lg hover:bg-muted/50 transition-colors"
                    >
                      <div class="flex items-center gap-3">
                        <Avatar.Root class="h-10 w-10">
                          {#if contact.avatarUrl}
                            <Avatar.Image
                              src={contact.avatarUrl}
                              alt={contact.name}
                            />
                          {:else}
                            <Avatar.Fallback
                              >{contact.name
                                .substring(0, 2)
                                .toUpperCase()}</Avatar.Fallback
                            >
                          {/if}
                        </Avatar.Root>
                        <div>
                          <div class="font-medium">{contact.name}</div>
                          <div class="text-sm text-muted-foreground">
                            {contact.email ||
                              contact.phoneNumber ||
                              'No contact info'}
                          </div>
                        </div>
                      </div>
                      <Button
                        variant="ghost"
                        size="sm"
                        href={`/app/accounts/${accountId}/contacts/${contact.id}`}
                      >
                        View
                      </Button>
                    </div>
                  {/each}
                </div>
              {/if}
            </Card.Content>
          </Card.Root>
        </div>
      </div>
    {:else}
      <div class="text-center py-20">
        <h2 class="text-xl font-semibold">Company not found</h2>
        <Button variant="link" onclick={goBack}>Go back to companies</Button>
      </div>
    {/if}
  </div>
</div>

<CompanyDialog
  bind:open={showEditDialog}
  mode="edit"
  {company}
  on:submit={handleUpdate}
/>
