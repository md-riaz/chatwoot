<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Card } from '$lib/components/ui/card';
  import { Avatar } from '$lib/components/ui/avatar';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import * as Select from '$lib/components/ui/select';
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '$lib/components/ui/table';
  import { Search, Plus, Mail, Phone, Tag, Download, Filter } from 'lucide-svelte';

  interface Contact {
    id: string;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    company?: string;
    tags: string[];
    lastActivity: string;
    createdAt: string;
    conversationCount: number;
    status: 'active' | 'inactive';
  }

  let { 
    contacts = [],
    searchQuery = '',
    selectedTag = 'all',
    sortBy = 'name' as 'name' | 'email' | 'created' | 'lastActivity',
    sortOrder = 'asc' as 'asc' | 'desc',
    currentPage = 1,
    itemsPerPage = 15,
    onContactSelect = (id: string) => {},
    onContactCreate = () => {},
    onBulkAction = (action: string, ids: string[]) => {},
    onExport = () => {},
    onFilterOpen = () => {}
  } = $props<{
    contacts?: Contact[];
    searchQuery?: string;
    selectedTag?: string;
    sortBy?: 'name' | 'email' | 'created' | 'lastActivity';
    sortOrder?: 'asc' | 'desc';
    currentPage?: number;
    itemsPerPage?: number;
    onContactSelect?: (id: string) => void;
    onContactCreate?: () => void;
    onBulkAction?: (action: string, ids: string[]) => void;
    onExport?: () => void;
    onFilterOpen?: () => void;
  }>();

  let selectedContacts: Set<string> = new Set();

  // Use $derived instead of $:
  const filteredContacts = $derived(contacts
    .filter((contact: Contact) => {
      const matchesSearch =
        searchQuery === '' ||
        contact.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        contact.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
        contact.company?.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesTag =
        selectedTag === 'all' || contact.tags.includes(selectedTag);
      return matchesSearch && matchesTag;
    })
    .sort((a: Contact, b: Contact) => {
      let comparison = 0;
      switch (sortBy) {
        case 'name':
          comparison = a.name.localeCompare(b.name);
          break;
        case 'email':
          comparison = a.email.localeCompare(b.email);
          break;
        case 'created':
          comparison = new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime();
          break;
        case 'lastActivity':
          comparison = new Date(a.lastActivity).getTime() - new Date(b.lastActivity).getTime();
          break;
      }
      return sortOrder === 'asc' ? comparison : -comparison;
    }));

  const paginatedContacts = $derived(filteredContacts.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  ));

  $: totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
  $: allSelected = paginatedContacts.length > 0 && paginatedContacts.every((c: Contact) => selectedContacts.has(c.id));

  function toggleAll() {
    if (allSelected) {
      paginatedContacts.forEach((c: Contact) => selectedContacts.delete(c.id));
    } else {
      paginatedContacts.forEach((c: Contact) => selectedContacts.add(c.id));
    }
    selectedContacts = selectedContacts;
  }

  function toggleContact(id: string) {
    if (selectedContacts.has(id)) {
      selectedContacts.delete(id);
    } else {
      selectedContacts.add(id);
    }
    selectedContacts = selectedContacts;
  }

  function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(date);
  }

  function getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  }

  function handleBulkAction(action: string) {
    onBulkAction(action, Array.from(selectedContacts));
    selectedContacts.clear();
    selectedContacts = selectedContacts;
  }
</script>

<div class="flex flex-col h-full space-y-4">
  <!-- Header with Search and Actions -->
  <div class="flex items-center justify-between gap-4">
    <div class="flex items-center flex-1 gap-2">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          type="search"
          placeholder="Search contacts..."
          bind:value={searchQuery}
          class="pl-9"
        />
      </div>
      <Button variant="outline" size="sm" onclick={(e: MouseEvent) => onFilterOpen()}>
        <Filter class="mr-2 h-4 w-4" />
        Filters
      </Button>
      <Select.Root bind:value={selectedTag} type="single">
        <Select.Trigger class="w-[140px]">
          <Select.Value placeholder="All Tags" />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="all">All Tags</Select.Item>
          <Select.Item value="vip">VIP</Select.Item>
          <Select.Item value="lead">Lead</Select.Item>
          <Select.Item value="customer">Customer</Select.Item>
        </Select.Content>
      </Select.Root>
    </div>
    <div class="flex items-center gap-2">
      {#if selectedContacts.size > 0}
        <span class="text-sm text-muted-foreground">
          {selectedContacts.size} selected
        </span>
        <Button variant="outline" size="sm" onclick={(e: MouseEvent) => handleBulkAction('delete')}>
          Delete
        </Button>
        <Button variant="outline" size="sm" onclick={(e: MouseEvent) => handleBulkAction('tag')}>
          Add Tag
        </Button>
      {/if}
      <Button variant="outline" size="sm" onclick={(e: MouseEvent) => onExport()}>
        <Download class="mr-2 h-4 w-4" />
        Export
      </Button>
      <Button onclick={(e: MouseEvent) => onContactCreate()}>
        <Plus class="mr-2 h-4 w-4" />
        New Contact
      </Button>
    </div>
  </div>

  <!-- Contacts Table -->
  <div class="flex-1 overflow-auto border rounded-lg">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-12">
            <Checkbox checked={allSelected} onclick={(e: MouseEvent) => toggleAll()} />
          </TableHead>
          <TableHead>Contact</TableHead>
          <TableHead>Email & Phone</TableHead>
          <TableHead>Company</TableHead>
          <TableHead>Tags</TableHead>
          <TableHead>Conversations</TableHead>
          <TableHead>Last Activity</TableHead>
          <TableHead>Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {#if paginatedContacts.length === 0}
          <TableRow>
            <TableCell colspan="8" class="h-24 text-center">
              <div class="flex flex-col items-center justify-center text-muted-foreground">
                <p class="text-sm font-medium">No contacts found</p>
                <p class="text-xs">Try adjusting your search or filters</p>
              </div>
            </TableCell>
          </TableRow>
        {:else}
          {#each paginatedContacts as contact}
            <TableRow class="cursor-pointer hover:bg-muted/50" onclick={(e: MouseEvent) => onContactSelect(contact.id)}>
              <TableCell onclick={(e: MouseEvent) => { e.stopPropagation(); toggleContact(contact.id); }}>
                <Checkbox
                  checked={selectedContacts.has(contact.id)}
                  onclick={(e: MouseEvent) => toggleContact(contact.id)}
                />
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <Avatar>
                    {#if contact.avatar}
                      <img src={contact.avatar} alt={contact.name} />
                    {:else}
                      <div class="flex h-full w-full items-center justify-center bg-primary text-primary-foreground">
                        {getInitials(contact.name)}
                      </div>
                    {/if}
                  </Avatar>
                  <div>
                    <div class="font-medium">{contact.name}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="space-y-1">
                  <div class="flex items-center gap-1 text-sm">
                    <Mail class="h-3 w-3 text-muted-foreground" />
                    <span>{contact.email}</span>
                  </div>
                  {#if contact.phone}
                    <div class="flex items-center gap-1 text-sm text-muted-foreground">
                      <Phone class="h-3 w-3" />
                      <span>{contact.phone}</span>
                    </div>
                  {/if}
                </div>
              </TableCell>
              <TableCell>
                <span class="text-sm">{contact.company || '-'}</span>
              </TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-1">
                  {#each contact.tags.slice(0, 2) as tag}
                    <Badge variant="secondary" class="text-xs">
                      {tag}
                    </Badge>
                  {/each}
                  {#if contact.tags.length > 2}
                    <Badge variant="outline" class="text-xs">
                      +{contact.tags.length - 2}
                    </Badge>
                  {/if}
                </div>
              </TableCell>
              <TableCell>
                <span class="text-sm">{contact.conversationCount}</span>
              </TableCell>
              <TableCell>
                <span class="text-sm text-muted-foreground">{formatDate(contact.lastActivity)}</span>
              </TableCell>
              <TableCell>
                <Badge variant={contact.status === 'active' ? 'default' : 'secondary'}>
                  {contact.status}
                </Badge>
              </TableCell>
            </TableRow>
          {/each}
        {/if}
      </TableBody>
    </Table>
  </div>

  <!-- Pagination -->
  {#if totalPages > 1}
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Showing {(currentPage - 1) * itemsPerPage + 1} to {Math.min(
          currentPage * itemsPerPage,
          filteredContacts.length
        )} of {filteredContacts.length} contacts
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === 1}
          onclick={(e: MouseEvent) => (currentPage -= 1)}
        >
          Previous
        </Button>
        <span class="text-sm px-4">
          Page {currentPage} of {totalPages}
        </span>
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === totalPages}
          onclick={(e: MouseEvent) => (currentPage += 1)}
        >
          Next
        </Button>
      </div>
    </div>
  {/if}
</div>