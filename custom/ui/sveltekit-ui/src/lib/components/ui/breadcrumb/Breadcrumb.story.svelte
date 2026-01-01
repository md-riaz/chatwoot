<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Breadcrumb } from './index';

  const singleItem = [{ label: 'Home', link: '#' }];
  
  const twoItems = [
    { label: 'Home', link: '#' },
    { label: 'Categories', link: '#' },
  ];
  
  const threeItems = [
    { label: 'Home', link: '#' },
    { label: 'Categories', link: '#' },
    { label: 'Marketing', count: 6, emoji: '📊' },
  ];
  
  const longBreadcrumb = [
    { label: 'Home', link: '#' },
    { label: 'Categories', link: '#', emoji: '📁' },
    { label: 'Marketing', link: '#' },
    { label: 'Digital', link: '#', emoji: '💻' },
    { label: 'Social Media', count: 12, emoji: '📱' },
  ];
</script>

<Hst.Story title="Navigation/Breadcrumb" icon="lucide:navigation">
  <Hst.Variant title="Single Item">
    <div class="w-full p-4 bg-background">
      <Breadcrumb items={singleItem} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Two Items">
    <div class="w-full p-4 bg-background">
      <Breadcrumb items={twoItems} />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Three Items with Count">
    <div class="w-full p-4 bg-background">
      <Breadcrumb items={threeItems} countLabel="articles" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Long Breadcrumb">
    <div class="w-full p-4 bg-background">
      <Breadcrumb items={longBreadcrumb} countLabel="articles" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="RTL Support">
    <div dir="rtl">
      <div class="w-full p-4 bg-background">
        <Breadcrumb items={threeItems} countLabel="مقالات" />
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
