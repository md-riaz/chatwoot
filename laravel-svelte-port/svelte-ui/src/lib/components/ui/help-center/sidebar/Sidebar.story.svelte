<script lang="ts">
  import { Sidebar } from './index.js';
  export let Hst: any;

  const categories = [
    { id: '1', name: 'Getting Started', articleCount: 12, subcategories: [
      { id: '1-1', name: 'Account Setup', articleCount: 5 },
      { id: '1-2', name: 'First Steps', articleCount: 7 }
    ]},
    { id: '2', name: 'Billing', articleCount: 8 },
    { id: '3', name: 'Features', articleCount: 24, subcategories: [
      { id: '3-1', name: 'Inbox Management', articleCount: 10 },
      { id: '3-2', name: 'Automation', articleCount: 8 },
      { id: '3-3', name: 'Reporting', articleCount: 6 }
    ]},
    { id: '4', name: 'Troubleshooting', articleCount: 15 }
  ];

  const popularArticles = [
    { id: 'a1', title: 'How to create your first inbox', views: 1234 },
    { id: 'a2', title: 'Setting up email forwarding', views: 987 },
    { id: 'a3', title: 'Understanding automation rules', views: 856 },
    { id: 'a4', title: 'Managing team permissions', views: 742 }
  ];
</script>

<Hst.Story title="Help Center/Sidebar" icon="lucide:sidebar">
  <Hst.Variant title="Full Sidebar">
    <Sidebar
      {categories}
      {popularArticles}
      onCategoryClick={(id) => console.log('Category:', id)}
      onArticleClick={(id) => console.log('Article:', id)}
      onSearch={(query) => console.log('Search:', query)}
    />
  </Hst.Variant>

  <Hst.Variant title="Categories Only">
    <Sidebar
      {categories}
      popularArticles={[]}
      onCategoryClick={(id) => console.log('Category:', id)}
      onArticleClick={(id) => console.log('Article:', id)}
      onSearch={(query) => console.log('Search:', query)}
    />
  </Hst.Variant>

  <Hst.Variant title="Minimal">
    <Sidebar
      categories={categories.slice(0, 2)}
      popularArticles={popularArticles.slice(0, 2)}
      onCategoryClick={(id) => console.log('Category:', id)}
      onArticleClick={(id) => console.log('Article:', id)}
      onSearch={(query) => console.log('Search:', query)}
    />
  </Hst.Variant>

  <Hst.Variant title="No Subcategories">
    <Sidebar
      categories={categories.map(c => ({ ...c, subcategories: undefined }))}
      {popularArticles}
      onCategoryClick={(id) => console.log('Category:', id)}
      onArticleClick={(id) => console.log('Article:', id)}
      onSearch={(query) => console.log('Search:', query)}
    />
  </Hst.Variant>

  <Hst.Variant title="With Many Popular">
    <Sidebar
      categories={categories.slice(0, 3)}
      popularArticles={[
        ...popularArticles,
        { id: 'a5', title: 'Advanced reporting features', views: 654 },
        { id: 'a6', title: 'API integration guide', views: 543 }
      ]}
      onCategoryClick={(id) => console.log('Category:', id)}
      onArticleClick={(id) => console.log('Article:', id)}
      onSearch={(query) => console.log('Search:', query)}
    />
  </Hst.Variant>
</Hst.Story>
