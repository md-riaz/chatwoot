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

  // Derive contact from contact (Laravel), meta (Rails), sender, or contactId
  const contact = $derived(
    conversation?.contact ||
      conversation?.meta?.sender ||
      (conversation as any)?.sender ||
      (conversation?.contactId
        ? $state
            .snapshot(contactsStore.allContacts)
            .find((c: any) => c.id == conversation.contactId)
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
    const assigneeId = value === 'none' ? null : parseInt(value);
    await conversationsStore.assignAgent(conversation.id, assigneeId);
  }

  async function handleTeamChange(value: string) {
    if (!conversation) return;
    selectedTeamId = value;
    const teamId = value === 'none' ? null : parseInt(value);
    await conversationsStore.assignTeam(conversation.id, teamId);
  }

  async function handlePriorityChange(value: string) {
    if (!conversation) return;
    selectedPriority = value;
    const priority = value === 'none' ? null : (value as any);
    await conversationsStore.updatePriority(conversation.id, priority);
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
          ? new Date(conversation.createdAt).toLocaleString()
          : 'Unknown',
        icon: Clock,
      },
      {
        label: 'Browser',
        value: meta.additionalAttributes?.browser?.browserName || 'Unknown',
        icon: Monitor,
      },
      {
        label: 'OS',
        value: meta.additionalAttributes?.browser?.platformName || 'Unknown',
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
      <div class="p-5 space-y-8">
        <!-- Contact Info Section -->
        <div class="flex flex-col gap-5">
          <!-- Profile Header -->
          <div class="flex flex-col items-start gap-4">
            <div class="flex items-center gap-4 w-full">
              <Avatar.Root
                class="h-14 w-14 rounded-full border-2 border-slate-50 shadow-sm shrink-0"
              >
                <Avatar.Image
                  src={contact?.avatarUrl || contact?.thumbnail}
                  alt={contact?.name}
                />
                <Avatar.Fallback
                  class="text-xl bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold"
                  >{contact?.name?.charAt(0) || '?'}</Avatar.Fallback
                >
              </Avatar.Root>

              <div class="flex flex-col min-w-0 flex-1">
                <h3
                  class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate leading-tight"
                >
                  {contact?.name || 'Unknown Contact'}
                </h3>
                {#if contact?.email}
                  <p
                    class="text-[13px] text-slate-500 dark:text-slate-400 truncate mt-0.5"
                  >
                    {contact.email}
                  </p>
                {/if}
              </div>
            </div>

            <!-- Description -->
            {#if contact?.description}
              <p
                class="text-[13px] text-slate-600 dark:text-slate-400 leading-relaxed italic"
              >
                "{contact.description}"
              </p>
            {/if}

            <!-- Contact Details Rows -->
            <div class="w-full space-y-2.5">
              {#if contact?.phoneNumber}
                <div
                  class="flex items-center gap-3 text-[13px] text-slate-600 dark:text-slate-300"
                >
                  <Phone class="h-4 w-4 text-slate-400 shrink-0" />
                  <span class="font-medium">{contact.phoneNumber}</span>
                </div>
              {/if}
              {#if contact?.location}
                <div
                  class="flex items-center gap-3 text-[13px] text-slate-600 dark:text-slate-300"
                >
                  <MapPin class="h-4 w-4 text-slate-400 shrink-0" />
                  <span class="font-medium">{contact.location}</span>
                </div>
              {/if}
              {#if contact?.companyName}
                <div
                  class="flex items-center gap-3 text-[13px] text-slate-600 dark:text-slate-300"
                >
                  <Building class="h-4 w-4 text-slate-400 shrink-0" />
                  <span class="font-medium">{contact.companyName}</span>
                </div>
              {/if}
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 pt-1 w-full flex-wrap">
              <Button
                variant="secondary"
                size="sm"
                class="h-8 flex-1 gap-2 text-[11px] font-bold uppercase tracking-wider"
              >
                <MessageSquarePlus class="h-3.5 w-3.5" />
                Message
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8 text-slate-500"
              >
                <PhoneCall class="h-3.5 w-3.5" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                class="h-8 w-8 text-slate-500"
              >
                <Pencil class="h-3.5 w-3.5" />
              </Button>
            </div>
          </div>
        </div>

        <Separator class="bg-slate-100 dark:bg-slate-800" />

        <!-- Conversation Actions -->
        <Collapsible.Root bind:open={isActionsOpen}>
          <div
            class="flex items-center justify-between group cursor-pointer"
            onclick={() => (isActionsOpen = !isActionsOpen)}
            onkeydown={e =>
              e.key === 'Enter' && (isActionsOpen = !isActionsOpen)}
            role="button"
            tabindex="0"
          >
            <span
              class="text-[11px] font-bold tracking-widest uppercase text-slate-500/80 dark:text-slate-400/80"
            >
              Conversation Actions
            </span>
            <Button
              variant="ghost"
              size="sm"
              class="h-6 w-6 p-0 opacity-50 group-hover:opacity-100"
            >
              {#if isActionsOpen}
                <ChevronDown class="h-3.5 w-3.5" />
              {:else}
                <ChevronRight class="h-3.5 w-3.5" />
              {/if}
            </Button>
          </div>
          <Collapsible.Content class="space-y-4 pt-4">
            <!-- Agent Select -->
            <div class="space-y-2">
              <span
                class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter"
                >Assigned Agent</span
              >
              <Select.Root
                type="single"
                bind:value={selectedAgentId}
                onValueChange={handleAgentChange}
              >
                <Select.Trigger
                  class="w-full h-10 text-[13px] font-medium bg-slate-50/50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 shadow-none"
                >
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
            <div class="space-y-2">
              <span
                class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter"
                >Assigned Team</span
              >
              <Select.Root
                type="single"
                bind:value={selectedTeamId}
                onValueChange={handleTeamChange}
              >
                <Select.Trigger
                  class="w-full h-10 text-[13px] font-medium bg-slate-50/50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 shadow-none"
                >
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
            <div class="space-y-2">
              <span
                class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter"
                >Priority</span
              >
              <Select.Root
                type="single"
                bind:value={selectedPriority}
                onValueChange={handlePriorityChange}
              >
                <Select.Trigger
                  class="w-full h-10 text-[13px] font-medium bg-slate-50/50 dark:bg-slate-900/50 border-slate-200 dark:border-slate-800 shadow-none"
                >
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

        <Separator class="bg-slate-100 dark:bg-slate-800" />

        <!-- Conversation Labels -->
        <Collapsible.Root bind:open={isLabelsOpen}>
          <div
            class="flex items-center justify-between group cursor-pointer"
            onclick={() => (isLabelsOpen = !isLabelsOpen)}
            onkeydown={e => e.key === 'Enter' && (isLabelsOpen = !isLabelsOpen)}
            role="button"
            tabindex="0"
          >
            <span
              class="text-[11px] font-bold tracking-widest uppercase text-slate-500/80 dark:text-slate-400/80"
            >
              Conversation Labels
            </span>
            <Button
              variant="ghost"
              size="sm"
              class="h-6 w-6 p-0 opacity-50 group-hover:opacity-100"
            >
              {#if isLabelsOpen}
                <ChevronDown class="h-3.5 w-3.5" />
              {:else}
                <ChevronRight class="h-3.5 w-3.5" />
              {/if}
            </Button>
          </div>

          <Collapsible.Content class="pt-4">
            <div class="flex flex-wrap gap-2 mb-4">
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
                <span class="text-sm text-slate-400 italic px-1"
                  >No labels added</span
                >
              {/if}
            </div>

            <Button
              variant="outline"
              size="sm"
              class="w-full gap-2 text-xs font-semibold text-slate-500 border-dashed border-2 hover:border-primary hover:text-primary transition-all"
            >
              <Plus class="h-3.5 w-3.5" /> Add Labels
            </Button>
          </Collapsible.Content>
        </Collapsible.Root>

        <Separator class="bg-slate-100 dark:bg-slate-800" />

        <!-- Conversation Information -->
        <Collapsible.Root bind:open={isInfoOpen}>
          <div
            class="flex items-center justify-between group cursor-pointer"
            onclick={() => (isInfoOpen = !isInfoOpen)}
            onkeydown={e => e.key === 'Enter' && (isInfoOpen = !isInfoOpen)}
            role="button"
            tabindex="0"
          >
            <span
              class="text-[11px] font-bold tracking-widest uppercase text-slate-500/80 dark:text-slate-400/80"
            >
              Conversation Information
            </span>
            <Button
              variant="ghost"
              size="sm"
              class="h-6 w-6 p-0 opacity-50 group-hover:opacity-100"
            >
              {#if isInfoOpen}
                <ChevronDown class="h-3.5 w-3.5" />
              {:else}
                <ChevronRight class="h-3.5 w-3.5" />
              {/if}
            </Button>
          </div>

          <Collapsible.Content class="pt-4 space-y-4">
            {#each getMetadata() as item}
              <div class="flex items-start gap-4">
                <div
                  class="mt-1 flex items-center justify-center h-7 w-7 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 shrink-0"
                >
                  <item.icon class="h-3.5 w-3.5 text-slate-400" />
                </div>
                <div class="flex-1 min-w-0">
                  <p
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mb-0.5"
                  >
                    {item.label}
                  </p>
                  <p
                    class="truncate text-[13px] font-medium text-slate-700 dark:text-slate-300"
                  >
                    {item.value}
                  </p>
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
