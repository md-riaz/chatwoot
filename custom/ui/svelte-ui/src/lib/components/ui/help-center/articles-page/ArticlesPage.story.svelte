<script lang="ts">
  import { ArticlesPage } from './index.js';
  export let Hst: any;

  const sampleArticles = [
    {
      id: '1',
      title: 'Getting Started with Chatwoot',
      description: 'Learn how to set up and configure your Chatwoot account for the first time.',
      category: 'setup',
      author: 'John Doe',
      status: 'published' as const,
      views: 1250,
      createdAt: '2024-01-15T10:00:00Z',
      updatedAt: '2024-01-20T14:30:00Z',
    },
    {
      id: '2',
      title: 'Managing Conversations',
      description: 'Best practices for handling customer conversations efficiently.',
      category: 'guides',
      author: 'Jane Smith',
      status: 'published' as const,
      views: 890,
      createdAt: '2024-01-18T09:00:00Z',
      updatedAt: '2024-01-22T11:00:00Z',
    },
    {
      id: '3',
      title: 'Webhook Configuration',
      description: 'Set up webhooks to integrate Chatwoot with external services.',
      category: 'integrations',
      author: 'Mike Johnson',
      status: 'draft' as const,
      views: 45,
      createdAt: '2024-01-25T08:00:00Z',
      updatedAt: '2024-01-25T16:00:00Z',
    },
    {
      id: '4',
      title: 'Team Management',
      description: 'Add team members, assign roles, and manage permissions.',
      category: 'setup',
      author: 'Sarah Williams',
      status: 'published' as const,
      views: 670,
      createdAt: '2024-01-10T12:00:00Z',
      updatedAt: '2024-01-19T10:00:00Z',
    },
    {
      id: '5',
      title: 'Old Migration Guide',
      description: 'Instructions for migrating from version 1.x to 2.x (archived).',
      category: 'guides',
      author: 'John Doe',
      status: 'archived' as const,
      views: 234,
      createdAt: '2023-06-01T10:00:00Z',
      updatedAt: '2023-12-15T14:00:00Z',
    },
  ];

  const sampleCategories = [
    { id: 'setup', name: 'Setup & Configuration', slug: 'setup', count: 2 },
    { id: 'guides', name: 'User Guides', slug: 'guides', count: 2 },
    { id: 'integrations', name: 'Integrations', slug: 'integrations', count: 1 },
  ];
</script>

<Hst.Story title="Help Center/Articles Page" icon="lucide:book-open">
  <Hst.Variant title="Default">
    <div class="h-[600px] p-4">
      <ArticlesPage
        articles={sampleArticles}
        categories={sampleCategories}
        on:articleSelect={(e) => console.log('Article selected:', e.detail)}
        on:articleCreate={() => console.log('Create article')}
        on:articleEdit={(e) => console.log('Edit article:', e.detail)}
        on:articleDelete={(e) => console.log('Delete article:', e.detail)}
        on:articlePreview={(e) => console.log('Preview article:', e.detail)}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="h-[600px] p-4">
      <ArticlesPage
        articles={[]}
        categories={sampleCategories}
        on:articleCreate={() => console.log('Create article')}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Filtered by Category">
    <div class="h-[600px] p-4">
      <ArticlesPage
        articles={sampleArticles}
        categories={sampleCategories}
        selectedCategory="setup"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Search">
    <div class="h-[600px] p-4">
      <ArticlesPage
        articles={sampleArticles}
        categories={sampleCategories}
        searchQuery="conversation"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sorted by Popularity">
    <div class="h-[600px] p-4">
      <ArticlesPage
        articles={sampleArticles}
        categories={sampleCategories}
        sortBy="popularity"
      />
    </div>
  </Hst.Variant>
</Hst.Story>
