<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { CategoryCard } from './index';

  const categories = [
    {
      id: 1,
      name: 'Getting Started',
      slug: 'getting-started',
      icon: '🚀',
      description: 'Learn the basics of setting up and using Chatwoot',
      articlesCount: 12,
    },
    {
      id: 2,
      name: 'Integrations',
      slug: 'integrations',
      icon: '🔗',
      description: 'Connect Chatwoot with your favorite tools and platforms',
      articlesCount: 8,
    },
    {
      id: 3,
      name: 'API Documentation',
      slug: 'api-documentation',
      icon: '📚',
      description: 'Complete API reference for developers',
      articlesCount: 25,
    },
    {
      id: 4,
      name: 'Troubleshooting',
      slug: 'troubleshooting',
      icon: '🔧',
      description: 'Common issues and their solutions',
      articlesCount: 15,
    },
  ];
</script>

<Hst.Story title="HelpCenter/CategoryCard" icon="lucide:folder">
  <Hst.Variant title="Category Grid">
    <div class="grid grid-cols-2 gap-4 p-4 bg-background">
      {#each categories as category}
        <CategoryCard
          id={category.id}
          name={category.name}
          slug={category.slug}
          icon={category.icon}
          description={category.description}
          articlesCount={category.articlesCount}
          onclick={() => console.log('Clicked category:', category.slug)}
        />
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Single Category">
    <div class="p-4 bg-background max-w-md">
      <CategoryCard
        id={1}
        name="Automation"
        slug="automation"
        icon="⚙️"
        description="Set up automated workflows to save time and improve efficiency"
        articlesCount={18}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Without Icon">
    <div class="p-4 bg-background max-w-md">
      <CategoryCard
        id={1}
        name="General"
        slug="general"
        description="General information and FAQs"
        articlesCount={5}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty Category">
    <div class="p-4 bg-background max-w-md">
      <CategoryCard
        id={1}
        name="New Category"
        slug="new-category"
        icon="📂"
        description="This category is empty"
        articlesCount={0}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
