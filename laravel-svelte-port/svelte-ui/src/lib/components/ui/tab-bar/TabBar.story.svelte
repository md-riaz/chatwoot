<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { TabBar } from './index';

  const defaultTabs = [
    { label: 'All articles', count: 24 },
    { label: 'Mine', count: 13 },
    { label: 'Draft', count: 5 },
    { label: 'Archived', count: 11 },
    { label: 'Unpublished', count: 23 },
    { label: 'Published', count: 100 },
    { label: 'Scheduled', count: 49 },
  ];

  const customTabs = [
    { label: 'Active', count: 10 },
    { label: 'Pending', count: 5 },
    { label: 'Completed', count: 15 },
  ];

  const noCountTabs = [
    { label: 'Tab 1' },
    { label: 'Tab 2' },
    { label: 'Tab 3' },
  ];

  const singleTab = [{ label: 'Single Tab', count: 42 }];

  function handleTabChanged(tab: { label: string }, index: number) {
    console.log('Tab changed:', tab.label, 'Index:', index);
  }
</script>

<Hst.Story title="Navigation/TabBar" icon="lucide:layout-list">
  <Hst.Variant title="Default">
    <div class="p-4 bg-background">
      <TabBar tabs={defaultTabs} onTabChange={handleTabChanged} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Tabs with Initial Tab">
    <div class="p-4 bg-background">
      <TabBar
        tabs={customTabs}
        initialActiveTab={1}
        onTabChange={handleTabChanged}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="No Counts">
    <div class="p-4 bg-background">
      <TabBar tabs={noCountTabs} onTabChange={handleTabChanged} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Tab">
    <div class="p-4 bg-background">
      <TabBar tabs={singleTab} onTabChange={handleTabChanged} />
    </div>
  </Hst.Variant>
</Hst.Story>
