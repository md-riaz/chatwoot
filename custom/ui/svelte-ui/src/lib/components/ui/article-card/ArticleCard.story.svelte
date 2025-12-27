<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { ArticleCard } from './index';

  const articles = [
    {
      id: 1,
      title: "How to get an SSL certificate for your Help Center's custom domain",
      status: 'draft',
      updatedAt: 1729048936,
      author: {
        name: 'John',
        thumbnail: 'https://i.pravatar.cc/300?u=1',
      },
      category: {
        title: 'Marketing',
        slug: 'marketing',
        icon: '📈',
      },
      views: 400,
    },
    {
      id: 2,
      title: 'Setting up your first Help Center portal',
      status: '',
      updatedAt: 1729048936,
      author: {
        name: 'Jane',
        thumbnail: 'https://i.pravatar.cc/300?u=2',
      },
      category: {
        title: 'Development',
        slug: 'development',
        icon: '🛠️',
      },
      views: 1400,
    },
    {
      id: 3,
      title: 'Best practices for organizing your Help Center content',
      status: 'archived',
      updatedAt: 1729048936,
      author: {
        name: 'Fernando',
        thumbnail: 'https://i.pravatar.cc/300?u=3',
      },
      category: {
        title: 'Finance',
        slug: 'finance',
        icon: '💰',
      },
      views: 4300,
    },
  ];
</script>

<Hst.Story title="HelpCenter/ArticleCard" icon="lucide:file-text">
  <Hst.Variant title="Article Cards">
    <div class="flex flex-col gap-4 p-4 bg-background">
      {#each articles as article}
        <ArticleCard
          id={article.id}
          title={article.title}
          status={article.status}
          author={article.author}
          category={article.category}
          views={article.views}
          updatedAt={article.updatedAt}
          onclick={() => console.log('Clicked article:', article.id)}
        />
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Published Article">
    <div class="p-4 bg-background">
      <ArticleCard
        id={1}
        title="Getting started with Chatwoot"
        author={{ name: 'Admin' }}
        category={{ title: 'Getting Started', slug: 'getting-started', icon: '🚀' }}
        views={5280}
        updatedAt={Date.now() / 1000}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Draft Article">
    <div class="p-4 bg-background">
      <ArticleCard
        id={2}
        title="Advanced automation workflows"
        status="draft"
        author={{ name: 'Editor' }}
        category={{ title: 'Automation', slug: 'automation', icon: '⚙️' }}
        views={0}
        updatedAt={Date.now() / 1000}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
