<script lang="ts">
  /**
   * ConversationSidebar - Right sidebar for conversation details
   * Features: Contact info, conversation actions (assignee, team, priority, labels), metadata
   */

  import { onMount } from 'svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';

  import * as Collapsible from '$lib/components/ui/collapsible';
  import * as Select from '$lib/components/ui/select';
  import * as Avatar from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Separator } from '$lib/components/ui/separator';
  import { Badge } from '$lib/components/ui/badge';
  import { ScrollArea } from '$lib/components/ui/scroll-area';
  import LabelPill from '$lib/components/ui/label-pill/label-pill.svelte';
  import {
    ChevronDown,
    ChevronRight,
    Mail,
    Phone,
    MapPin,
    Building,
    ExternalLink,
    Pencil,
    Trash2,
    Merge,
    PhoneCall,
    MessageSquarePlus,
    Clock,
    Monitor,
    Plus,
    User,
    Tag,
  } from 'lucide-svelte';

  interface Props {
    conversationId: number;
    class?: string;
  }

  let { conversationId, class: className = '' }: Props = $props();

  // Get conversation from store (use == for type safety)
  const conversation = $derived(
    conversationsStore.allConversations.find(c => c.id == conversationId)
  );

  // Derive contact from meta or contactId
  const contact = $derived(
    conversation?.meta?.sender ||
      (conversation?.contactId
        ? $state
            .snapshot(contactsStore.contacts)
            .find(c => c.id == conversation.contactId)
        : undefined)
  );

  // Store data
  const agents = $derived(agentsStore.allAgents);
  const teams = $derived(teamsStore.allTeams);
  const labels = $derived(labelsStore.allLabels);

  // Collapsible states
  let isActionsOpen = $state(true);
  let isLabelsOpen = $state(true);
  let isInfoOpen = $state(true);

  // Select values (bound to state for bits-ui v2)
  let selectedAgentId = $state<string>('none');
  let selectedTeamId = $state<string>('none');
  let selectedPriority = $state<string>('none');

  // Sync select values from conversation data
  $effect(() => {
    if (conversation) {
      selectedAgentId = conversation.assigneeId?.toString() || 'none';
      selectedTeamId = conversation.teamId?.toString() || 'none';
      selectedPriority = conversation.priority || 'none';
    }
  });

  // Fetch data on mount
  onMount(async () => {
    await Promise.all([
      agentsStore.fetchAgents(),
      teamsStore.fetchTeams(),
      labelsStore.fetchLabels(),
      // contactsStore.fetchContacts() // Might be too heavy, rely on conversation data for now
    ]);
  });

  // Handlers
  async function handleAgentChange(value: string) {
    if (!conversation) return;
    selectedAgentId = value;
    const assignee_id = value === 'none' ? null : parseInt(value);
    await conversationsStore.updateBox(conversation.id, { assignee_id });
  }

  async function handleTeamChange(value: string) {
    if (!conversation) return;
    selectedTeamId = value;
    const team_id = value === 'none' ? null : parseInt(value);
    await conversationsStore.updateBox(conversation.id, { team_id });
  }

  async function handlePriorityChange(value: string) {
    if (!conversation) return;
    selectedPriority = value;
    const priority = value === 'none' ? null : value;
    await conversationsStore.updateBox(conversation.id, { priority });
  }

  async function handleLabelRemove(label: string) {
    if (!conversation) return;
    await conversationsStore.updateLabels(
      conversation.id,
      (conversation.labels || []).filter(l => l !== label)
    );
  }

  async function handleAddLabel(label: string) {
    if (!conversation) return;
    await conversationsStore.updateLabels(conversation.id, [
      ...(conversation.labels || []),
      label,
    ]);
  }

  // Helper for metadata
  function getMetadata() {
    if (!conversation) return [];
    const meta = conversation as any;
    return [
      {
        label: 'Initiated at',
        value: conversation.createdAt
          ? new Date(Number(conversation.createdAt) * 1000).toLocaleString()
          : 'Unknown',
        icon: Clock,
      },
      {
        label: 'Browser',
        value: meta.additionalAttributes?.browser?.browser_name || 'Unknown',
        icon: Monitor,
      },
      {
        label: 'OS',
        value: meta.additionalAttributes?.browser?.platform_name || 'Unknown',
        icon: Monitor,
      },
      {
        label: 'Reference ID',
        value: conversation.id.toString(),
        icon: Tag,
      },
    ].filter(item => item.value !== 'Unknown');
  }
</script>

<div
  class={`h-full border-l border-border bg-background flex flex-col w-[340px] ${className}`}
>
  {#if conversation}
    <ScrollArea class="h-full">
      <div class="p-4 space-y-6">
        <!-- Contact Info Section -->
        <div class="flex flex-col gap-4">
          <!-- Profile Header -->
          <div class="flex flex-col items-start gap-3">
            <div class="flex items-center gap-3 w-full">
              <Avatar.Root class="h-12 w-12 rounded-full border border-border">
                <Avatar.Image
                  src={contact?.avatarUrl || contact?.thumbnail}
                  alt={contact?.name}
                />
                <Avatar.Fallback class="text-lg"
                  >{contact?.name?.charAt(0) || '?'}</Avatar.Fallback
                >
              </Avatar.Root>

              <div class="flex flex-col min-w-0 flex-1">
                <h3 class="text-base font-semibold text-foreground truncate">
                  {contact?.name || 'Unknown Contact'}
                </h3>
                <div class="flex items-center gap-2 text-muted-foreground">
                  {#if contact?.email}
                    <span class="text-xs truncate">{contact.email}</span>
                  {/if}
                  <a
                    href="#"
                    class="text-muted-foreground hover:text-foreground"
                  >
                    <ExternalLink class="h-3.5 w-3.5" />
                  </a>
                </div>
              </div>
            </div>

            <!-- Description -->
            {#if contact?.description}
              <p class="text-sm text-muted-foreground line-clamp-2">
                {contact.description}
              </p>
            {/if}

            <!-- Contact Details Rows -->
            <div class="w-full space-y-2 mt-2">
              {#if contact?.email}
                <div class="flex items-center gap-2 text-sm">
                  <div
                    class="flex items-center justify-center w-6 h-6 rounded bg-muted"
                  >
                    <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                  </div>
                  <span class="truncate flex-1" title={contact.email}
                    >{contact.email}</span
                  >
                </div>
              {/if}
              {#if contact?.phoneNumber}
                <div class="flex items-center gap-2 text-sm">
                  <div
                    class="flex items-center justify-center w-6 h-6 rounded bg-muted"
                  >
                    <Phone class="h-3.5 w-3.5 text-muted-foreground" />
                  </div>
                  <span>{contact.phoneNumber}</span>
                </div>
              {/if}
              {#if contact?.location}
                <div class="flex items-center gap-2 text-sm">
                  <div
                    class="flex items-center justify-center w-6 h-6 rounded bg-muted"
                  >
                    <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                  </div>
                  <span>{contact.location}</span>
                </div>
              {/if}
              {#if contact?.company_name}
                <div class="flex items-center gap-2 text-sm">
                  <div
                    class="flex items-center justify-center w-6 h-6 rounded bg-muted"
                  >
                    <Building class="h-3.5 w-3.5 text-muted-foreground" />
                  </div>
                  <span>{contact.company_name}</span>
                </div>
              {/if}
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-2">
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                title="New Message"
              >
                <MessageSquarePlus class="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                title="Call"
              >
                <PhoneCall class="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                title="Edit Contact"
              >
                <Pencil class="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                title="Merge Contact"
              >
                <Merge class="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8 text-destructive hover:text-destructive"
                title="Delete Contact"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        <Separator />

        <!-- Conversation Actions -->
        <Collapsible.Root bind:open={isActionsOpen}>
          <div class="flex items-center justify-between mb-2">
            <span
              class="text-sm font-semibold tracking-wide uppercase text-muted-foreground"
            >
              Conversation Actions
            </span>
            <Collapsible.Trigger>
              {#snippet child({ props })}
                <Button
                  {...props}
                  variant="ghost"
                  size="sm"
                  class="h-6 w-6 p-0"
                >
                  {#if isActionsOpen}
                    <ChevronDown class="h-4 w-4" />
                  {:else}
                    <ChevronRight class="h-4 w-4" />
                  {/if}
                </Button>
              {/snippet}
            </Collapsible.Trigger>
          </div>
          <Collapsible.Content class="space-y-4 pt-1">
            <!-- Agent Select -->
            <div class="space-y-1.5">
              <span class="text-xs font-medium text-muted-foreground"
                >Assigned Agent</span
              >
              <Select.Root
                type="single"
                bind:value={selectedAgentId}
                onValueChange={handleAgentChange}
              >
                <Select.Trigger class="w-full h-9">
                  <span>
                    {#if selectedAgentId === 'none'}
                      Select Agent
                    {:else}
                      {agents.find(a => a.id.toString() === selectedAgentId)
                        ?.name || 'Unknown Agent'}
                    {/if}
                  </span>
                </Select.Trigger>
                <Select.Content>
                  <Select.Item value="none">None</Select.Item>
                  {#each agents as agent}
                    <Select.Item value={agent.id.toString()}
                      >{agent.name}</Select.Item
                    >
                  {/each}
                </Select.Content>
              </Select.Root>
            </div>

            <!-- Team Select -->
            <div class="space-y-1.5">
              <span class="text-xs font-medium text-muted-foreground"
                >Assigned Team</span
              >
              <Select.Root
                type="single"
                bind:value={selectedTeamId}
                onValueChange={handleTeamChange}
              >
                <Select.Trigger class="w-full h-9">
                  <span>
                    {#if selectedTeamId === 'none'}
                      Select Team
                    {:else}
                      {teams.find(t => t.id.toString() === selectedTeamId)
                        ?.name || 'Unknown Team'}
                    {/if}
                  </span>
                </Select.Trigger>
                <Select.Content>
                  <Select.Item value="none">None</Select.Item>
                  {#each teams as team}
                    <Select.Item value={team.id.toString()}
                      >{team.name}</Select.Item
                    >
                  {/each}
                </Select.Content>
              </Select.Root>
            </div>

            <!-- Priority Select -->
            <div class="space-y-1.5">
              <span class="text-xs font-medium text-muted-foreground"
                >Priority</span
              >
              <Select.Root
                type="single"
                bind:value={selectedPriority}
                onValueChange={handlePriorityChange}
              >
                <Select.Trigger class="w-full h-9">
                  <div class="flex items-center gap-2">
                    <span class="capitalize"
                      >{selectedPriority === 'none'
                        ? 'None'
                        : selectedPriority}</span
                    >
                  </div>
                </Select.Trigger>
                <Select.Content>
                  <Select.Item value="none">None</Select.Item>
                  <Select.Item value="low">Low</Select.Item>
                  <Select.Item value="medium">Medium</Select.Item>
                  <Select.Item value="high">High</Select.Item>
                  <Select.Item value="urgent">Urgent</Select.Item>
                </Select.Content>
              </Select.Root>
            </div>
          </Collapsible.Content>
        </Collapsible.Root>

        <Separator />

        <!-- Conversation Labels -->
        <Collapsible.Root bind:open={isLabelsOpen}>
          <div class="flex items-center justify-between mb-2">
            <span
              class="text-sm font-semibold tracking-wide uppercase text-muted-foreground"
            >
              Conversation Labels
            </span>
            <Collapsible.Trigger>
              {#snippet child({ props })}
                <Button
                  {...props}
                  variant="ghost"
                  size="sm"
                  class="h-6 w-6 p-0"
                >
                  {#if isLabelsOpen}
                    <ChevronDown class="h-4 w-4" />
                  {:else}
                    <ChevronRight class="h-4 w-4" />
                  {/if}
                </Button>
              {/snippet}
            </Collapsible.Trigger>
          </div>

          <Collapsible.Content class="pt-2">
            <div class="flex flex-wrap gap-2 mb-3">
              {#if conversation.labels && conversation.labels.length > 0}
                {#each conversation.labels as label}
                  <LabelPill
                    title={label}
                    color="#1f2937"
                    removable={true}
                    onRemove={() => handleLabelRemove(label)}
                  />
                {/each}
              {:else}
                <span class="text-sm text-muted-foreground italic"
                  >No labels added</span
                >
              {/if}
            </div>

            <Button variant="outline" size="sm" class="w-full gap-1">
              <Plus class="h-3.5 w-3.5" /> Start typing to search labels
            </Button>
          </Collapsible.Content>
        </Collapsible.Root>

        <Separator />

        <!-- Conversation Information -->
        <Collapsible.Root bind:open={isInfoOpen}>
          <div class="flex items-center justify-between mb-2">
            <span
              class="text-sm font-semibold tracking-wide uppercase text-muted-foreground"
            >
              Conversation Information
            </span>
            <Collapsible.Trigger>
              {#snippet child({ props })}
                <Button
                  {...props}
                  variant="ghost"
                  size="sm"
                  class="h-6 w-6 p-0"
                >
                  {#if isInfoOpen}
                    <ChevronDown class="h-4 w-4" />
                  {:else}
                    <ChevronRight class="h-4 w-4" />
                  {/if}
                </Button>
              {/snippet}
            </Collapsible.Trigger>
          </div>

          <Collapsible.Content class="pt-2 space-y-3">
            {#each getMetadata() as item}
              <div class="flex items-start gap-3 text-sm">
                <div class="mt-0.5 text-muted-foreground">
                  <item.icon class="h-4 w-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-muted-foreground">{item.label}</p>
                  <p class="truncate font-medium">{item.value}</p>
                </div>
              </div>
            {/each}
          </Collapsible.Content>
        </Collapsible.Root>
      </div>
    </ScrollArea>
  {:else}
    <div class="flex items-center justify-center h-full text-muted-foreground">
      <p>Select a conversation</p>
    </div>
  {/if}
</div>
