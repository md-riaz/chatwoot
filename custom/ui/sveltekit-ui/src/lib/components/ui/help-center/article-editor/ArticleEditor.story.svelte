<script lang="ts">
  import { ArticleEditor } from './index.js';
  export let Hst: any;

  const categories = ['Getting Started', 'Billing', 'Features', 'Troubleshooting', 'API'];
  const locales = ['en', 'es', 'fr', 'de', 'pt'];

  let newArticle = {
    title: '',
    content: '',
    status: 'draft' as const,
    category: '',
    author: '',
    tags: [],
    featured: false,
    locale: 'en'
  };

  let existingArticle = {
    id: '1',
    title: 'Getting Started with Chatwoot',
    content: 'Welcome to Chatwoot! This guide will help you get started with setting up your account and configuring your first inbox.\n\n## Step 1: Create Your Account\n\nFirst, sign up for a Chatwoot account...',
    status: 'published' as const,
    category: 'Getting Started',
    author: 'John Doe',
    tags: ['setup', 'onboarding', 'basics'],
    featured: true,
    locale: 'en'
  };
</script>

<Hst.Story title="Help Center/Article Editor" icon="lucide:edit">
  <Hst.Variant title="New Article">
    <ArticleEditor
      article={newArticle}
      {categories}
      {locales}
      onSave={(article) => console.log('Save:', article)}
      onPublish={(article) => console.log('Publish:', article)}
      onCancel={() => console.log('Cancel')}
    />
  </Hst.Variant>

  <Hst.Variant title="Edit Existing">
    <ArticleEditor
      article={{...existingArticle}}
      {categories}
      {locales}
      onSave={(article) => console.log('Save:', article)}
      onPublish={(article) => console.log('Publish:', article)}
      onCancel={() => console.log('Cancel')}
    />
  </Hst.Variant>

  <Hst.Variant title="With Many Tags">
    <ArticleEditor
      article={{
        title: 'Advanced Configuration',
        content: 'This article covers advanced configuration options...',
        status: 'draft' as const,
        category: 'Features',
        author: 'Jane Smith',
        tags: ['advanced', 'config', 'settings', 'automation', 'integrations', 'api'],
        featured: false,
        locale: 'en'
      }}
      {categories}
      {locales}
      onSave={(article) => console.log('Save:', article)}
      onPublish={(article) => console.log('Publish:', article)}
      onCancel={() => console.log('Cancel')}
    />
  </Hst.Variant>

  <Hst.Variant title="Saving State">
    <ArticleEditor
      article={{...existingArticle}}
      {categories}
      {locales}
      saving={true}
      onSave={(article) => console.log('Save:', article)}
      onPublish={(article) => console.log('Publish:', article)}
      onCancel={() => console.log('Cancel')}
    />
  </Hst.Variant>
</Hst.Story>
