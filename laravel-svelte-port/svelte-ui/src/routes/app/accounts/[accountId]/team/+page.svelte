<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import {
    Search,
    Mail,
    Shield,
    UserCheck,
    UserPlus,
    AlertCircle,
  } from '@lucide/svelte';
  import * as Card from '$lib/components/ui/card';
  import * as Avatar from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { agentsStore } from '$lib/stores/agents.svelte';

  let searchQuery = $state('');

  const accountId = $derived($page.params.accountId);
  const teamMembers = $derived(agentsStore.sortedAgents);
  const isLoading = $derived(agentsStore.isLoading);
  const error = $derived(agentsStore.error);

  const filteredMembers = $derived(() => {
    if (!searchQuery.trim()) return teamMembers;
    const query = searchQuery.toLowerCase();
    return teamMembers.filter(
      member =>
        member.name.toLowerCase().includes(query) ||
        member.email.toLowerCase().includes(query)
    );
  });

  function getInitials(name: string): string {
    return name
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  }

  function getRoleBadgeVariant(
    role: string
  ): 'default' | 'secondary' | 'outline' {
    return role === 'administrator' ? 'default' : 'secondary';
  }

  function getAvailabilityVariant(
    availability: string
  ): 'default' | 'secondary' | 'outline' {
    return availability === 'online' ? 'default' : 'outline';
  }

  onMount(async () => {
    await agentsStore.fetchAgents({ page: 1 });
  });
</script>

<div class="container mx-auto p-6">
  <div
    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div>
      <h1 class="text-3xl font-bold">Team Management</h1>
      <p class="text-muted-foreground">
        Manage your team members and their roles
      </p>
    </div>
    <Button onclick={() => goto(`/app/accounts/${accountId}/settings/agents`)}>
      <UserPlus class="mr-2 h-4 w-4" />
      Add Team Member
    </Button>
  </div>

  <div class="mb-6">
    <div class="relative">
      <Search
        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
      />
      <Input
        type="text"
        placeholder="Search by name or email..."
        bind:value={searchQuery}
        class="pl-10"
      />
    </div>
  </div>

  <div class="mb-4">
    <p class="text-sm text-muted-foreground">
      {filteredMembers().length}
      {filteredMembers().length === 1 ? 'member' : 'members'}
    </p>
  </div>

  {#if isLoading}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {#each Array(6) as _}
        <Card.Root
          ><Card.Content class="p-6"
            ><Skeleton class="h-24 w-full" /></Card.Content
          ></Card.Root
        >
      {/each}
    </div>
  {:else if error}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <AlertCircle class="mb-4 h-12 w-12 text-destructive" />
        <h3 class="mb-2 text-lg font-semibold">Unable to load team members</h3>
        <p class="mb-4 text-sm text-muted-foreground">{error}</p>
        <Button
          variant="outline"
          onclick={() => agentsStore.fetchAgents({ page: 1 })}>Retry</Button
        >
      </Card.Content>
    </Card.Root>
  {:else if filteredMembers().length === 0}
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <UserCheck class="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-2 text-lg font-semibold">No team members found</h3>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {#each filteredMembers() as member (member.id)}
        <Card.Root class="transition-shadow hover:shadow-md">
          <Card.Content class="p-6">
            <div class="flex items-start gap-4">
              <Avatar.Root class="h-12 w-12">
                {#if member.avatarUrl}
                  <Avatar.Image src={member.avatarUrl} alt={member.name} />
                {/if}
                <Avatar.Fallback>{getInitials(member.name)}</Avatar.Fallback>
              </Avatar.Root>

              <div class="flex-1 min-w-0">
                <h3 class="truncate font-semibold">{member.name}</h3>
                <div
                  class="mb-2 flex items-center gap-1 text-sm text-muted-foreground"
                >
                  <Mail class="h-3 w-3" />
                  <a
                    href="mailto:{member.email}"
                    class="truncate hover:underline">{member.email}</a
                  >
                </div>

                <div class="flex flex-wrap gap-2">
                  <Badge
                    variant={getRoleBadgeVariant(member.role)}
                    class="gap-1"
                  >
                    <Shield class="h-3 w-3" />
                    {member.role}
                  </Badge>
                  <Badge
                    variant={getAvailabilityVariant(member.availabilityStatus)}
                  >
                    {member.availabilityStatus}
                  </Badge>
                </div>
              </div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</div>
