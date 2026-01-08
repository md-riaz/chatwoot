<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { AssistantCard } from './index';

  const assistants = [
    {
      id: 1,
      name: 'Support Assistant',
      description: 'Helps answer common customer questions and provides first-level support.',
      updatedAt: '2024-12-15T10:00:00Z',
      createdAt: '2024-11-01T08:00:00Z',
    },
    {
      id: 2,
      name: 'Sales Assistant',
      description: 'Qualifies leads and answers product-related questions.',
      updatedAt: '2024-12-10T14:30:00Z',
      createdAt: '2024-10-15T09:00:00Z',
    },
    {
      id: 3,
      name: 'Onboarding Assistant',
      description: 'Guides new users through product setup and configuration.',
      createdAt: '2024-12-01T12:00:00Z',
    },
  ];
</script>

<Hst.Story title="Captain/AssistantCard" icon="lucide:bot">
  <Hst.Variant title="Assistant List">
    <div class="flex flex-col gap-4 p-4 bg-background max-w-2xl">
      {#each assistants as assistant}
        <AssistantCard
          id={assistant.id}
          name={assistant.name}
          description={assistant.description}
          updatedAt={assistant.updatedAt}
          createdAt={assistant.createdAt}
          onclick={() => console.log('Clicked assistant:', assistant.id)}
        />
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Assistant">
    <div class="p-4 bg-background max-w-2xl">
      <AssistantCard
        id={1}
        name="Customer Support Bot"
        description="An AI-powered assistant that handles customer inquiries 24/7, providing instant responses to frequently asked questions and escalating complex issues to human agents."
        updatedAt="2024-12-20T15:00:00Z"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="New Assistant">
    <div class="p-4 bg-background max-w-2xl">
      <AssistantCard
        id={1}
        name="New Assistant"
        description="Just created, no updates yet."
        createdAt="2024-12-25T10:00:00Z"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
