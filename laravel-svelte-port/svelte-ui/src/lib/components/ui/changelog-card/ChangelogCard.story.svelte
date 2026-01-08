<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ChangelogCard } from './index';

  const changelogEntries = [
    {
      id: '1',
      title: 'AI-Powered Response Suggestions',
      description: 'Captain AI now suggests responses based on your conversation history and knowledge base.',
      date: '2024-12-20',
      type: 'feature' as const,
      version: '3.5.0',
    },
    {
      id: '2',
      title: 'Improved Dashboard Performance',
      description: 'Dashboard now loads 40% faster with optimized data fetching.',
      date: '2024-12-18',
      type: 'improvement' as const,
      version: '3.4.2',
    },
    {
      id: '3',
      title: 'Fixed Notification Delays',
      description: 'Resolved an issue where push notifications were delayed by up to 30 seconds.',
      date: '2024-12-15',
      type: 'fix' as const,
      version: '3.4.1',
    },
    {
      id: '4',
      title: 'API Authentication Changes',
      description: 'Updated API authentication to use OAuth 2.0. Previous API keys will be deprecated.',
      date: '2024-12-10',
      type: 'breaking' as const,
      version: '3.4.0',
    },
  ];
</script>

<Hst.Story title="Feedback/ChangelogCard" icon="lucide:history">
  <Hst.Variant title="Individual Cards">
    <div class="p-4 bg-background max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">What's New</h2>
      <ChangelogCard
        entries={changelogEntries}
        onclick={(entry) => console.log('Clicked:', entry.title)}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Stacked View">
    <div class="p-4 bg-background max-w-2xl">
      <h2 class="text-lg font-semibold mb-4">Recent Updates</h2>
      <ChangelogCard
        entries={changelogEntries}
        stacked
        onclick={(entry) => console.log('Clicked:', entry.title)}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Entry">
    <div class="p-4 bg-background max-w-2xl">
      <ChangelogCard
        entries={[changelogEntries[0]]}
        onclick={(entry) => console.log('Clicked:', entry.title)}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
