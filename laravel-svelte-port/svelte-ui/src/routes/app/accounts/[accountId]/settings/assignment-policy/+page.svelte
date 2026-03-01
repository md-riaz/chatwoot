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

  let accountId = $derived($page.params.accountId);

  const featureCards = [
    {
      key: 'assignment',
      title: 'Agent Assignment Policy',
      description:
        'Configure how new conversations are automatically routed and distributed among your agents.',
      featureLabels: [
        'Round-robin and balanced assignment strategies',
        'Fair distribution limits across agents',
        'Inbox-level policy configuration',
      ],
    },
    {
      key: 'capacity',
      title: 'Agent Capacity Policy',
      description:
        'Set conversation capacity limits for agents to prevent overloading and ensure quality support.',
      featureLabels: [
        'Per-agent conversation capacity limits',
        'Exclusion rules for specific labels or older conversations',
        'Agent-level capacity management',
      ],
    },
  ];

  function handleCardClick(key: string) {
    goto(`/app/accounts/${accountId}/settings/assignment-policy/${key}`);
  }
</script>

<div class="flex flex-col w-full space-y-6">
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
        featureLabels={card.featureLabels}
        onclick={() => handleCardClick(card.key)}
      />
    {/each}
  </div>
</div>
