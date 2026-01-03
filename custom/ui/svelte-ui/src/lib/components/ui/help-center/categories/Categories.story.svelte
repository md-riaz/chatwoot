<script lang="ts">
  import { Categories } from './index.js';
  export let Hst: any;

  const sampleCategories = [
    {
      id: '1',
      name: 'Getting Started',
      description: 'Everything you need to know to get started',
      color: '#3B82F6',
      articleCount: 12,
      order: 1
    },
    {
      id: '2',
      name: 'Billing',
      description: 'Payment and subscription information',
      color: '#10B981',
      articleCount: 8,
      order: 2
    },
    {
      id: '3',
      name: 'Features',
      description: 'Learn about our features',
      color: '#F59E0B',
      articleCount: 24,
      order: 3
    },
    {
      id: '4',
      name: 'Troubleshooting',
      description: 'Common issues and solutions',
      color: '#EF4444',
      articleCount: 15,
      order: 4
    }
  ];
</script>

<Hst.Story title="Help Center/Categories" icon="lucide:folder">
  <Hst.Variant title="With Categories">
    <Categories
      categories={sampleCategories}
      onCreate={(cat) => console.log('Create:', cat)}
      onUpdate={(id, cat) => console.log('Update:', id, cat)}
      onDelete={(id) => console.log('Delete:', id)}
      onReorder={(cats) => console.log('Reorder:', cats)}
    />
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <Categories
      categories={[]}
      onCreate={(cat) => console.log('Create:', cat)}
      onUpdate={(id, cat) => console.log('Update:', id, cat)}
      onDelete={(id) => console.log('Delete:', id)}
      onReorder={(cats) => console.log('Reorder:', cats)}
    />
  </Hst.Variant>

  <Hst.Variant title="Single Category">
    <Categories
      categories={[sampleCategories[0]]}
      onCreate={(cat) => console.log('Create:', cat)}
      onUpdate={(id, cat) => console.log('Update:', id, cat)}
      onDelete={(id) => console.log('Delete:', id)}
      onReorder={(cats) => console.log('Reorder:', cats)}
    />
  </Hst.Variant>

  <Hst.Variant title="Many Categories">
    <Categories
      categories={[
        ...sampleCategories,
        {
          id: '5',
          name: 'API Documentation',
          description: 'API reference and guides',
          color: '#8B5CF6',
          articleCount: 32,
          order: 5
        },
        {
          id: '6',
          name: 'Integrations',
          description: 'Connect with other tools',
          color: '#EC4899',
          articleCount: 18,
          order: 6
        }
      ]}
      onCreate={(cat) => console.log('Create:', cat)}
      onUpdate={(id, cat) => console.log('Update:', id, cat)}
      onDelete={(id) => console.log('Delete:', id)}
      onReorder={(cats) => console.log('Reorder:', cats)}
    />
  </Hst.Variant>

  <Hst.Variant title="Creating New">
    <div>
      <Categories
        categories={sampleCategories.slice(0, 2)}
        onCreate={(cat) => console.log('Create:', cat)}
        onUpdate={(id, cat) => console.log('Update:', id, cat)}
        onDelete={(id) => console.log('Delete:', id)}
        onReorder={(cats) => console.log('Reorder:', cats)}
      />
      <p class="text-sm text-muted-foreground mt-4 px-6">Click "New Category" to see the creation form</p>
    </div>
  </Hst.Variant>
</Hst.Story>
