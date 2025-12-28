<script lang="ts">
  import { Button } from '../../../button/index.js';
  import { Input } from '../../../input/index.js';
  import { Badge } from '../../../badge/index.js';
  import { Card } from '../../../card/index.js';
  import { Avatar } from '../../../avatar/index.js';
  import { Checkbox } from '../../../checkbox/index.js';
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../select/index.js';
  import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../../../table/index.js';
  import { Search, Plus, Mail, Phone, Tag, Download, Filter } from 'lucide-svelte';

  export let contacts: Contact[] = [];
  export let searchQuery: string = '';
  export let selectedTag: string = 'all';
  export let sortBy: 'name' | 'email' | 'created' | 'lastActivity' = 'name';
  export let sortOrder: 'asc' | 'desc' = 'asc';
  export let currentPage: number = 1;
  export let itemsPerPage: number = 15;
  export let onContactSelect: (id: string) => void = () => {};
  export let onContactCreate: () => void = () => {};
  export let onBulkAction: (action: string, ids: string[]) => void = () => {};
  export let onExport: () => void = () => {};
  export let onFilterOpen: () => void = () => {};

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

  let selectedContacts: Set<string> = new Set();

  $: filteredContacts = contacts
    .filter((contact) => {
      const matchesSearch =
        searchQuery === '' ||
        contact.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        contact.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
        contact.company?.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesTag =
        selectedTag === 'all' || contact.tags.includes(selectedTag);
      return matchesSearch && matchesTag;
    })
    .sort((a, b) => {
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
    });

  $: paginatedContacts = filteredContacts.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  $: totalPages = Math.ceil(filteredContacts.length / itemsPerPage);
  $: allSelected = paginatedContacts.length > 0 && paginatedContacts.every(c => selectedContacts.has(c.id));

  function toggleAll() {
    if (allSelected) {
      paginatedContacts.forEach(c => selectedContacts.delete(c.id));
    } else {
      paginatedContacts.forEach(c => selectedContacts.add(c.id));
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
      <Button variant="outline" size="sm" on:click={onFilterOpen}>
        <Filter class="mr-2 h-4 w-4" />
        Filters
      </Button>
      <Select bind:value={selectedTag}>
        <SelectTrigger class="w-[140px]">
          <SelectValue placeholder="All Tags" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Tags</SelectItem>
          <SelectItem value="vip">VIP</SelectItem>
          <SelectItem value="lead">Lead</SelectItem>
          <SelectItem value="customer">Customer</SelectItem>
        </SelectContent>
      </Select>
    </div>
    <div class="flex items-center gap-2">
      {#if selectedContacts.size > 0}
        <span class="text-sm text-muted-foreground">
          {selectedContacts.size} selected
        </span>
        <Button variant="outline" size="sm" on:click={() => handleBulkAction('delete')}>
          Delete
        </Button>
        <Button variant="outline" size="sm" on:click={() => handleBulkAction('tag')}>
          Add Tag
        </Button>
      {/if}
      <Button variant="outline" size="sm" on:click={onExport}>
        <Download class="mr-2 h-4 w-4" />
        Export
      </Button>
      <Button on:click={onContactCreate}>
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
            <Checkbox checked={allSelected} on:click={toggleAll} />
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
            <TableRow class="cursor-pointer hover:bg-muted/50" on:click={() => onContactSelect(contact.id)}>
              <TableCell on:click|stopPropagation>
                <Checkbox
                  checked={selectedContacts.has(contact.id)}
                  on:click={() => toggleContact(contact.id)}
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
          on:click={() => (currentPage -= 1)}
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
          on:click={() => (currentPage += 1)}
        >
          Next
        </Button>
      </div>
    </div>
  {/if}
</div>
