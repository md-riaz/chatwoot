<script lang="ts">
  /**
   * Assignment Policy Index Page
   * Shows two feature cards: Agent Assignment Policy & Agent Capacity Policy
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/assignmentPolicy/Index.vue
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';
  import AssignmentCard from '$lib/components/assignment-policy/AssignmentCard.svelte';
  import {
    ArrowUpCircle,
    Scale,
    Inbox,
    GlassWater,
    CircleMinus,
    UsersRound,
  } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId);

  const featureCards = [
    {
      key: 'assignment',
      title: 'Agent Assignment Policy',
      description:
        'Configure how new conversations are automatically routed and distributed among your agents.',
      features: [
        {
          icon: ArrowUpCircle,
          label: 'Round-robin and balanced assignment strategies',
        },
        {
          icon: Scale,
          label: 'Fair distribution limits across agents',
        },
        {
          icon: Inbox,
          label: 'Inbox-level policy configuration',
        },
      ],
    },
    {
      key: 'capacity',
      title: 'Agent Capacity Policy',
      description:
        'Set conversation capacity limits for agents to prevent overloading and ensure quality support.',
      features: [
        {
          icon: GlassWater,
          label: 'Per-agent conversation capacity limits',
        },
        {
          icon: CircleMinus,
          label: 'Exclusion rules for specific labels or older conversations',
        },
        {
          icon: UsersRound,
          label: 'Agent-level capacity management',
        },
      ],
    },
  ];

  function handleCardClick(key: string) {
    goto(`/app/accounts/${accountId}/settings/assignment-policy/${key}`);
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <BaseSettingsHeader title="Assignment Policy" />

  <p class="text-muted-foreground mb-8">
    Configure automatic conversation assignment and agent capacity management to
    optimize your team's workflow.
  </p>

  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {#each featureCards as card}
      <AssignmentCard
        title={card.title}
        description={card.description}
        features={card.features}
        onclick={() => handleCardClick(card.key)}
      />
    {/each}
  </div>
</div>
