<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Switch } from '$lib/components/ui/switch';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as Card from '$lib/components/ui/card';
  import * as RadioGroup from '$lib/components/ui/radio-group';
  import { Loader2 } from 'lucide-svelte';

  // Constants matching Vue
  const ROUND_ROBIN = 'round_robin';
  const BALANCED = 'balanced';
  const EARLIEST_CREATED = 'earliest_created';
  const LONGEST_WAITING = 'longest_waiting';
  const DEFAULT_FAIR_DISTRIBUTION_LIMIT = 100;
  const DEFAULT_FAIR_DISTRIBUTION_WINDOW = 3600;

  interface FormData {
    name: string;
    description: string;
    enabled: boolean;
    assignmentOrder: string;
    conversationPriority: string;
    fairDistributionLimit: number;
    fairDistributionWindow: number;
  }

  let {
    mode = 'CREATE',
    initialData,
    isLoading = false,
    onsubmit,
  }: {
    mode?: 'CREATE' | 'EDIT';
    initialData?: Partial<FormData>;
    isLoading?: boolean;
    onsubmit?: (data: FormData) => void;
  } = $props();

  // Form state - initialized to defaults, $effect syncs from initialData
  let name = $state('');
  let description = $state('');
  let enabled = $state(false);
  let assignmentOrder = $state(ROUND_ROBIN);
  let conversationPriority = $state(EARLIEST_CREATED);
  let fairDistributionLimit = $state(DEFAULT_FAIR_DISTRIBUTION_LIMIT);
  let fairDistributionWindow = $state(DEFAULT_FAIR_DISTRIBUTION_WINDOW);

  // Update form when initialData changes (for edit mode)
  $effect(() => {
    if (initialData) {
      name = initialData.name ?? '';
      description = initialData.description ?? '';
      enabled = initialData.enabled ?? false;
      assignmentOrder = initialData.assignmentOrder ?? ROUND_ROBIN;
      conversationPriority =
        initialData.conversationPriority ?? EARLIEST_CREATED;
      fairDistributionLimit =
        initialData.fairDistributionLimit ?? DEFAULT_FAIR_DISTRIBUTION_LIMIT;
      fairDistributionWindow =
        initialData.fairDistributionWindow ?? DEFAULT_FAIR_DISTRIBUTION_WINDOW;
    }
  });

  let isValid = $derived(name.trim().length > 0);

  let buttonLabel = $derived(
    mode === 'CREATE' ? 'Create Policy' : 'Update Policy'
  );

  const assignmentOrderOptions = [
    {
      value: ROUND_ROBIN,
      label: 'Round Robin',
      description:
        'Distribute conversations evenly across available agents in order.',
    },
    {
      value: BALANCED,
      label: 'Balanced',
      description: 'Assign to the agent with the fewest active conversations.',
    },
  ];

  const priorityOptions = [
    {
      value: EARLIEST_CREATED,
      label: 'Earliest Created',
      description: 'Assign the oldest unassigned conversations first.',
    },
    {
      value: LONGEST_WAITING,
      label: 'Longest Waiting',
      description:
        'Assign conversations that have been waiting the longest for a reply.',
    },
  ];

  function handleSubmit(e: Event) {
    e.preventDefault();
    if (!isValid || isLoading) return;

    onsubmit?.({
      name: name.trim(),
      description: description.trim(),
      enabled,
      assignmentOrder,
      conversationPriority,
      fairDistributionLimit,
      fairDistributionWindow,
    });
  }

  export function resetForm() {
    name = '';
    description = '';
    enabled = false;
    assignmentOrder = ROUND_ROBIN;
    conversationPriority = EARLIEST_CREATED;
    fairDistributionLimit = DEFAULT_FAIR_DISTRIBUTION_LIMIT;
    fairDistributionWindow = DEFAULT_FAIR_DISTRIBUTION_WINDOW;
  }
</script>

<form onsubmit={handleSubmit} class="flex flex-col gap-6">
  <!-- Basic Info Section -->
  <Card.Root>
    <Card.Content class="pt-6 space-y-4">
      <div class="space-y-2">
        <Label for="policy-name">Name</Label>
        <Input
          id="policy-name"
          bind:value={name}
          placeholder="Enter policy name"
          required
        />
      </div>

      <div class="space-y-2">
        <Label for="policy-description">Description</Label>
        <Textarea
          id="policy-description"
          bind:value={description}
          placeholder="Enter a description for this policy"
          rows={3}
        />
      </div>

      {#if mode === 'EDIT'}
        <div class="flex items-center justify-between gap-4 pt-2">
          <div>
            <Label for="policy-enabled">Status</Label>
            <p class="text-sm text-muted-foreground">
              {enabled ? 'Active' : 'Inactive'}
            </p>
          </div>
          <Switch id="policy-enabled" bind:checked={enabled} />
        </div>
      {/if}
    </Card.Content>
  </Card.Root>

  <!-- Assignment Order Section -->
  <Card.Root>
    <Card.Header class="pb-3">
      <Card.Title class="text-base">Assignment Order</Card.Title>
      <Card.Description>
        How conversations should be distributed among agents.
      </Card.Description>
    </Card.Header>
    <Card.Content>
      <RadioGroup.Root
        bind:value={assignmentOrder}
        class="grid grid-cols-1 sm:grid-cols-2 gap-3"
      >
        {#each assignmentOrderOptions as option}
          <label
            class="flex items-start gap-3 rounded-lg border p-4 cursor-pointer transition-all duration-200 {assignmentOrder ===
            option.value
              ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
              : 'border-border hover:border-primary/30'}"
          >
            <RadioGroup.Item value={option.value} class="mt-0.5" />
            <div class="space-y-1">
              <span class="text-sm font-medium">{option.label}</span>
              <p class="text-xs text-muted-foreground">{option.description}</p>
            </div>
          </label>
        {/each}
      </RadioGroup.Root>
    </Card.Content>
  </Card.Root>

  <!-- Conversation Priority Section -->
  <Card.Root>
    <Card.Header class="pb-3">
      <Card.Title class="text-base">Conversation Priority</Card.Title>
      <Card.Description>
        How conversations should be prioritized for assignment.
      </Card.Description>
    </Card.Header>
    <Card.Content>
      <RadioGroup.Root
        bind:value={conversationPriority}
        class="grid grid-cols-1 sm:grid-cols-2 gap-3"
      >
        {#each priorityOptions as option}
          <label
            class="flex items-start gap-3 rounded-lg border p-4 cursor-pointer transition-all duration-200 {conversationPriority ===
            option.value
              ? 'border-primary bg-primary/5 ring-1 ring-primary/20'
              : 'border-border hover:border-primary/30'}"
          >
            <RadioGroup.Item value={option.value} class="mt-0.5" />
            <div class="space-y-1">
              <span class="text-sm font-medium">{option.label}</span>
              <p class="text-xs text-muted-foreground">{option.description}</p>
            </div>
          </label>
        {/each}
      </RadioGroup.Root>
    </Card.Content>
  </Card.Root>

  <!-- Fair Distribution Section -->
  <Card.Root>
    <Card.Header class="pb-3">
      <Card.Title class="text-base">Fair Distribution</Card.Title>
      <Card.Description>
        Control the maximum number of conversations assigned to an agent within
        a time window.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="fair-limit">Distribution Limit</Label>
          <Input
            id="fair-limit"
            type="number"
            bind:value={fairDistributionLimit}
            min={1}
          />
          <p class="text-xs text-muted-foreground">
            Maximum conversations per agent in the time window.
          </p>
        </div>
        <div class="space-y-2">
          <Label for="fair-window">Time Window (seconds)</Label>
          <Input
            id="fair-window"
            type="number"
            bind:value={fairDistributionWindow}
            min={1}
          />
          <p class="text-xs text-muted-foreground">
            Time window in seconds for the distribution limit.
          </p>
        </div>
      </div>
    </Card.Content>
  </Card.Root>

  <!-- Submit Button -->
  <div class="flex justify-start">
    <Button type="submit" disabled={!isValid || isLoading}>
      {#if isLoading}
        <Loader2 class="mr-2 h-4 w-4 animate-spin" />
      {/if}
      {buttonLabel}
    </Button>
  </div>
</form>
